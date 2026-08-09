<?php

namespace App\Http\Controllers\Api;

use \App\Http\Resources\PostResource;
use \App\Models\Chat;
use \App\Models\ChatParticipant;
use \App\Models\CommunityCategory;
use \App\Models\Post;
use \App\Traits\FeedRecommendationsTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Notification;
use App\Models\PostCategory;
use App\Models\User;
use App\Traits\UploadAble;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityApiController extends Controller
{
    use UploadAble, FeedRecommendationsTrait;

    /**
     * @OA\Get(
     *     path="/api/communities",
     *     summary="Get list of active communities",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search communities by name, description or tags",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Communities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Communities retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="uuid", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="image_path", type="string"),
     *                     @OA\Property(property="type", type="string", example="public"),
     *                     @OA\Property(property="tags", type="string"),
     *                     @OA\Property(property="creator_id", type="integer"),
     *                     @OA\Property(property="members_count", type="integer"),
     *                     @OA\Property(property="user_membership_status", type="string", example="active"),
     *                     @OA\Property(property="user_role", type="string", example="member"),
     *                     @OA\Property(property="creator", type="object")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function categories()
    {
        $categories = CommunityCategory::where('is_active', 1)->get();

        return response()->json([
            'status' => true,
            'message' => 'Community categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/communities",
     *     summary="Get list of active communities",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search communities by name, description or tags",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Communities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Communities retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="uuid", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="image_path", type="string"),
     *                     @OA\Property(property="type", type="string", example="public"),
     *                     @OA\Property(property="tags", type="string"),
     *                     @OA\Property(property="creator_id", type="integer"),
     *                     @OA\Property(property="members_count", type="integer"),
     *                     @OA\Property(property="user_membership_status", type="string", example="active"),
     *                     @OA\Property(property="user_role", type="string", example="member"),
     *                     @OA\Property(property="creator", type="object")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $user = auth()->user();
        $query = Community::where('is_active', 1)->with('categories');

        if ($request->filled('tab')) {
            $tabOriginal = $request->input('tab');
            $tabNormalized = strtolower(str_replace(' ', '_', $tabOriginal));

            if ($tabNormalized === 'for_you') {
                $query->inRandomOrder(); // Can be expanded to rule-based logic
            } elseif ($tabNormalized === 'local') {
                if ($user->profile && $user->profile->city_id) {
                    $query->where('city_id', $user->profile->city_id);
                } else {
                    // Fallback to sort by distance if lat/lng available, or just random
                    $query->inRandomOrder();
                }
            } else {
                // Treat any other tab as a category ID or group name
                $query->whereHas('categories', function ($q) use ($tabOriginal) {
                    $q->where('group', $tabOriginal)->orWhere('community_categories.id', $tabOriginal);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        $communitiesPaginator = $query->with('creator')->latest()->paginate($perPage, ['*'], 'page_number', $page);

        $communitiesPaginator->through(function ($community) use ($userId) {
            $community->members_count = $community->members()->where('status', 'active')->count();

            $membership = $community->members()->where('user_id', $userId)->first();
            $community->user_membership_status = $membership ? $membership->status : null;
            $community->user_role = $membership ? $membership->role : null;
            $community->chat_id = $community->chat ? $community->chat->id : null;

            return $community;
        });

        return $this->responseJsonPaginated(
            true,
            200,
            'Communities retrieved successfully',
            $communitiesPaginator
        );
    }

    /**
     * @OA\Get(
     *     path="/api/communities/{uuid}",
     *     summary="Get details of a specific community",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the community",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Community details retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Community not found")
     * )
     */
    public function show($uuid)
    {
        $userId = auth()->id();
        $community = Community::with('creator')->where('uuid', $uuid)->first();

        if (!$community) {
            return response()->json([
                'status' => false,
                'message' => 'Community not found'
            ], 404);
        }

        $community->increment('view_count');

        $community->members_count = $community->members()->where('status', 'active')->count();

        $membership = $community->members()->where('user_id', $userId)->first();
        $community->user_membership_status = $membership ? $membership->status : null;
        $community->user_role = $membership ? $membership->role : null;
        $community->chat_id = $community->chat ? $community->chat->id : null;

        // Retrieve active members
        $community->active_members = CommunityMember::with('user')
            ->where('community_id', $community->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'name' => $m->user->name ?? null,
                    'image_path' => $m->user->image_path ?? null,
                    'role' => $m->role,
                    'joined_at' => $m->created_at->toIso8601String()
                ];
            });

        // Retrieve pending requests ONLY for creator
        $community->pending_requests = [];
        if ($community->creator_id == $userId) {
            $community->pending_requests = CommunityMember::with('user')
                ->where('community_id', $community->id)
                ->where('status', 'pending')
                ->get()
                ->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'user_id' => $m->user_id,
                        'name' => $m->user->name ?? null,
                        'image_path' => $m->user->image_path ?? null,
                        'requested_at' => $m->created_at->toIso8601String()
                    ];
                });
        }

        return response()->json([
            'status' => true,
            'message' => 'Community details retrieved successfully',
            'data' => $community
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/communities/create",
     *     summary="Create a new community",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "type", "file"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"public", "private"}),
     *                 @OA\Property(property="tags", type="string", description="Comma separated tags"),
     *                 @OA\Property(property="file", type="string", format="binary", description="Community Banner Image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Community created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Community created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:public,private',
            'tags' => 'nullable|string|max:255',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:community_categories,id',
            'lat' => 'nullable',
            'lng' => 'nullable',
            'city_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        // find default community hub id
        // $defaultCommunityHub = CommunityHub::where('is_default', 1)->first();
        try {
            $postData = [
                "name" => $request->name,
                "description" => $request->description,
                "creator_id" => auth()->id(),
                "type" => $request->type,
                "tags" => $request->tags,
                "is_active" => 1,
                "lat" => $request->lat ?? null,
                "lng" => $request->lng ?? null,
                "city_id" => $request->city_id ?? null,
            ];

            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                $isFileUploaded = $this->uploadOne($image, config('constants.SITE_COMMUNITY_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                if ($isFileUploaded) {
                    $postData['image'] = $fileName;
                }
            }
            $community = Community::create($postData);

            if ($request->has('categories')) {
                $community->categories()->sync($request->categories);
            }

            $chat = Chat::create([
                'is_group' => true,
                'name' => $community->name,
                'admin_id' => auth()->id(),
                'community_id' => $community->id,
            ]);

            // Automatically join the creator as active creator role
            CommunityMember::create([
                'community_id' => $community->id,
                'user_id' => auth()->id(),
                'status' => 'active',
                'role' => 'creator',
            ]);

            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => auth()->id(),
                'role' => 'admin',
            ]);

            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Community Created',
                'description' => 'You created the community "' . $request->name . '" successfully.',
                'type' => 'community_create',
                'for' => 2,
                'is_read' => 0,
                'is_active' => 1,
            ]);

            DB::commit();

            $community->chat_id = $chat->id;

            return response()->json([
                'status' => true,
                'message' => 'Community created successfully',
                'data' => $community
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create community: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/communities/update/{uuid}",
     *     summary="Update an existing community",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the community to update",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "type"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"public", "private"}),
     *                 @OA\Property(property="tags", type="string", description="Comma separated tags"),
     *                 @OA\Property(property="file", type="string", format="binary", description="Community Banner Image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Community updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Community not found")
     * )
     */
    public function update(Request $request, $uuid)
    {
        $community = Community::where('uuid', $uuid)->first();

        if (!$community) {
            return response()->json([
                'status' => false,
                'message' => 'Community not found'
            ], 404);
        }

        if ($community->creator_id != auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to update this community.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:public,private',
            'tags' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:community_categories,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'city_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $postData = [
                "name" => $request->name,
                "description" => $request->description,
                "type" => $request->type,
                "tags" => $request->tags,
            ];

            if ($request->has('lat')) $postData['lat'] = $request->lat;
            if ($request->has('lng')) $postData['lng'] = $request->lng;
            if ($request->has('city_id')) $postData['city_id'] = $request->city_id;

            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                $isFileUploaded = $this->uploadOne($image, config('constants.SITE_COMMUNITY_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                if ($isFileUploaded) {
                    $postData['image'] = $fileName;
                }
            }

            $community->update($postData);

            if ($request->has('categories')) {
                $community->categories()->sync($request->categories);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Community updated successfully',
                'data' => $community
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update community: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/communities/delete/{uuid}",
     *     summary="Delete a community",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the community to delete",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community deleted successfully"
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Community not found")
     * )
     */
    public function destroy($uuid)
    {
        $community = Community::where('uuid', $uuid)->first();

        if (!$community) {
            return response()->json([
                'status' => false,
                'message' => 'Community not found'
            ], 404);
        }

        if ($community->creator_id != auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to delete this community.'
            ], 403);
        }

        $community->delete();

        return response()->json([
            'status' => true,
            'message' => 'Community deleted successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/communities/{uuid}/join",
     *     summary="Join a community",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the community to join",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Join response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully joined the community"),
     *             @OA\Property(property="joined", type="boolean", example=true),
     *             @OA\Property(property="pending", type="boolean", example=false)
     *         )
     *     )
     * )
     */
    public function join($uuid)
    {
        $userId = auth()->id();
        $community = Community::where('uuid', $uuid)->first();

        if (!$community) {
            return response()->json([
                'status' => false,
                'message' => 'Community not found'
            ], 404);
        }

        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->status == 'active') {
                return response()->json([
                    'status' => true,
                    'message' => 'Already a member of this community',
                    'joined' => true,
                    'pending' => false
                ]);
            } elseif ($existing->status == 'pending') {
                return response()->json([
                    'status' => true,
                    'message' => 'Join request already pending',
                    'joined' => false,
                    'pending' => true
                ]);
            }
        }

        $status = $community->type == 'public' ? 'active' : 'pending';

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $userId,
            'status' => $status,
            'role' => 'member'
        ]);
        if (!$existing) {
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Join Community',
                'description' => $status == 'active' ? 'You are now a member of the community "' . $community->name : 'Your request to join the community "' . $community->name . '" has been sent',
                'type' => 'community_join',
                'for' => 2,
                'is_read' => 0,
                'is_active' => 1,
            ]);
        }
        if ($status == 'active' && $community->chat) {
            ChatParticipant::firstOrCreate([
                'chat_id' => $community->chat->id,
                'user_id' => $userId,
            ], [
                'role' => 'member'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => $status == 'active' ? 'Successfully joined the community' : 'Join request sent successfully',
            'joined' => $status == 'active',
            'pending' => $status == 'pending'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/communities/{uuid}/leave",
     *     summary="Leave a community",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the community to leave",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully left the community"
     *     )
     * )
     */
    public function leave($uuid)
    {
        $userId = auth()->id();
        $community = Community::where('uuid', $uuid)->first();

        if (!$community) {
            return response()->json([
                'status' => false,
                'message' => 'Community not found'
            ], 404);
        }

        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $userId)
            ->first();

        if (!$existing) {
            return response()->json([
                'status' => false,
                'message' => 'You are not a member of this community.'
            ], 400);
        }

        if ($existing->role == 'creator') {
            return response()->json([
                'status' => false,
                'message' => 'Community creator cannot leave the community. Delete the community instead.'
            ], 400);
        }

        if ($community->chat) {
            ChatParticipant::where('chat_id', $community->chat->id)
                ->where('user_id', $userId)
                ->delete();
        }

        $existing->delete();

        return response()->json([
            'status' => true,
            'message' => 'Successfully left the community'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/communities/{uuid}/requests",
     *     summary="List pending requests (Creator only)",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the community",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pending requests listed successfully"
     *     )
     * )
     */
    public function listRequests(Request $request, $uuid)
    {
        $userId = auth()->id();
        $community = Community::where('uuid', $uuid)->first();

        if (!$community) {
            return response()->json([
                'status' => false,
                'message' => 'Community not found'
            ], 404);
        }

        if ($community->creator_id != $userId) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to view pending requests for this community.'
            ], 403);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        $pendingRequestsPaginator = CommunityMember::with('user')
            ->where('community_id', $community->id)
            ->where('status', 'pending')
            ->paginate($perPage, ['*'], 'page_number', $page);

        $pendingRequestsPaginator->through(function ($m) {
            return [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'name' => $m->user->name ?? null,
                'image_path' => $m->user->image_path ?? null,
                'requested_at' => $m->created_at->toIso8601String()
            ];
        });

        return $this->responseJsonPaginated(
            true,
            200,
            'Pending requests retrieved successfully',
            $pendingRequestsPaginator
        );
    }

    /**
     * @OA\Post(
     *     path="/api/communities/requests/{id}/approve",
     *     summary="Approve join request (Creator only)",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the membership request",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Request approved successfully"
     *     )
     * )
     */
    public function approveRequest($id)
    {
        $member = CommunityMember::with('community')->find($id);

        if (!$member || $member->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Pending request not found.'
            ], 404);
        }

        if ($member->community->creator_id != auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to approve requests for this community.'
            ], 403);
        }

        $member->update(['status' => 'active']);
        Notification::create([
            'user_id' => $member->user_id,
            'title' => 'Join Community',
            'description' => 'You are now a member of the community "' . $member->community->name,
            'type' => 'community_join',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
        ]);
        if ($member->community->chat) {
            ChatParticipant::firstOrCreate([
                'chat_id' => $member->community->chat->id,
                'user_id' => $member->user_id,
            ], [
                'role' => 'member'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Join request approved successfully.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/communities/requests/{id}/reject",
     *     summary="Reject join request (Creator only)",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the membership request",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Request rejected successfully"
     *     )
     * )
     */
    public function rejectRequest($id)
    {
        $member = CommunityMember::with('community')->find($id);

        if (!$member || $member->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Pending request not found.'
            ], 404);
        }

        if ($member->community->creator_id != auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to reject requests for this community.'
            ], 403);
        }

        if ($member->community->chat) {
            ChatParticipant::where('chat_id', $member->community->chat->id)
                ->where('user_id', $member->user_id)
                ->delete();
        }
        Notification::create([
            'user_id' => $member->user_id,
            'title' => 'Join Community',
            'description' => 'Your request to join the community "' . $member->community->name . '" has been rejected.',
            'type' => 'community_join',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
        ]);
        $member->delete();

        return response()->json([
            'status' => true,
            'message' => 'Join request rejected successfully.'
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/communities/trending",
     *     summary="Get list of trending communities",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Communities retrieved successfully"
     *     )
     * )
     */
    public function trendingCommunities(Request $request)
    {
        $communities = Community::query()->where('is_active', 1)
            ->withCount('activeMembers')
            ->with('creator')
            // Score = (View Velocity * 0.4) + (Total Views * 0.3) + (Active Members * 0.3)
            // View Velocity = view_count / days_since_created
            ->orderByRaw('((view_count / (DATEDIFF(NOW(), created_at) + 1)) * 0.4) + (view_count * 0.3) + (active_members_count * 0.3) DESC')
            ->take(10)
            ->get();
        return $this->responseJson(true, 200, 'Trending communities retrieved successfully', $communities);
    }

    /**
     * @OA\Get(
     *     path="/api/communities/suggested-communities",
     *     summary="Get list of suggested communities based on user preferences",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Suggested communities retrieved successfully"
     *     )
     * )
     */
    public function suggestedCommunities(Request $request)
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Start with hobbies
        $searchTerms = $user->hobbies()->pluck('name')->toArray();

        // Add additional profile preferences if available
        $profile = $user->profile;
        if ($profile) {
            if ($profile->orientation) {
                $orientationStr = getOrientation()[$profile->orientation] ?? null;
                if ($orientationStr) {
                    $searchTerms[] = $orientationStr;
                }
            }
            if ($profile->sex_importance) {
                $sexImportanceStr = getSexImportance()[$profile->sex_importance] ?? null;
                if ($sexImportanceStr) {
                    $searchTerms[] = $sexImportanceStr;
                }
            }
            if ($profile->age_range_min && $profile->age_range_max) {
                // E.g. search for "18-25" or "18" or "25" in tags/description
                $searchTerms[] = $profile->age_range_min . '-' . $profile->age_range_max;
            }
        }

        // Remove empty strings and duplicates
        $searchTerms = array_unique(array_filter($searchTerms));

        $query = Community::query()->where('is_active', 1)->where('type', 'public');

        if (!empty($searchTerms)) {
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('tags', 'like', '%' . $term . '%')
                        ->orWhere('name', 'like', '%' . $term . '%')
                        ->orWhere('description', 'like', '%' . $term . '%');
                }
            });
        }

        // Exclude communities the user is already a member of
        $joinedCommunityIds = CommunityMember::where('user_id', $userId)
            ->whereIn('status', ['active', 'pending'])
            ->pluck('community_id')
            ->toArray();

        if (!empty($joinedCommunityIds)) {
            $query->whereNotIn('id', $joinedCommunityIds);
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        // Order by a simplified trending score or just latest if no complex sorting needed
        $communitiesPaginator = $query->with('creator')
            ->withCount('activeMembers')
            ->orderByRaw('((view_count / (DATEDIFF(NOW(), created_at) + 1)) * 0.4) + (view_count * 0.3) + (active_members_count * 0.3) DESC')
            ->paginate($perPage, ['*'], 'page_number', $page);

        // If no suggested communities found, fallback to trending public communities not joined by the user
        if ($communitiesPaginator->isEmpty() && !empty($searchTerms)) {
            $fallbackQuery = Community::query()->where('is_active', 1)->where('type', 'public');
            if (!empty($joinedCommunityIds)) {
                $fallbackQuery->whereNotIn('id', $joinedCommunityIds);
            }
            $communitiesPaginator = $fallbackQuery->with('creator')
                ->withCount('activeMembers')
                ->orderByRaw('((view_count / (DATEDIFF(NOW(), created_at) + 1)) * 0.4) + (view_count * 0.3) + (active_members_count * 0.3) DESC')
                ->paginate($perPage, ['*'], 'page_number', $page);
        }

        $communitiesPaginator->through(function ($community) {
            $community->members_count = $community->active_members_count;
            $community->user_membership_status = null;
            $community->user_role = null;
            $community->chat_id = $community->chat ? $community->chat->id : null;
            return $community;
        });

        return $this->responseJsonPaginated(
            true,
            200,
            'Suggested communities retrieved successfully',
            $communitiesPaginator
        );
    }

    /**
     * @OA\Get(
     *     path="/api/communities/members-list",
     *     summary="Get list of community members",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search members by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Members list retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function membersList(Request $request)
    {
        try {
            $query = User::query()->where(['user_type' => 3])->where('id', '!=', auth()->id())
                ->where('is_active', 1);

            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $members = $query->get();

            return $this->responseJson(true, 200, 'Members list retrieved successfully', UserResource::collection($members));
        } catch (\Exception $e) {
            logger($e->getMessage() . '---' . $e->getLine() . '---' . $e->getFile());
            return $this->responseJson(false, 500, 'Something went wrong', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/communities/{uuid}/add-members",
     *     summary="Add members to a community",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Community UUID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_ids"},
     *             @OA\Property(
     *                 property="user_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 description="Array of User IDs to add to the community"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Members added successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Community not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Something went wrong"
     *     )
     * )
     */
    public function addMembers(Request $request, $uuid)
    {
        try {
            $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $community = Community::where('uuid', $uuid)->first();
            if (!$community) {
                return $this->responseJson(false, 404, 'Community not found');
            }

            $addedMembers = [];

            foreach ($request->user_ids as $userId) {
                $exists = CommunityMember::where('community_id', $community->id)
                    ->where('user_id', $userId)
                    ->exists();

                if (!$exists) {
                    CommunityMember::create([
                        'community_id' => $community->id,
                        'user_id' => $userId,
                        'status' => 'active',
                        'role' => 'member'
                    ]);

                    if ($community->chat) {
                        ChatParticipant::firstOrCreate([
                            'chat_id' => $community->chat->id,
                            'user_id' => $userId,
                        ], [
                            'role' => 'member'
                        ]);
                    }
                    Notification::create([
                        'user_id' => $userId,
                        'title' => 'Community Member Added',
                        'description' => 'You have been added to the community "' . $community->name,
                        'type' => 'community_member_added',
                        'for' => 2,
                        'is_read' => 0,
                        'is_active' => 1,
                    ]);

                    $addedMembers[] = $userId;
                }
            }
            return $this->responseJson(true, 200, 'Members added successfully', $addedMembers);
        } catch (\Exception $e) {
            logger($e->getMessage() . '---' . $e->getLine() . '---' . $e->getFile());
            return $this->responseJson(false, 500, 'Something went wrong', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/communities/feed",
     *     summary="Get community feed posts from mutual users",
     *     tags={"Communities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of posts per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter posts by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community feed retrieved successfully"
     *     )
     * )
     */
    public function communityFeed(Request $request)
    {
        try {
            $userId = auth()->id();
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            // Get communities where the current user is an active member
            $userCommunities = CommunityMember::where('user_id', $userId)
                ->where('status', 'active')
                ->pluck('community_id');

            // Get users who are active members in any of those communities
            $mutualUserIds = CommunityMember::whereIn('community_id', $userCommunities)
                ->where('status', 'active')
                ->pluck('user_id')
                ->unique();

            $postsQuery = Post::with(['user.profile', 'user.kycVerification', 'media', 'loves', 'comments', 'stars', 'emojis'])
                ->where('status', 'active')
                ->whereIn('user_id', $mutualUserIds);

            if ($request->has('category_id') && !empty($request->category_id)) {
                $postsQuery->where('post_category_id', $request->category_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $postsQuery->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $posts = $postsQuery->latest()->paginate($perPage, ['*'], 'page', $page);

            $postsResource = PostResource::collection($posts);

            if ($request->input('page', 1) == 1) {
                return $postsResource->additional([
                    'status' => true,
                    'message' => 'Community feed retrieved successfully!',
                    'communities' => $this->getRecommendedCommunities(),
                    'events' => $this->getRecommendedEvents(),
                    'nearby_users' => $this->getNearbyUsers(),
                    'matches' => $this->getMatches(),
                ]);
            }

            return $postsResource->additional([
                'status' => true,
                'message' => 'Community feed retrieved successfully!'
            ]);
        } catch (\Exception $e) {
            logger($e->getMessage() . '---' . $e->getLine() . '---' . $e->getFile());
            return $this->responseJson(false, 500, 'Something went wrong', $e->getMessage());
        }
    }
}
