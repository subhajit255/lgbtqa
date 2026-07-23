<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/friends/request/{user_id}",
     *     summary="Send a friend request",
     *     tags={"Friends"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="ID of the user to send request to",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Friend request sent successfully"),
     *     @OA\Response(response=400, description="Invalid request / Already sent"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function sendRequest(Request $request, $user_id)
    {
        $user = Auth::user();

        if ($user->id == $user_id) {
            return $this->responseJson(false, 400, 'You cannot send a friend request to yourself.', []);
        }

        $friend = User::find($user_id);
        if (!$friend) {
            return $this->responseJson(false, 404, 'User not found.', []);
        }

        // Check if request already exists
        $existingRequest = FriendRequest::where(function($query) use ($user, $user_id) {
            $query->where('user_id', $user->id)->where('friend_id', $user_id);
        })->orWhere(function($query) use ($user, $user_id) {
            $query->where('user_id', $user_id)->where('friend_id', $user->id);
        })->first();

        if ($existingRequest) {
            return $this->responseJson(false, 400, 'Friend request already exists or you are already friends.', []);
        }

        FriendRequest::create([
            'user_id' => $user->id,
            'friend_id' => $user_id,
            'status' => 'pending'
        ]);

        return $this->responseJson(true, 200, 'Friend request sent successfully.', []);
    }

    /**
     * @OA\Get(
     *     path="/api/friends/requests",
     *     summary="View pending friend requests",
     *     tags={"Friends"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Pending requests fetched successfully")
     * )
     */
    public function viewRequests(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        // Get requests where user is the receiver and status is pending
        $requestsPaginator = FriendRequest::with('sender.profile')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->paginate($perPage, ['*'], 'page_number', $page);

        return $this->responseJsonPaginated(true, 200, 'Pending friend requests fetched.', $requestsPaginator);
    }

    /**
     * @OA\Post(
     *     path="/api/friends/accept/{request_id}",
     *     summary="Accept a friend request",
     *     tags={"Friends"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="request_id",
     *         in="path",
     *         description="ID of the friend request",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Friend request accepted"),
     *     @OA\Response(response=404, description="Request not found")
     * )
     */
    public function acceptRequest(Request $request, $request_id)
    {
        $user = Auth::user();
        $friendRequest = FriendRequest::where('id', $request_id)->where('friend_id', $user->id)->first();

        if (!$friendRequest) {
            return $this->responseJson(false, 404, 'Friend request not found.', []);
        }

        $friendRequest->status = 'accepted';
        $friendRequest->save();

        return $this->responseJson(true, 200, 'Friend request accepted.', []);
    }

    /**
     * @OA\Post(
     *     path="/api/friends/reject/{request_id}",
     *     summary="Reject a friend request",
     *     tags={"Friends"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="request_id",
     *         in="path",
     *         description="ID of the friend request",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Friend request rejected"),
     *     @OA\Response(response=404, description="Request not found")
     * )
     */
    public function rejectRequest(Request $request, $request_id)
    {
        $user = Auth::user();
        $friendRequest = FriendRequest::where('id', $request_id)->where('friend_id', $user->id)->first();

        if (!$friendRequest) {
            return $this->responseJson(false, 404, 'Friend request not found.', []);
        }

        $friendRequest->status = 'rejected';
        $friendRequest->save();

        return $this->responseJson(true, 200, 'Friend request rejected.', []);
    }
}
