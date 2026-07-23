<?php

namespace App\Http\Controllers\Admin;

use App\Models\Blog;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class BlogController extends BaseController
{
    use CommonFunction;
    use UploadAble;
    public function index(Request $request)
    {
        $details = Blog::latest()->get();
        return view('admin.blog.index', compact('details'));
    }
    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'title' => 'required|string',
                    'slug' => 'required|string',
                    'description' => 'required|string'
                ]);
                $message = "Blog Updated Successfully";
            } else {
                $request->validate([
                    'title' => 'required|string',
                    'slug' => 'required|string',
                    'description' => 'required|string',
                    'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
                ]);
                $message = "Blog Created Successfully";
            }

            DB::beginTransaction();
            try {
                $postData = [
                    "title" => $request->title,
                    "slug" => $request->slug,
                    "description" => $request->description,
                    "meta_data" => $request->meta_data,
                    "meta_title" => $request->meta_title,
                    "meta_description" => $request->meta_description,
                ];
                if (!empty($request->file)) {
                    $image = $request->file;
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_BLOG_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['file'] = $fileName;
                    }
                }
                $details = Blog::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }
            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.blog.list')];
            return response($data);
        }
        $details = array();
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'blogs');
            $details = Blog::find($uuid);
        }
        return view('admin.blog.add', compact('details'));
    }
}
