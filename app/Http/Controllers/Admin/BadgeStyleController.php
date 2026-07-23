<?php

namespace App\Http\Controllers\Admin;

use App\Models\BadgeStyle;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class BadgeStyleController extends BaseController
{
    use CommonFunction;
    use UploadAble;

    public function index(Request $request)
    {
        $details = BadgeStyle::latest()->get();
        return view('admin.badge-style.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'name' => 'required|string|unique:badge_styles,name,' . $id,
                    'file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp',
                ]);
                $message = "Badge Style Updated Successfully";
            } else {
                $request->validate([
                    'name' => 'required|string|unique:badge_styles,name',
                    'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp',
                ]);
                $message = "Badge Style Created Successfully";
            }

            DB::beginTransaction();
            try {
                $postData = [
                    "name" => $request->name,
                ];

                if (!empty($request->file)) {
                    $image = $request->file;
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_BADGE_STYLE_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['icon'] = $fileName;
                    }
                }

                $details = BadgeStyle::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }

            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.badge-style.list')];
            return response($data);
        }

        $details = array();
        if (!empty($request->uuid)) {
            $details = BadgeStyle::where('uuid', $request->uuid)->first();
        }

        return view('admin.badge-style.add', compact('details'));
    }

    public function delete($id)
    {
        $badgeStyle = BadgeStyle::find($id);
        if ($badgeStyle) {
            $badgeStyle->delete();
            return redirect()->back()->with('success', 'Badge Style deleted successfully');
        }
        return redirect()->back()->with('error', 'Badge Style not found');
    }
}
