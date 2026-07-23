<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class GalleryController extends BaseController
{
    use CommonFunction;

    public function index(Request $request)
    {
        $users = User::where('user_type', 3)->orderBy('name')->get();
        
        $query = Gallery::with('user')->latest();

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        $details = $query->paginate(20);

        return view('admin.gallery.index', compact('details', 'users'));
    }

    public function delete(Request $request)
    {
        $id = uuidtoid($request->uuid, 'galleries');
        $gallery = Gallery::find($id);

        if ($gallery) {
            $gallery->delete();
            return response()->json(['status' => true, 'message' => 'Image deleted successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Image not found'], 404);
    }
}
