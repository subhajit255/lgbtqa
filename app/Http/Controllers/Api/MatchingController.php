<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSwipe;
use App\Models\UserIcebreaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/matching/feed",
     *     summary="Get the feed of users to swipe on",
     *     tags={"Matching"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tab",
     *         in="query",
     *         description="Tab to view: 'for_you' or 'nearby'",
     *         required=false,
     *         @OA\Schema(type="string", enum={"for_you", "nearby"}, default="for_you")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function feed(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'for_you'); // 'for_you' or 'nearby'

        // Get IDs of users the current user has already swiped on
        $swipedUserIds = UserSwipe::where('user_id', $user->id)->pluck('target_user_id')->toArray();

        // Get blocked user IDs
        $blockedUserIds = $user->blockedUsers()->pluck('users.id')->toArray();

        $excludedIds = array_merge([$user->id], $swipedUserIds, $blockedUserIds);

        $query = User::with(['profile', 'userLocation'])
            ->whereNotIn('id', $excludedIds);

        if ($tab === 'nearby') {
            $currentUserLocation = $user->userLocation;

            if ($currentUserLocation && $currentUserLocation->lat && $currentUserLocation->lng) {
                $lat = $currentUserLocation->lat;
                $lng = $currentUserLocation->lng;

                // Haversine formula to order by distance
                $query->join('user_locations', 'users.id', '=', 'user_locations.user_id')
                    ->selectRaw("users.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( user_locations.lat ) ) * cos( radians( user_locations.lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( user_locations.lat ) ) ) ) AS distance", [$lat, $lng, $lat])
                    ->orderBy('distance');
            } else {
                // Fallback if current user has no location, just show users with locations
                $query->has('userLocation')->inRandomOrder();
            }
        } else {
            // 'for_you' tab
            // Here you can add algorithmic logic (e.g. matching hobbies, preferences)
            // For now, we will just randomize
            $query->inRandomOrder();
        }

        $users = $query->limit(20)->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/matching/swipe",
     *     summary="Handle a swipe action (like, pass, super_like)",
     *     tags={"Matching"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"target_user_id", "action"},
     *             @OA\Property(property="target_user_id", type="integer", example=2),
     *             @OA\Property(property="action", type="string", enum={"like", "pass", "super_like"}, example="like")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Swipe recorded",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="is_match", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Swipe recorded.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Already swiped"
     *     )
     * )
     */
    public function swipe(Request $request)
    {
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'action' => 'required|in:like,pass,super_like'
        ]);

        $user = Auth::user();
        $targetUserId = $request->target_user_id;
        $action = $request->action;

        // Ensure user hasn't already swiped this target
        $existingSwipe = UserSwipe::where('user_id', $user->id)
            ->where('target_user_id', $targetUserId)
            ->first();

        if ($existingSwipe) {
            return response()->json(['status' => 'error', 'message' => 'Already swiped on this user.'], 400);
        }

        $isMatch = false;

        // If the action is a 'like' or 'super_like', check for a mutual like
        if (in_array($action, ['like', 'super_like'])) {
            $mutualLike = UserSwipe::where('user_id', $targetUserId)
                ->where('target_user_id', $user->id)
                ->whereIn('action', ['like', 'super_like'])
                ->first();

            if ($mutualLike) {
                $isMatch = true;
                // Update their swipe to reflect the match
                $mutualLike->update(['is_match' => true]);

                // TODO: Possibly create a Chat record here if they should auto-chat
            }
        }

        // Record the swipe
        $swipe = UserSwipe::create([
            'user_id' => $user->id,
            'target_user_id' => $targetUserId,
            'action' => $action,
            'is_match' => $isMatch
        ]);

        return response()->json([
            'status' => 'success',
            'is_match' => $isMatch,
            'message' => $isMatch ? 'It\'s a match!' : 'Swipe recorded.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/matching/icebreaker",
     *     summary="Send an icebreaker (Nudge, Post-It, or Message) to a user immediately after liking them.",
     *     tags={"Matching"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"receiver_id", "type"},
     *             @OA\Property(property="receiver_id", type="integer", example=3),
     *             @OA\Property(property="type", type="string", enum={"message", "post_it", "nudge"}, example="nudge"),
     *             @OA\Property(property="content", type="string", nullable=true, example="Hey there!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Icebreaker sent successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Must like user first"
     *     )
     * )
     */
    public function sendIcebreaker(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'type' => 'required|in:message,post_it,nudge',
            'content' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        // Check if the user has liked the receiver (can't send icebreaker without liking)
        $swipe = UserSwipe::where('user_id', $user->id)
            ->where('target_user_id', $request->receiver_id)
            ->whereIn('action', ['like', 'super_like'])
            ->first();

        if (!$swipe) {
            return response()->json(['status' => 'error', 'message' => 'You must like the user before sending an icebreaker.'], 403);
        }

        $icebreaker = UserIcebreaker::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'type' => $request->type,
            'content' => $request->input('content')
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $icebreaker,
            'message' => 'Icebreaker sent successfully.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/matching/matches",
     *     summary="Get user's matches",
     *     tags={"Matching"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function matches(Request $request)
    {
        $user = Auth::user();

        $matches = User::whereHas('swipes', function ($q) use ($user) {
            $q->where('target_user_id', $user->id)->where('is_match', true);
        })->get();

        return response()->json([
            'status' => 'success',
            'data' => $matches
        ]);
    }
}
