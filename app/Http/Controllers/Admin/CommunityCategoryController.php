<?php

namespace App\Http\Controllers\Admin;

use App\Models\CommunityCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class CommunityCategoryController extends BaseController
{
    public function index(Request $request)
    {
        $details = CommunityCategory::latest()->get();
        return view('admin.community_category.index', compact('details'));
    }

    public function add(Request $request)
    {
        if ($request->post()) {
            $id = $request->id ?? NULL;
            if (!empty($id)) {
                $request->validate([
                    'group' => 'required|string',
                ]);
                $message = "Community Category Updated Successfully";
            } else {
                $request->validate([
                    'group' => 'required|string',
                ]);
                $message = "Community Category Created Successfully";
            }
            
            $postData = [
                "group" => $request->group,
            ];
            
            if (empty($id)) {
                $postData['is_active'] = 1;
            }

            $details = CommunityCategory::updateOrCreate(['id' => $id], $postData);
            
            $data = ['status' => true, 'message' => $message, 'data' => $details, 'url' => route('admin.community-category.list')];
            return response()->json($data);
        }
        
        // Not needed for Modal
        return response()->json(['status' => false, 'message' => 'Invalid request']);
    }
    
    public function delete($id)
    {
        $category = CommunityCategory::find($id);
        if ($category) {
            $category->delete();
            return redirect()->back()->with('success', 'Deleted successfully');
        }
        return redirect()->back()->with('error', 'Category not found');
    }
}
