<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\BaseController;
use App\Models\BadgeColor;
use App\Models\BadgeStyle;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Cms;
use App\Models\Hobby;
use App\Models\User;
use App\Models\Event;
use App\Models\Community;
use App\Models\CommunityMember;
use Illuminate\Http\Request;

class AjaxController extends BaseController
{
    public function deleteData(Request $request)
    {
        if ($request->ajax()) {
            $table = $request->find;
            switch ($table) {
                case 'users':
                    $id = uuidtoid($request->uuid, $table);
                    $data = User::find($id);
                    $data->delete();
                    $message = 'User Deleted';
                    break;
                case 'banners':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Banner::find($id);
                    $data->delete();
                    $message = 'Banner Deleted';
                    break;
                case 'cms':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Cms::find($id);
                    $data->delete();
                    $message = 'Cms Deleted';
                    break;
                case 'categories':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Category::find($id);
                    $data->delete();
                    $message = 'Category Deleted';
                    break;
                case 'hobbies':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Hobby::find($id);
                    $data->delete();
                    $message = 'Hobby Deleted';
                    break;
                case 'posts':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Post::find($id);
                    $data->delete();
                    $message = 'Post Deleted';
                    break;
                case 'badge_colors':
                    $id = uuidtoid($request->uuid, $table);
                    $data = BadgeColor::find($id);
                    $data->delete();
                    $message = 'Badge Color Deleted';
                    break;
                case 'badge_styles':
                    $id = uuidtoid($request->uuid, $table);
                    $data = BadgeStyle::find($id);
                    $data->delete();
                    $message = 'Badge Style Deleted';
                    break;
                case 'events':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Event::find($id);
                    $data->delete();
                    $message = 'Event Deleted';
                    break;
                case 'communities':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Community::find($id);
                    $data->delete();
                    $message = 'Community Deleted';
                    break;
                case 'community_members':
                    $id = $request->uuid;
                    $data = CommunityMember::find($id);
                    if ($data) {
                        $data->delete();
                    }
                    $message = 'Member Removed';
                    break;
            }
            if ($data) {
                return $this->responseJson(true, 200, $message);
            } else {
                return $this->responseJson(false, 200, 'Something Went Wrong');
            }
        } else {
            abort(403);
        }
    }

    public function statusChange(Request $request)
    {
        if ($request->ajax()) {
            $table = $request->find;
            $message = 'Status changed successfully';
            switch ($table) {
                case 'users':
                    $id = uuidtoid($request->uuid, $table);
                    $data = User::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;
                case 'banners':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Banner::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;
                case 'cms':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Cms::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;
                case 'categories':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Category::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;
                case 'hobbies':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Hobby::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;
                case 'posts':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Post::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;
                case 'badge_colors':
                    $id = uuidtoid($request->uuid, $table);
                    $data = BadgeColor::find($id);
                    $data->update(['status' => $request->status]);
                    break;
                case 'badge_styles':
                    $id = uuidtoid($request->uuid, $table);
                    $data = BadgeStyle::find($id);
                    $data->update(['status' => $request->status]);
                    break;
                case 'events':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Event::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;
                case 'communities':
                    $id = uuidtoid($request->uuid, $table);
                    $data = Community::find($id);
                    $data->update(['is_active' => $request->status]);
                    break;

            }
            if ($data) {
                return $this->responseJson(true, 200, $message);
            } else {
                return $this->responseJson(false, 200, 'Something Went Wrong');
            }
        } else {
            abort(403);
        }
    }
}
