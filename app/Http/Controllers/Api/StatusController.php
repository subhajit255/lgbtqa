<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityMember;
use App\Models\Status;
use App\Models\StatusReaction;
use App\Models\StatusComment;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StatusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/statuses",
     *     summary="Get Feed of active statuses grouped by User",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statuses retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statuses retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="profile_image", type="string"),
     *                     @OA\Property(property="statuses", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="type", type="string", enum={"text", "image", "video"}),
     *                             @OA\Property(property="content", type="string"),
     *                             @OA\Property(property="background_color", type="string"),
     *                             @OA\Property(property="tagged_user", type="object", nullable=true)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        // Get all users who have active statuses
        $query = User::whereHas('statuses', function ($query) {
            $query->active();
        })->with(['statuses' => function ($query) {
            $query->active()->with('taggedUser')->latest();
        }]);

        $paginatedUsers = $query->paginate($perPage, ['*'], 'page_number', $page);

        $paginatedUsers->through(function ($user) {
            $user->statuses->map(function ($status) {
                if (in_array($status->type, ['image', 'video']) && $status->content) {
                    $status->content = asset('storage/' . $status->content);
                }
                return $status;
            });
            return $user;
        });

        return $this->responseJsonPaginated(
            true,
            200,
            'Statuses retrieved successfully',
            $paginatedUsers
        );
    }

    /**
     * @OA\Post(
     *     path="/api/statuses",
     *     summary="Create a new status",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"type"},
     *                 @OA\Property(property="type", type="string", enum={"text", "image", "video"}),
     *                 @OA\Property(property="content", type="string", description="Message content for text status"),
     *                 @OA\Property(property="background_color", type="string", example="#6f42c1"),
     *                 @OA\Property(property="media_file", type="string", format="binary", description="Image or Video file"),
     *                 @OA\Property(property="tagged_user_id", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Status created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Status created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:text,image,video',
            'content' => 'nullable|string',
            'background_color' => 'nullable|string',
            'tagged_user_id' => 'nullable|exists:users,id',
        ]);

        $content = $request->content;

        // If it's an image or video, handle upload
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('statuses', $fileName, 'public');
            $content = $path;
        }

        $status = Status::create([
            'user_id' => auth()->id(),
            'tagged_user_id' => $request->tagged_user_id,
            'type' => $request->type,
            'content' => $content,
            'background_color' => $request->background_color,
            'expires_at' => now()->addHours(24),
            'is_active' => true,
        ]);

        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Status Created',
            'description' => 'Your status has been updated successfully.',
            'type' => 'status_create',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
        ]);

        $responseData = $status->toArray();
        if (in_array($status->type, ['image', 'video']) && $status->content) {
            $responseData['content'] = asset('storage/' . $status->content);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status created successfully',
            'data' => $responseData
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/statuses/{status}",
     *     summary="Delete user own status",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Status deleted successfully"
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Status $status)
    {
        if ($status->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // optionally delete file from storage if type is image/video
        if (in_array($status->type, ['image', 'video']) && $status->content) {
            Storage::disk('public')->delete($status->content);
        }

        $status->delete();

        return response()->json([
            'status' => true,
            'message' => 'Status deleted successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/statuses/{status}/react",
     *     summary="React to a status",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"emoji"},
     *             @OA\Property(property="emoji", type="string", example="❤️")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reaction updated/added successfully")
     * )
     */
    public function react(Request $request, Status $status)
    {
        $request->validate([
            'emoji' => 'required|string'
        ]);

        // Toggle or Update reaction
        $existingReaction = StatusReaction::where('status_id', $status->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingReaction) {
            if ($existingReaction->emoji === $request->emoji) {
                // Remove reaction if same emoji sent again
                $existingReaction->delete();
                return response()->json(['status' => true, 'message' => 'Reaction removed']);
            } else {
                // Update emoji
                $existingReaction->update(['emoji' => $request->emoji]);
                return response()->json(['status' => true, 'message' => 'Reaction updated', 'data' => $existingReaction]);
            }
        }

        $reaction = StatusReaction::create([
            'status_id' => $status->id,
            'user_id' => auth()->id(),
            'emoji' => $request->emoji
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reaction added successfully',
            'data' => $reaction
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/statuses/{status}/reactions",
     *     summary="Get reactions for a specific status",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Reactions retrieved successfully"
     *     )
     * )
     */
    public function reactions(Status $status)
    {
        $reactions = StatusReaction::with('user:id,uuid,name,image_path')
            ->where('status_id', $status->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Reactions retrieved successfully',
            'data' => $reactions
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/statuses/{status}/comments",
     *     summary="Add a comment to a status (N-Level support)",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"comment"},
     *             @OA\Property(property="comment", type="string", example="Love this!"),
     *             @OA\Property(property="parent_id", type="integer", description="ID of parent comment if replying", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment added successfully")
     * )
     */
    public function addComment(Request $request, Status $status)
    {
        $request->validate([
            'comment' => 'required',
            'parent_id' => 'nullable|exists:status_comments,id'
        ]);

        $comment = StatusComment::create([
            'status_id' => $status->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'comment' => $request->comment
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Comment added successfully',
            'data' => $comment->load('user:id,name,profile_image')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/statuses/{status}/comments",
     *     summary="Get N-Level comments for a status",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Comments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="comment", type="string"),
     *                     @OA\Property(property="user", type="object"),
     *                     @OA\Property(property="replies", type="array", @OA\Items(type="object"))
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getComments(Request $request, Status $status)
    {
        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        // Recursively load replies
        $commentsPaginator = StatusComment::where('status_id', $status->id)
            ->whereNull('parent_id')
            ->with(['user:id,name,profile_image', 'replies.user:id,name,profile_image'])
            ->latest()
            ->paginate($perPage, ['*'], 'page_number', $page);

        return $this->responseJsonPaginated(
            true,
            200,
            'Comments retrieved successfully',
            $commentsPaginator
        );
    }

    /**
     * @OA\Get(
     *     path="/api/statuses/user/{user}",
     *     summary="Get a specific user active statuses",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="User statuses retrieved successfully"
     *     )
     * )
     */
    public function userStatuses(Request $request, User $user)
    {
        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        $statusesPaginator = $user->statuses()->active()->with('taggedUser')->latest()->paginate($perPage, ['*'], 'page_number', $page);

        $statusesPaginator->through(function ($status) {
            if (in_array($status->type, ['image', 'video']) && $status->content) {
                $status->content = asset('storage/' . $status->content);
            }
            return $status;
        });

        return $this->responseJsonPaginated(
            true,
            200,
            'User statuses retrieved successfully',
            $statusesPaginator
        );
    }

    /**
     * @OA\Get(
     *     path="/api/statuses/same-communities-statuses",
     *     summary="Get active statuses of users who are members of my communities",
     *     tags={"Statuses (Stories)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statuses retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statuses retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="profile_image", type="string"),
     *                     @OA\Property(property="statuses", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="type", type="string", enum={"text", "image", "video"}),
     *                             @OA\Property(property="content", type="string"),
     *                             @OA\Property(property="background_color", type="string"),
     *                             @OA\Property(property="tagged_user", type="object", nullable=true)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function statusOfSameCommunitiesMembers(Request $request)
    {
        try {
            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            // find my joined communities
            $communities = CommunityMember::where(['user_id' => auth()->user()->id, 'status' => 'active'])->pluck('community_id')->toArray();

            // get all active statuses of users who are members of my communities
            $community_users = CommunityMember::whereIn('community_id', $communities)->pluck('user_id')->toArray();

            // fetch statuses of those users grouped by user
            $query = User::whereIn('id', $community_users)
                ->whereHas('statuses', function ($query) {
                    $query->active();
                })->with(['statuses' => function ($query) {
                    $query->active()->with('taggedUser')->latest();
                }]);

            $paginatedUsers = $query->paginate($perPage, ['*'], 'page_number', $page);

            $paginatedUsers->through(function ($user) {
                $user->statuses->map(function ($status) {
                    if (in_array($status->type, ['image', 'video']) && $status->content) {
                        $status->content = asset('storage/' . $status->content);
                    }
                    return $status;
                });
                return $user;
            });

            return $this->responseJsonPaginated(
                true,
                200,
                'Statuses retrieved successfully',
                $paginatedUsers
            );
        } catch (\Exception $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');

            return $this->responseJson($status, $code, $message, $response);
        }
    }
}
