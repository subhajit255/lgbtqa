<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class CategoryController extends BaseController
{
    use CommonFunction;
    use UploadAble;
    public function index(Request $request)
    {
        $details = Category::latest()->get();
        return view('admin.category.index', compact('details'));
    }
    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'title' => 'required|string',
                    'alias' => 'required|string|unique:categories,alias,' . $id,
                    'description' => 'required|string',
                    // 'parent_id' => 'sometimes|exists:categories,id',
                    'file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
                    'color_picker' => 'required|string|unique:categories,color_code,' . $id,
                ]);
                $message = "Category Updated Successfully";
            } else {
                $request->validate([
                    'title' => 'required|string',
                    'alias' => 'required|string|unique:categories,alias',
                    'description' => 'required|string',
                    // 'parent_id' => 'sometimes|exists:categories,id',
                    'file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
                    'color_picker' => 'required|string|unique:categories,color_code',
                ]);
                $message = "Category Created Successfully";
            }
            DB::beginTransaction();
            try {
                $postData = [
                    "title" => $request->title,
                    "alias" => $request->alias,
                    "parent_id" => $request->parent_id,
                    "description" => $request->description,
                    'color_code' => $request->color_picker,
                ];
                if (!empty($request->file)) {
                    $image = $request->file;
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_CATEGORY_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['file'] = $fileName;
                    }
                }
                $details = Category::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }
            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.category.list')];
            return response($data);
        }
        $details = array();
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'categories');
            $details = Category::find($uuid);
        }

        $categories = Category::all();
        $categoriesOption = $this->categoryDropdownOptions($categories);
        return view('admin.category.add', compact('categoriesOption', 'details'));
    }
}
