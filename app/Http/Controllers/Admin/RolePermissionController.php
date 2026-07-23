<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionController extends BaseController
{
    use UploadAble;

    public function index()
    {
        $data['title'] = 'Role';
        $data['roles'] = Role::whereNotIn('slug', ['super-admin'])->get();

        return view('admin.role_permission.role', $data);
    }

    public function roleAdd(Request $request)
    {
        $id = $request->id ?? null;
        if (! empty($id)) {
            $request->validate([
                'name' => 'required|string|unique:roles,name,'.$id,
            ]);
            $message = 'Role Updated Successfully';
            $url = route('admin.role.list');
        } else {
            $request->validate([
                'name' => 'required|string|unique:roles,name',
            ]);
            $message = 'Role Created Successfully';
        }

        DB::beginTransaction();
        try {
            $postData = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
            ];
            $details = Role::updateOrCreate(['id' => $id], $postData);

            $url = route('admin.role.permission', $details->uuid);
            DB::Commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $status = false;
            $code = 500;
            $response = errorLogAndReturn($th);
            $message = config('constants.CATCH_ERROR_MSG');

            return $this->responseJson($status, $code, $message, $response);
        }
        $data = ['status' => true, 'message' => $message, 'data' => $details ?? null, 'url' => $url];

        return response($data);
    }

    public function rolePermission(Request $request, $id)
    {
        $title = 'Role & Permission';
        $roleData = Role::where('uuid', $request->uuid)->first();
        $permissions = Permission::whereNotIn('group_by', ['role_permissions'])->get()->groupBy('group_by');
        if ($request->post()) {
            $request->validate([
                'permission' => 'required|array',
            ]);
            try {
                $roleData->permissions()->detach();
                $isPermissionAttached = $roleData->givePermissionsTo((array) $request->permission);
                if ($isPermissionAttached) {
                    $roleData->update(['is_active' => 1]);
                    $status = true;
                    $code = 200;
                    $response = [];
                    $message = 'Permission attached successfully';
                }
            } catch (\Exception $th) {
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
            }
            $data = ['status' => true, 'message' => $message, 'data' => [], 'url' => route('admin.role.list')];

            return response($data);
        }

        return view('admin.role_permission.permission', compact('permissions', 'roleData', 'title'));
    }

    public function userList(Request $request)
    {
        $details = User::whereIn('user_type', [2])->latest()->get();

        return view('admin.role_permission.admin_user.index', compact('details'));
    }

    public function userAdd(Request $request)
    {
        $user = [];
        if ($request->post()) {
            $id = $request->id ?? null;
            if (! empty($id)) {
                $request->validate([
                    'role_id' => 'required|exists:roles,uuid',
                    'name' => 'required|string',
                    'email' => 'required|string|unique:users,email,'.$id,
                    'mobile_number' => 'required|numeric|unique:users,mobile_number,'.$id,
                ]);
                $message = 'User Updated Successfully';
            } else {
                $request->validate([
                    'role_id' => 'required|exists:roles,uuid',
                    'name' => 'required|string',
                    'email' => 'required|string|unique:users,email',
                    'mobile_number' => 'required|numeric|digits:10|unique:users,mobile_number',
                ]);
                $message = 'User Created Successfully';
            }

            DB::beginTransaction();
            try {
                $password = $request->mobile_number;
                $postData = [
                    'name' => $request->name,
                    'username' => $request->username,
                    'mobile_number' => $request->mobile_number,
                    'email' => $request->email,
                    'user_type' => 2,
                    'is_approve' => 1,
                    'is_verified_email' => 1,
                    'is_verified_phone' => 1,
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

                // role permission add
                $roleData = Role::where('uuid', $request->role_id)->first();
                $user->roles()->sync($roleData);
                $permissions = Permission::whereHas('roles', function ($q) use ($roleData) {
                    $q->where('slug', $roleData->slug);
                })->get();
                $user->permissions()->attach($permissions);
                // role permission add

                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');

                return $this->responseJson($status, $code, $message, $response);
            }

            $data = ['status' => true, 'message' => $message, 'data' => $user, 'url' => route('admin.role.user.list')];

            return response($data);
        }

        $details = [];
        if (! empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'users');
            $details = User::find($uuid);
        }

        return view('admin.role_permission.admin_user.add', compact('details'));
    }

    public function userRolePermission(Request $request)
    {
        $permissions = Permission::whereNotIn('group_by', ['role_permissions'])->get()->groupBy('group_by');
        $user = User::where('uuid', $request->uuid)->first();
        if ($request->post()) {
            $request->validate([
                'permission' => 'required|array',
            ]);
            try {
                $user->permissions()->sync($request->permission);
                $status = true;
                $code = 200;
                $response = [];
                $message = 'Permissions updated successfully for the user.';
            } catch (\Exception $th) {
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
            }
            $data = ['status' => true, 'message' => $message, 'data' => [], 'url' => route('admin.role.user.list')];

            return response($data);
        }
        $title = 'User Role & Permission';
        $roleData = $user->permissions()->get();

        return view('admin.role_permission.user_permission', compact('permissions', 'roleData', 'user'));
    }
}
