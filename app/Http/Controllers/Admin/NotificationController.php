<?php

namespace App\Http\Controllers\Admin;

use App\Traits\UploadAble;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use App\Http\Controllers\BaseController;

class NotificationController extends BaseController
{
    use CommonFunction;
    use UploadAble;
    public function index(Request $request)
    {
        $details = Notification::where('for', 1)->latest()->paginate(10);
        return view('admin.notification.index', compact('details'));
    }

    public function delete($id)
    {
        try {
            $notification = Notification::find($id);
            if ($notification) {
                $notification->delete();
                return response()->json(['status' => true, 'message' => 'Notification deleted successfully.']);
            }
            return response()->json(['status' => false, 'message' => 'Notification not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            if (is_array($ids) && count($ids) > 0) {
                Notification::whereIn('id', $ids)->delete();
                return response()->json(['status' => true, 'message' => 'Selected notifications deleted successfully.']);
            }
            return response()->json(['status' => false, 'message' => 'No notifications selected.'], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
