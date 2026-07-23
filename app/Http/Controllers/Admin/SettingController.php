<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class SettingController extends BaseController
{
    use CommonFunction;
    use UploadAble;
    public function index(Request $request)
    {
        if ($request->post()) {
            $message = "Settings Updated Successfully";

            DB::beginTransaction();
            try {
                $postData = array();

                if (!empty($request->instagram)) {
                    $postData['instagram'] = $request->instagram;
                }
                if (!empty($request->facebook)) {
                    $postData['facebook'] = $request->facebook;
                }
                if (!empty($request->twitter)) {
                    $postData['twitter'] = $request->twitter;
                }
                if (!empty($request->linkedin)) {
                    $postData['linkedin'] = $request->linkedin;
                }
                if (!empty($request->contact_email)) {
                    $postData['contact_email'] = $request->contact_email;
                }
                if (!empty($request->contact_number)) {
                    $postData['contact_number'] = $request->contact_number;
                }
                if (!empty($request->term_and_condition)) {
                    $postData['term_and_condition'] = $request->term_and_condition;
                }
                if (!empty($request->privacy_policy)) {
                    $postData['privacy_policy'] = $request->privacy_policy;
                }
                if (!empty($request->about_us)) {
                    $postData['about_us'] = $request->about_us;
                }
                if (!empty($request->child_safety)) {
                    $postData['child_safety'] = $request->child_safety;
                }

                $details = Setting::updateOrCreate(['id' => 1], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }
            $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => route('admin.setting.update')];
            return response($data);
        }
        $details = Setting::find(1);
        return view('admin.setting.index', compact('details'));
    }
}
