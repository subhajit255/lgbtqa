<?php

namespace App\Http\Controllers\Admin;

use App\Models\Hobby;
use App\Models\HobbyItem;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class HobbyController extends BaseController
{
    use CommonFunction;

    public function index(Request $request)
    {
        $details = Hobby::latest()->get();
        return view('admin.hobby.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'title' => 'required|string|unique:hobbies,title,' . $id,
                    'type' => 'nullable|integer|in:1,2,3,4',
                ]);
                $message = "Hobby Updated Successfully";
            } else {
                $request->validate([
                    'title' => 'required|string|unique:hobbies,title',
                    'type' => 'nullable|integer|in:1,2,3,4',
                ]);
                $message = "Hobby Created Successfully";
            }

            DB::beginTransaction();
            try {
                $postData = [
                    "title" => $request->title,
                    "type" => $request->type ?? 1,
                    "is_active" => $request->is_active ?? 1,
                ];

                $hobby = Hobby::updateOrCreate(['id' => $id], $postData);

                // Handle Hobby Items
                if (isset($request->item_name) && is_array($request->item_name)) {
                    $existingItemIds = [];
                    foreach ($request->item_name as $key => $name) {
                        if (!empty($name)) {
                            $itemId = $request->item_id[$key] ?? null;
                            $itemData = [
                                'hobby_id' => $hobby->id,
                                'name' => $name,
                                'is_active' => $request->item_status[$key] ?? 1,
                            ];
                            
                            $item = HobbyItem::updateOrCreate(['id' => $itemId], $itemData);
                            $existingItemIds[] = $item->id;
                        }
                    }
                    // Delete items not in the request
                    HobbyItem::where('hobby_id', $hobby->id)->whereNotIn('id', $existingItemIds)->delete();
                }

                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }
            $data = ['status' => true, 'message' => $message, 'data' => $hobby ?? null, 'url' => route('admin.hobby.list')];
            return response($data);
        }

        $details = null;
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'hobbies');
            $details = Hobby::with('items')->find($uuid);
        }

        return view('admin.hobby.add', compact('details'));
    }
}
