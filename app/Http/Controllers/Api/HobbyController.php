<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HobbyItemResource;
use App\Http\Resources\Api\HobbyResource;
use App\Models\Hobby;

class HobbyController extends Controller
{
    /**
     * Display a listing of hobbies.
     */
    /**
     * @OA\Get(
     *     path="/api/hobbies",
     *     summary="Get Hobbies",
     *     tags={"Hobbies"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Data Found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        $hobbies = Hobby::with(['items' => function ($query) {
            $query->where('is_active', 1);
        }])
            ->where('is_active', 1)
            ->whereIn('type', [1, 2, 3, 4])
            ->orderBy('id', 'asc')
            ->get();

        $grouped = $hobbies->groupBy('type');
        $formattedData = [];

        $pageKeys = [
            1 => 'hobbies',
            2 => 'lifestyle',
            3 => 'home_and_future',
            4 => 'your_vibe',
        ];

        for ($page = 1; $page <= 4; $page++) {
            $pageHobbies = $grouped->get($page, collect());
            $pageData = [];
            foreach ($pageHobbies as $hobby) {
                $pageData[] = [
                    'title' => $hobby->title,
                    'item' => HobbyItemResource::collection($hobby->items)->map(fn($item) => [
                        'id' => $item->id,
                        'uuid' => $item->uuid,
                        'name' => $item->name,
                    ])->values()->all(),
                ];
            }
            $formattedData[$pageKeys[$page]] = $pageData;
        }

        return response()->json([
            'status' => true,
            'message' => 'Hobbies retrieved successfully',
            'data' => $formattedData,
        ]);
    }
}
