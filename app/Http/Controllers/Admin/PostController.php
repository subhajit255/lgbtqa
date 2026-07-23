<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\PostMedia;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class PostController extends BaseController
{
    use CommonFunction;
    use UploadAble;

    public function index(Request $request)
    {
        $details = Post::with(['user', 'media'])->latest()->get();
        return view('admin.post.index', compact('details'));
    }

    public function add(Request $request)
    {
        $details = [];
        if (!empty($request->uuid)) {
            $id = uuidtoid($request->uuid, 'posts');
            $details = Post::with('media')->find($id);
        }
        $users = User::where('user_type', 3)->orderBy('name')->get();
        return view('admin.post.add', compact('details', 'users'));
    }

    public function view($uuid)
    {
        $id = uuidtoid($uuid, 'posts');
        $detail = Post::with([
            'user', 
            'media', 
            'loves', 
            'stars', 
            'emojis', 
            'comments.user'
        ])->findOrFail($id);
        
        return view('admin.post.view', compact('detail'));
    }

    public function store(Request $request)
    {
        $id = $request->id ?? NULL;
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $postData = [
                'title' => $request->title,
                'description' => $request->description,
                'user_id' => $request->user_id,
                'status' => $request->status ?? 'active',
            ];

            $post = Post::updateOrCreate(['id' => $id], $postData);

            if ($request->has('media_files')) {
                foreach ($request->media_files as $file_name) {
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $file_type = in_array(strtolower($file_ext), ['mp4', 'mov', 'avi']) ? 'video' : 'image';
                    
                    PostMedia::create([
                        'post_id' => $post->id,
                        'file' => $file_name,
                        'file_type' => $file_type
                    ]);
                }
            }

            DB::commit();
            $message = !empty($id) ? "Post Updated Successfully" : "Post Created Successfully";
            return $this->responseJson(true, 200, $message, ['url' => route('admin.post.list')]);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }

    public function uploadMedia(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = config('constants.SITE_POST_UPLOAD_PATH');
            
            if ($this->uploadOne($file, $path, $fileName, 'public')) {
                return response()->json(['status' => true, 'file_name' => $fileName]);
            }
        }
        return response()->json(['status' => false, 'message' => 'Upload failed'], 400);
    }

    public function delete(Request $request)
    {
        $id = uuidtoid($request->uuid, 'posts');
        $post = Post::find($id);
        if ($post) {
            $post->delete();
            return $this->responseJson(true, 200, "Post Deleted Successfully");
        }
        return $this->responseJson(false, 404, "Post not found");
    }

    public function deleteMedia(Request $request)
    {
        $media = PostMedia::find($request->id);
        if ($media) {
            $this->deleteOne(config('constants.SITE_POST_UPLOAD_PATH') . $media->file);
            $media->delete();
            return response()->json(['status' => true, 'message' => 'Media deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Media not found'], 404);
    }
}
