<?php

namespace App\Http\Controllers\Admin;

use App\Models\Community;
use App\Models\CommunityCategory;
use App\Models\CommunityMember;
use App\Models\User;
use App\Traits\UploadAble;
use App\Traits\CommonFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class CommunityController extends BaseController
{
    use CommonFunction;
    use UploadAble;

    public function index(Request $request)
    {
        $details = Community::withCount(['members' => function($q) {
            $q->where('status', 'active');
        }])->latest()->get();
        return view('admin.community.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'creator_id' => 'required|exists:users,id',
                'type' => 'required|string|in:public,private',
                'tags' => 'nullable|string|max:255',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:community_categories,id',
            ];

            if (empty($id)) {
                $rules['file'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:102400';
                $message = "Community Created Successfully";
            } else {
                $rules['file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400';
                $message = "Community Updated Successfully";
            }

            $request->validate($rules);

            DB::beginTransaction();
            try {
                $postData = [
                    "name" => $request->name,
                    "description" => $request->description,
                    "creator_id" => $request->creator_id,
                    "type" => $request->type,
                    "tags" => $request->tags,
                    "is_active" => $request->is_active ?? 1,
                ];

                if ($request->hasFile('file')) {
                    $image = $request->file('file');
                    $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $isFileUploaded = $this->uploadOne($image, config('constants.SITE_COMMUNITY_IMAGE_UPLOAD_PATH'), $fileName, 'public');
                    if ($isFileUploaded) {
                        $postData['image'] = $fileName;
                    }
                }

                $community = Community::updateOrCreate(['id' => $id], $postData);

                if ($request->has('categories')) {
                    $community->categories()->sync($request->categories);
                }

                // Ensure the creator is registered as an active creator member
                CommunityMember::updateOrCreate(
                    [
                        'community_id' => $community->id,
                        'user_id' => $community->creator_id,
                    ],
                    [
                        'status' => 'active',
                        'role' => 'creator'
                    ]
                );

                DB::Commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $status = false;
                $code = 500;
                $response = errorLogAndReturn($th);
                $message = config('constants.CATCH_ERROR_MSG');
                return $this->responseJson($status, $code, $message, $response);
            }

            $data = ['status' => true, 'message' => $message, 'data' => $community ?? null, 'url' => route('admin.community.list')];
            return response($data);
        }

        $details = null;
        if (!empty($request->uuid)) {
            $uuid = uuidtoid($request->uuid, 'communities');
            $details = Community::find($uuid);
        }

        $users = User::orderBy('name')->get();
        $categories = CommunityCategory::where('is_active', 1)->get();

        return view('admin.community.add', compact('details', 'users', 'categories'));
    }

    public function view($uuid)
    {
        $communityId = uuidtoid($uuid, 'communities');
        $community = Community::with(['creator'])->findOrFail($communityId);

        $activeMembers = CommunityMember::with('user')
            ->where('community_id', $community->id)
            ->where('status', 'active')
            ->get();

        $pendingRequests = CommunityMember::with('user')
            ->where('community_id', $community->id)
            ->where('status', 'pending')
            ->get();

        return view('admin.community.view', compact('community', 'activeMembers', 'pendingRequests'));
    }

    public function approveMember($id)
    {
        $member = CommunityMember::findOrFail($id);
        $member->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Join request approved successfully.');
    }

    public function rejectMember($id)
    {
        $member = CommunityMember::findOrFail($id);
        $member->delete();

        return redirect()->back()->with('success', 'Join request rejected successfully.');
    }
}
