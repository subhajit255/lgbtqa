<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use \App\Models\VisitorHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VisitorHistoryApiController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/profile/visit",
     *     summary="Log a profile visit",
     *     tags={"Profile Visits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="visited_id",
     *         in="query",
     *         description="ID of the user being visited",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Visit logged successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logVisit(Request $request)
    {
        $visitor = auth()->user();
        if (!$visitor) {
            return $this->responseJson(false, 401, "Unauthorized", []);
        }

        $request->validate([
            'visited_id' => 'required|exists:users,id'
        ]);

        $visitedId = $request->visited_id;

        // Prevent self-visits
        if ($visitor->id == $visitedId) {
            return $this->responseJson(false, 400, "Cannot visit yourself", []);
        }

        // If visitor has hide_my_visits ON, they don't log an outgoing visit, or they leave a hidden visit?
        // "Core privacy remains free: OFF hides outgoing and incoming member-facing history. HIDE MY VISITS adds only non-reciprocal convenience."
        // Wait, earlier rule: "Product rule: OFF by default; ON is reciprocal with a 30-day visible history"
        // Wait, "OFF by default; ON is reciprocal". It means by default you can't see who visited you, and your visits are hidden.
        // Let's just log the visit anyway. The visibility logic will be handled when fetching.
        // Wait, "OFF clears member-visible history with no backfill". I already handled deletion in toggle.
        // So we log it if the visitor has hide_my_visits == 1. Wait, if hide_my_visits == 0 (OFF), they don't log outgoing? "OFF hides outgoing and incoming".
        if ($visitor->hide_my_visits == 0) {
            // We can still log it for admin purposes, but maybe not. Let's just log it if ON.
            // Wait, "HIDE MY VISITS adds only non-reciprocal convenience." This is for the paid perk.
            // But for now, if user has hide_my_visits == 1, they are participating in the reciprocal history.
        }

        // Let's just log the visit
        VisitorHistory::updateOrCreate(
            ['visitor_id' => $visitor->id, 'visited_id' => $visitedId],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return $this->responseJson(true, 200, "Visit logged successfully", []);
    }

    /**
     * @OA\Get(
     *     path="/api/profile/visitors",
     *     summary="Get list of users who visited the current user",
     *     tags={"Profile Visits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Visitors retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getVisitors(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, "Unauthorized", []);
        }

        if ($user->hide_my_visits == 0) {
            return $this->responseJson(false, 400, "Turn on visitor history to see who visited you", []);
        }

        // 30 days limit
        $dateLimit = now()->subDays(30);

        $visitors = VisitorHistory::with(['visitor:id,name,profile_image'])
            ->where('visited_id', $user->id)
            ->where('created_at', '>=', $dateLimit)
            ->whereHas('visitor', function ($query) {
                // Ensure the visitor also has hide_my_visits ON (reciprocal rule)
                $query->where('hide_my_visits', 1);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->responseJson(true, 200, "Visitors retrieved successfully", $visitors);
    }
}
