<?php

namespace App\Http\Controllers\Admin;

use App\Models\PostCategory;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Str;

class PostCategoryController extends BaseController
{
    use CommonFunction;
    use UploadAble;

    public function index(Request $request)
    {
        $details = PostCategory::where('is_default', 0)->latest()->get();
        return view('admin.post-category.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                    'file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
                    'is_active' => 'sometimes|boolean',
                ]);
                $message = "Post Category Updated Successfully";
            } else {
                $request->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                    'file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
                    'is_active' => 'sometimes|boolean',
                ]);
                $message = "Post Category Created Successfully";
            }

            DB::beginTransaction();
            try {
                $slug = Str::slug($request->title);
                // check uniqueness
                $originalSlug = $slug;
                $count = 1;
                while (PostCategory::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }

                $postData = [
                    "title" => $request->title,
                    "slug" => $slug,
                    "description" => $request->description,
                    "is_active" => $request->is_active ?? 1,
                    "is_default" => 0,
                ];

                if (!empty($request->file)) {
                    $image = $request->file;
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_POST_CATEGORY_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['image'] = $fileName;
                    }
                }

                $details = PostCategory::updateOrCreate(['id' => $id], $postData);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }

            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.post-category.list')];
            return response($data);
        }

        $details = null;
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'post_categories');
            $details = PostCategory::find($uuid);
        }

        return view('admin.post-category.add', compact('details'));
    }
}
