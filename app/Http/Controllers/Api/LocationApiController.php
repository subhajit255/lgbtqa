<?php

namespace App\Http\Controllers\Api;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class LocationApiController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/locations",
     *     summary="List all approved locations",
     *     tags={"Locations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of locations fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Locations fetched successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $locations = Location::where('is_active', 1)
            ->whereIn('status', ['APPROVED', 'PUBLISHED'])
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Locations fetched successfully',
            'data' => $locations
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/locations/create",
     *     summary="Create a new location",
     *     tags={"Locations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "address"},
     *                 @OA\Property(property="name", type="string", description="Location name"),
     *                 @OA\Property(property="description", type="string", description="Optional description"),
     *                 @OA\Property(property="address", type="string", description="Verified address or map pin"),
     *                 @OA\Property(property="lat", type="number", format="float"),
     *                 @OA\Property(property="lng", type="number", format="float"),
     *                 @OA\Property(property="hours", type="string", description="Opening hours"),
     *                 @OA\Property(property="official_link", type="string", description="Official website URL")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Location submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Location submitted for review successfully"),
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
            'address' => 'required|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'hours' => 'nullable|string|max:255',
            'official_link' => 'nullable|url|max:255',
        ]);

        DB::beginTransaction();
        try {
            $location = Location::create([
                "name" => $request->name,
                "user_id" => auth()->id(),
                "description" => $request->description,
                "address" => $request->address,
                "lat" => $request->lat,
                "lng" => $request->lng,
                "hours" => $request->hours,
                "official_link" => $request->official_link,
                "is_active" => 1,
                "status" => "SUBMITTED"
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Location submitted for review successfully',
                'data' => $location
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to submit location: ' . $th->getMessage()
            ], 500);
        }
    }
}
