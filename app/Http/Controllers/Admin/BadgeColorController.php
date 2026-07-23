<?php

namespace App\Http\Controllers\Admin;

use App\Models\BadgeColor;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class BadgeColorController extends BaseController
{
    use CommonFunction;

    public function index(Request $request)
    {
        $details = BadgeColor::latest()->get();
        return view('admin.badge-color.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'name' => 'required|string|unique:badge_colors,name,' . $id,
                    'color_code' => 'required|string',
                ]);
                $message = "Badge Color Updated Successfully";
            } else {
                $request->validate([
                    'name' => 'required|string|unique:badge_colors,name',
                    'color_code' => 'required|string',
                ]);
                $message = "Badge Color Created Successfully";
            }

            DB::beginTransaction();
            try {
                $postData = [
                    "name" => $request->name,
                    "color_code" => $request->color_code,
                ];

                $details = BadgeColor::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }

            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.badge-color.list')];
            return response($data);
        }

        $details = array();
        if (!empty($request->uuid)) {
            $details = BadgeColor::where('uuid', $request->uuid)->first();
        }

        return view('admin.badge-color.add', compact('details'));
    }

    public function delete($id)
    {
        $badgeColor = BadgeColor::find($id);
        if ($badgeColor) {
            $badgeColor->delete();
            return redirect()->back()->with('success', 'Badge Color deleted successfully');
        }
        return redirect()->back()->with('error', 'Badge Color not found');
    }
}
