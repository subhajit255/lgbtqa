<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class BannerController extends BaseController
{
    use CommonFunction;
    use UploadAble;
    public function index(Request $request)
    {
        $details = Banner::latest()->get();
        return view('admin.banner.index', compact('details'));
    }
    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'title' => 'required|string',
                    'url' => 'required|string',
                    'description' => 'required|string'
                ]);
                $message = "Banner Updated Successfully";
            } else {
                $request->validate([
                    'title' => 'required|string',
                    'url' => 'required|string',
                    'description' => 'required|string',
                    'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
                ]);
                $message = "Banner Created Successfully";
            }

            DB::beginTransaction();
            try {
                $postData = [
                    "title" => $request->title,
                    "url" => $request->url,
                    "description" => $request->description,
                ];
                if (!empty($request->file)) {
                    $image = $request->file;
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_BANNER_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['file'] = $fileName;
                    }
                }
                $details = Banner::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }
            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.banner.list')];
            return response($data);
        }
        $details = array();
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'banners');
            $details = Banner::find($uuid);
        }
        return view('admin.banner.add', compact('details'));
    }
}
