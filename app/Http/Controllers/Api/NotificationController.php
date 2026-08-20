<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get notifications list",
     *     description="Retrieve list of notifications for the authenticated user, ordered DESC. Supports filtering by read/unread status.",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status: 'read', 'unread', or 'all'",
     *         required=false,
     *         @OA\Schema(type="string", enum={"read", "unread", "all"}, default="all")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Notifications retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $statusFilter = $request->query('status', 'all');

            $query = Notification::with('referencedUser')
                ->where('user_id', $user->id)
                ->where('is_active', 1)
                ->where('is_read', 0)
                ->latest(); // Descending order by created_at

            if ($statusFilter === 'unread') {
                $query->where('is_read', 0);
            } elseif ($statusFilter === 'read') {
                $query->where('is_read', 1);
            }

            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $notificationsPaginator = $query->paginate($perPage, ['*'], 'page_number', $page);

            $notificationsPaginator->through(function ($notification) {
                if ($notification->referenced_user_id && $notification->referencedUser) {
                    $notification->reference_user_details = [
                        'id' => $notification->referencedUser->id,
                        'name' => $notification->referencedUser->name,
                        'username' => $notification->referencedUser->username,
                        'profile_image' => $notification->referencedUser->image_path,
                    ];
                } else {
                    $notification->reference_user_details = null;
                }

                // Hide the loaded relationship to keep response clean if desired
                $notification->makeHidden('referencedUser');

                return $notification;
            });

            return $this->responseJsonPaginated(true, 200, 'Notifications retrieved successfully', $notificationsPaginator);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/{id}/read",
     *     summary="Mark a specific notification as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Notification marked as read")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Notification::where('user_id', Auth::id())
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return $this->responseJson(false, 404, 'Notification not found', []);
            }

            $notification->update(['is_read' => 1]);

            return $this->responseJson(true, 200, 'Notification marked as read', $notification);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/read-all",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="All notifications marked as read")
     *         )
     *     )
     * )
     */
    public function markAllAsRead(Request $request)
    {
        try {
            Notification::where('user_id', Auth::id())
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            return $this->responseJson(true, 200, 'All notifications marked as read', []);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }
}
