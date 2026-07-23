<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Traits\CommonFunction;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController
{
    use CommonFunction;
    use UploadAble;

    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $query = User::with(['kycVerification.badgeStyle', 'kycVerification.badgeColor'])->where('user_type', 3);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        $details = $query->latest()->paginate(10);

        return view('admin.user.index', compact('details'));
    }

    public function getStates($country_id)
    {
        $states = [];
        $states = getStates($country_id);

        return response()->json($states);
    }

    public function getCities($stateId)
    {
        $states = [];
        $states = getCities($stateId);

        return response()->json($states);
    }

    public function add(Request $request)
    {
        $user = [];
        if ($request->post()) {
            $id = $request->id ?? null;
            if (! empty($id)) {
                $request->validate([
                    'name' => 'required|string',
                    'email' => 'required|string|unique:users,email,'.$id,
                    'mobile_number' => 'nullable|numeric|unique:users,mobile_number,'.$id,
                ]);
                $message = 'User Updated Successfully';
            } else {
                $request->validate([
                    'name' => 'required|string',
                    'email' => 'required|string|unique:users,email',
                    'mobile_number' => 'nullable|numeric|digits:10|unique:users,mobile_number',
                ]);
                $message = 'User Created Successfully';
            }

            DB::beginTransaction();
            try {
                $password = '12345678';
                $postData = [
                    'name' => $request->name,
                    'username' => $request->username,
                    'phone_code' => $request->phone_code ?? null,
                    'mobile_number' => $request->mobile_number ?? null,
                    'email' => $request->email,
                    'country_id' => $request->country_id ?? null,
                    'state_id' => $request->state_id ?? null,
                    'city_id' => $request->city_id ?? null,
                    'password' => bcrypt($password),
                ];
                if (! empty($request->file)) {
                    $image = $request->file;
                    $fileName = uniqid().'.'.$image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_PROFILE_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['profile_image'] = $fileName;
                    }
                }
                $user = User::updateOrCreate(['id' => $id], $postData);
                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');

                return $this->responseJson($status, $code, $message, $response);
            }

            $data = ['status' => true, 'message' => $message, 'data' => $user, 'url' => route('admin.user.list')];

            return response($data);
        }

        $details = [];
        if (! empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'users');
            $details = User::find($uuid);
        }

        return view('admin.user.add', compact('details'));
    }

    public function view($uuid)
    {
        $id = uuidtoid($uuid, 'users');
        $detail = User::with(['profile', 'hobbies.hobby', 'galleries'])->findOrFail($id);

        // Fetch location names from JSON helpers
        $countries = getCountries();
        $country = collect($countries)->where('id', $detail->country_id)->first();
        $detail->country_name = $country['name'] ?? 'N/A';

        $states = getStates($detail->country_id);
        $state = collect($states)->where('id', $detail->state_id)->first();
        $detail->state_name = $state['name'] ?? 'N/A';

        $cities = getCities($detail->state_id);
        $city = collect($cities)->where('id', $detail->city_id)->first();
        $detail->city_name = $city['name'] ?? 'N/A';

        $friends = $detail->friends()->with('profile')->get();
        $blockedUsers = $detail->blockedUsers()->with('profile')->get();

        return view('admin.user.view', compact('detail', 'friends', 'blockedUsers'));
    }

    public function delete($uuid)
    {
        $id = uuidtoid($uuid, 'users');
        $user = User::findOrFail($id);

        if ($user) {
            return $this->responseJson(true, 200, 'User Deleted Successfully');
        }

        return $this->responseJson(false, 404, 'User not found');
    }
}
