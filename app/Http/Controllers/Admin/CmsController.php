<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cms;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class CmsController extends BaseController
{
    use CommonFunction;
    use UploadAble;
    public function index(Request $request)
    {
        $details = Cms::latest()->get();
        return view('admin.cms.index', compact('details'));
    }
    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'title' => 'required|string',
                    'description' => 'required|string'
                ]);
                $message = "Cms Updated Successfully";
            } else {
                $request->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                    // 'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
                ]);
                $message = "Cms Created Successfully";
            }

            DB::beginTransaction();
            try {
                $postData = [
                    "title" => $request->title,
                    "alias" => !empty($id) ? (Cms::find($id)->alias ?? \Illuminate\Support\Str::slug($request->title)) : \Illuminate\Support\Str::slug($request->title),
                    "description" => $request->description,
                    "for_home" => $request->for_home ?? 0,
                ];
                if (!empty($request->file)) {
                    $image = $request->file;
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_CMS_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['file'] = $fileName;
                    }
                }
                $details = Cms::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }
            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.cms.list')];
            return response($data);
        }
        $details = array();
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'cms');
            $details = Cms::find($uuid);
        }
        return view('admin.cms.add', compact('details'));
    }
}
