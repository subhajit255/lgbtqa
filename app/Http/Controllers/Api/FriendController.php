<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\FriendRequest;
use App\Models\Notification;
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
     *     @OA\Parameter(
     *         name="met_in_real_life",
     *         in="query",
     *         description="Whether the user met this person in real life",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
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
        $existingRequest = FriendRequest::query()->where(function ($query) use ($user, $user_id) {
            $query->where('user_id', $user->id)->where('friend_id', $user_id);
        })->orWhere(function ($query) use ($user, $user_id) {
            $query->where('user_id', $user_id)->where('friend_id', $user->id);
        })->first();

        if ($existingRequest) {
            return $this->responseJson(false, 400, 'Friend request already exists or you are already friends.', []);
        }

        FriendRequest::create([
            'user_id' => $user->id,
            'friend_id' => $user_id,
            'status' => 'pending',
            'met_in_real_life' => $request->boolean('met_in_real_life', false)
        ]);
        Notification::create([
            'user_id' => $user_id,
            'title' => 'Friend Request',
            'description' => 'You have received a friend request from "' . $user->name . '"',
            'type' => 'friend_request',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
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
        Notification::create([
            'user_id' => $friendRequest->user_id,
            'title' => 'Friend Request Accepted',
            'description' => auth()->user()->name . ' has accepted your friend request.',
            'type' => 'friend_request_accepted',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
        ]);
        return  $this->responseJson(true, 200, 'Friend request accepted.', []);
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
        Notification::create([
            'user_id' => $friendRequest->user_id,
            'title' => 'Friend Request Accepted',
            'description' => $user->name . ' has rejected your friend request.',
            'type' => 'friend_request_rejected',
            'for' => 2,
            'is_read' => 0,
            'is_active' => 1,
        ]);
        return $this->responseJson(true, 200, 'Friend request rejected.', []);
    }
    /**
     * @OA\Get(
     *     path="/api/friends/my-friends",
     *     summary="Get list of accepted friends with optional search by name",
     *     tags={"Friends"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search friends by name, username, or profile display name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page_number",
     *         in="query",
     *         description="Page number (default: 1)",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(response=200, description="My friends fetched successfully."),
     *     @OA\Response(response=500, description="Something went wrong")
     * )
     */
    public function myFriends(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            // Eager load sender and receiver profiles
            $query = FriendRequest::with(['sender.profile', 'receiver.profile'])
                ->where('status', 'accepted')
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhere('friend_id', $user->id);
                });

            // Keyword / Name search
            $search = $request->input('search') ?? $request->input('name') ?? $request->input('keyword');
            if (!empty($search)) {
                $query->where(function ($q) use ($user, $search) {
                    // If current user is sender, search in receiver's name/username/profile
                    $q->where(function ($sub) use ($user, $search) {
                        $sub->where('user_id', $user->id)
                            ->whereHas('receiver', function ($uq) use ($search) {
                                $uq->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('username', 'like', '%' . $search . '%')
                                    ->orWhereHas('profile', function ($pq) use ($search) {
                                        $pq->where('display_name', 'like', '%' . $search . '%')
                                            ->orWhere('first_name', 'like', '%' . $search . '%')
                                            ->orWhere('last_name', 'like', '%' . $search . '%');
                                    });
                            });
                    })
                        // If current user is receiver, search in sender's name/username/profile
                        ->orWhere(function ($sub) use ($user, $search) {
                            $sub->where('friend_id', $user->id)
                                ->whereHas('sender', function ($uq) use ($search) {
                                    $uq->where('name', 'like', '%' . $search . '%')
                                        ->orWhere('username', 'like', '%' . $search . '%')
                                        ->orWhereHas('profile', function ($pq) use ($search) {
                                            $pq->where('display_name', 'like', '%' . $search . '%')
                                                ->orWhere('first_name', 'like', '%' . $search . '%')
                                                ->orWhere('last_name', 'like', '%' . $search . '%');
                                        });
                                });
                        });
                });
            }

            $friends = $query->paginate($perPage, ['*'], 'page_number', $page);
            return $this->responseJsonPaginated(true, 200, 'My friends fetched successfully.', $friends);
        } catch (\Exception $e) {
            logger($e->getMessage() . '-' . $e->getLine() . '-' . $e->getFile());
            return $this->responseJson(false, 500, 'Something went wrong');
        }
    }
}
