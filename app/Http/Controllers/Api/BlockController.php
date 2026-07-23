<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserBlock;
use App\Http\Resources\Api\User\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/users/blocked",
     *     summary="Get blocked users list",
     *     tags={"User Blocks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page_number",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blocked users fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Blocked users fetched successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function blockedList(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $blockedUsersPaginator = $user->blockedUsers()
                ->with('profile')
                ->paginate($perPage, ['*'], 'page_number', $page);

            $blockedUsersPaginator->through(fn($item) => new ProfileResource($item));

            return $this->responseJsonPaginated(
                true,
                200,
                'Blocked users fetched successfully.',
                $blockedUsersPaginator
            );
        } catch (\Throwable $th) {
            return $this->responseJson(
                false,
                500,
                config('constants.CATCH_ERROR_MSG'),
                errorLogAndReturn($th)
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users/block/{user_id}",
     *     summary="Block a user",
     *     tags={"User Blocks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="ID of the user to block",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User blocked successfully"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function blockUser(Request $request, $user_id)
    {
        $user = Auth::user();

        if ($user->id == $user_id) {
            return $this->responseJson(false, 400, 'You cannot block yourself.', []);
        }

        $userToBlock = User::find($user_id);
        if (!$userToBlock) {
            return $this->responseJson(false, 404, 'User not found.', []);
        }

        $alreadyBlocked = UserBlock::where('user_id', $user->id)->where('blocked_user_id', $user_id)->exists();
        if ($alreadyBlocked) {
            return $this->responseJson(false, 400, 'User is already blocked.', []);
        }

        UserBlock::create([
            'user_id' => $user->id,
            'blocked_user_id' => $user_id
        ]);

        return $this->responseJson(true, 200, 'User blocked successfully.', []);
    }

    /**
     * @OA\Post(
     *     path="/api/users/unblock/{user_id}",
     *     summary="Unblock a user",
     *     tags={"User Blocks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="ID of the user to unblock",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User unblocked successfully"),
     *     @OA\Response(response=404, description="Block record not found")
     * )
     */
    public function unblockUser(Request $request, $user_id)
    {
        $user = Auth::user();

        $blockRecord = UserBlock::where('user_id', $user->id)->where('blocked_user_id', $user_id)->first();

        if (!$blockRecord) {
            return $this->responseJson(false, 404, 'User is not blocked.', []);
        }

        $blockRecord->delete();

        return $this->responseJson(true, 200, 'User unblocked successfully.', []);
    }
}
