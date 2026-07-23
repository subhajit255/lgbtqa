<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroupApiController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/groups/discover",
     *     summary="Discover Public Groups",
     *     tags={"Group Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of discoverable groups")
     * )
     */
    public function discover(Request $request)
    {
        $query = Chat::discoverable();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('tags', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        $groupsPaginator = $query->with('latestMessage')
                       ->withCount('participants')
                       ->latest()
                       ->paginate($perPage, ['*'], 'page_number', $page);

        return $this->responseJsonPaginated(true, 200, 'Groups retrieved successfully', $groupsPaginator);
    }

    /**
     * @OA\Post(
     *     path="/api/groups/{chat}/join",
     *     summary="Join a Public Group",
     *     tags={"Group Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Joined successfully")
     * )
     */
    public function join(Request $request, Chat $chat)
    {
        if (!$chat->is_public || !$chat->is_group) {
            return $this->responseJson(false, 403, 'This group is not public or not a group chat');
        }

        if ($chat->is_locked) {
            return $this->responseJson(false, 403, 'This chat is locked by admin');
        }

        if ($chat->participants()->count() >= $chat->member_limit) {
            return $this->responseJson(false, 422, 'Group member limit reached');
        }

        $participant = ChatParticipant::firstOrCreate([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
        ], [
            'role' => ChatParticipant::ROLE_MEMBER
        ]);

        return $this->responseJson(true, 200, 'Joined successfully', $chat->load('users:id,name,profile_image'));
    }

    /**
     * @OA\Post(
     *     path="/api/groups/{chat}/leave",
     *     summary="Leave a Group",
     *     tags={"Group Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Left successfully")
     * )
     */
    public function leave(Request $request, Chat $chat)
    {
        $participant = $chat->participants()->where('user_id', auth()->id())->first();

        if (!$participant) {
            return $this->responseJson(false, 404, 'You are not a member of this group');
        }

        if ($chat->admin_id == auth()->id()) {
            return $this->responseJson(false, 422, 'Admin cannot leave without assigning a new admin');
        }

        $participant->delete();

        return $this->responseJson(true, 200, 'Left successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/groups/{chat}/lock",
     *     summary="Lock/Unlock Chat (Superadmin only)",
     *     tags={"Group Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(@OA\Property(property="is_locked", type="boolean"))
     *     ),
     *     @OA\Response(response=200, description="Status updated")
     * )
     */
    public function toggleLock(Request $request, Chat $chat)
    {
        // Simple role check based on standard Laravel practice or the user's instructions
        if (!auth()->user()->hasRole('superadmin')) {
             return $this->responseJson(false, 403, 'Only Superadmin can lock/unlock chats');
        }

        $chat->update(['is_locked' => $request->is_locked]);

        return $this->responseJson(true, 200, 'Chat status updated successfully', ['is_locked' => $chat->is_locked]);
    }
}
