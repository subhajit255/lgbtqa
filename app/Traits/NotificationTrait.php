<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * Trait FlashMessages
 */
trait NotificationTrait
{
    public function saveNotification($data)
    {
        try {
            $notificationSave = Notification::create($data);

            return $notificationSave;
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');

            return $this->responseJson($status, $code, $message, $response);
        }
    }

    public function getAllNotifications()
    {
        try {
            $allNotification = Notification::where('user_id', Auth::user()->id)->latest()->get();
            if ($allNotification) {
                return $allNotification;
            } else {
                return [];
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');

            return $this->responseJson($status, $code, $message, $response);
        }
    }

    public function countUnReadNotification()
    {
        try {
            $allNotificationCount = Notification::where(['user_id' => Auth::user()->id, 'is_read' => 0])->count();
            if ($allNotificationCount) {
                return $allNotificationCount;
            } else {
                return 0;
            }
        } catch (\Throwable $th) {
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');

            return $this->responseJson($status, $code, $message, $response);
        }
    }
}
