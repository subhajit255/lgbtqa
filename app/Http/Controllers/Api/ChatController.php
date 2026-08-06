<?php

namespace App\Http\Controllers\Api;

use \App\Models\UserBlock;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/chats",
     *     summary="Get Chat List",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Chats retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        $chatsPaginator = Chat::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['users' => function ($q) {
                $q->select('users.id', 'name', 'profile_image');
            }])
            ->with('latestMessage')
            ->latest('updated_at')
            ->paginate($perPage, ['*'], 'page_number', $page);

        return $this->responseJsonPaginated(
            true,
            200,
            'Chats retrieved successfully',
            $chatsPaginator
        );
    }

    /**
     * @OA\Post(
     *     path="/api/chats",
     *     summary="Create a new Chat",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"is_group", "user_ids"},
     *
     *             @OA\Property(property="is_group", type="boolean", example=false),
     *             @OA\Property(property="name", type="string", description="Required if is_group is true"),
     *             @OA\Property(property="user_ids", type="array", @OA\Items(type="integer"), example={2,3})
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Chat created successfully"),
     *     @OA\Response(response=200, description="Chat already exists (for 1-on-1)"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_group' => 'required|boolean',
            'name' => 'nullable|required_if:is_group,true|string|max:255',
            'user_ids' => 'required|array|min:1', // Users to add
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $userIds = $request->user_ids;
        $userIds[] = $user->id; // Ensure creator is included
        $userIds = array_unique($userIds);

        // For 1-on-1 chat, check if it already exists
        if (!$request->is_group && count($userIds) == 2) {
            $existingChat = Chat::where('is_group', false)
                ->whereHas('participants', function ($q) use ($userIds) {
                    $q->whereIn('user_id', $userIds);
                }, '=', 2)
                ->first();

            if ($existingChat) {
                return response()->json([
                    'status' => true,
                    'message' => 'Chat already exists',
                    'data' => $existingChat->load('users:id,name,profile_image')
                ]);
            }
        }

        $chat = Chat::create([
            'is_group' => $request->is_group,
            'name' => $request->is_group ? $request->name : null,
            'admin_id' => $request->is_group ? $user->id : null,
        ]);

        foreach ($userIds as $userId) {
            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $userId
            ]);

            Notification::create([
                'user_id' => $userId,
                'title' => $request->is_group ? 'Added to Group Chat' : 'New Chat Started',
                'description' => $request->is_group
                    ? ($userId == $user->id ? 'You created the group chat "' . $request->name . '".' : 'You were added to the group chat "' . $request->name . '".')
                    : ($userId == $user->id ? 'You started a chat.' : 'A new chat has been started with you.'),
                'type' => 'chat_create',
                'chat_id' => $chat->id,
                'for' => 2,
                'is_read' => 0,
                'is_active' => 1,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Chat created successfully',
            'data' => $chat->load('users:id,name,profile_image')
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/chats/{chat}/participants",
     *     summary="Add Participant to Group Chat",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"user_id"},
     *
     *             @OA\Property(property="user_id", type="integer", example=2)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Participant added successfully"),
     *     @OA\Response(response=400, description="Cannot add to 1-on-1 chat"),
     *     @OA\Response(response=403, description="Forbidden - Only admin can add"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function addParticipant(Request $request, Chat $chat)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if (!$chat->is_group) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot add participants to a 1-on-1 chat'
            ], 400);
        }

        // Check if admin
        if ($chat->admin_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Only group admin can add participants'
            ], 403);
        }

        $participant = ChatParticipant::firstOrCreate([
            'chat_id' => $chat->id,
            'user_id' => $request->user_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Participant added successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/chats/users",
     *     summary="Get Chat Users List (excluding me)",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search users by name or username",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page_no",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="uuid", type="string"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="username", type="string"),
     *                         @OA\Property(property="profile_image", type="string"),
     *                         @OA\Property(property="image_path", type="string"),
     *                         @OA\Property(property="is_online", type="boolean"),
     *                         @OA\Property(property="last_seen_at", type="string", nullable=true),
     *                         @OA\Property(property="last_message", type="string", nullable=true),
     *                         @OA\Property(property="last_message_date_time", type="string", nullable=true, example="2026-07-04 23:50:00"),
     *                         @OA\Property(property="latest_message", type="object", nullable=true)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function chatUserList(Request $request)
    {
        $currentUser = $request->user();
        $search = $request->query('search');

        // Exclude users blocked by the current user or who blocked the current user
        $blockedUserIds = UserBlock::where('user_id', $currentUser->id)->pluck('blocked_user_id')
            ->merge(UserBlock::where('blocked_user_id', $currentUser->id)->pluck('user_id'))
            ->filter()->unique()->toArray();

        $query = $currentUser->friends()
            ->where('id', '!=', $currentUser->id)
            ->where('user_type', 3) // Standard User
            ->where('is_active', 1)
            ->where('is_blocked', 0);

        if (!empty($blockedUserIds)) {
            $query->whereNotIn('id', $blockedUserIds);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        $perPage = $request->input('per_page', 10);
        $pageNo = $request->input('page_no', 1);
        $users = $query->select('id', 'uuid', 'name', 'username', 'profile_image')
            ->paginate($perPage, ['*'], 'page_no', $pageNo);

        $userIds = $users->pluck('id')->toArray();

        // Get 1-on-1 chats for current user with these users
        $chats = Chat::where('is_group', false)
            ->whereHas('participants', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            })
            ->whereHas('participants', function ($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })
            ->with(['participants', 'latestMessage'])
            ->get();

        $userLastMessages = [];
        foreach ($chats as $chat) {
            $otherParticipant = $chat->participants->firstWhere('user_id', '!=', $currentUser->id);
            if ($otherParticipant && $chat->latestMessage) {
                $userLastMessages[$otherParticipant->user_id] = $chat->latestMessage;
            }
        }

        $users->getCollection()->transform(function ($user) use ($userLastMessages) {
            $latestMessage = $userLastMessages[$user->id] ?? null;
            $user->last_message = $latestMessage ? $latestMessage->message : null;
            $user->last_message_date_time = $latestMessage && $latestMessage->created_at ? $latestMessage->created_at->toDateTimeString() : null;
            $user->latest_message = $latestMessage;
            return $user;
        });

        return response()->json([
            'status' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/chats/{chat}/read",
     *     summary="Mark chat as read up to a message",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message_id"},
     *             @OA\Property(property="message_id", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Chat marked as read successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function markAsRead(Request $request, Chat $chat)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $participant = ChatParticipant::where('chat_id', $chat->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$participant) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $participant->update([
            'last_read_message_id' => $request->message_id,
            'last_read_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Chat marked as read successfully'
        ]);
    }
}
