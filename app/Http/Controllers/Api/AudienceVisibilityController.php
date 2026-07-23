<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\AudienceVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AudienceVisibilityController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/audience-visibility",
     *     summary="Fetch Audience Visibility Preference",
     *     description="Retrieve the authenticated user's audience visibility preference. Returns 'open' by default if not set.",
     *     tags={"Audience Visibility"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Audience visibility fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Audience visibility fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="visibility", type="string", example="open"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-11T23:30:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-11T23:30:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            
            $audience = AudienceVisibility::firstOrCreate(
                ['user_id' => $userId],
                ['visibility' => 'open']
            );

            return $this->responseJson(true, 200, 'Audience visibility fetched successfully.', $audience);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/audience-visibility",
     *     summary="Add/Update Audience Visibility Preference",
     *     description="Add or update the authenticated user's audience visibility preference. Stored as text.",
     *     tags={"Audience Visibility"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"visibility"},
     *             @OA\Property(property="visibility", type="string", enum={"friends_only", "community", "open"}, example="friends_only", description="Friends Only, Community, or Open")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Audience visibility saved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Audience visibility saved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="visibility", type="string", example="friends_only"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-11T23:30:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-11T23:30:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="response_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The selected visibility is invalid."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function update(Request $request)
    {
        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'visibility' => 'required|string|in:friends_only,community,open',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $audience = AudienceVisibility::updateOrCreate(
                ['user_id' => $userId],
                ['visibility' => $request->visibility]
            );

            return $this->responseJson(true, 200, 'Audience visibility saved successfully.', $audience);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }
}
