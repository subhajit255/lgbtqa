<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $query = Status::with(['user', 'taggedUser'])->withCount(['reactions', 'comments']);

        if ($request->filter == 'active') {
            $query->active();
        } elseif ($request->filter == 'expired') {
            $query->where('expires_at', '<=', now());
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            });
        }

        $statuses = $query->latest()->paginate(16);
        return view('admin.status.index', compact('statuses'));
    }

    public function students(Request $request)
    {
        $query = \App\Models\User::whereHas('statuses')
            ->withCount(['statuses as total_statuses', 'statuses as active_statuses' => function($q) {
                $q->active();
            }])
            ->with(['statuses' => function($q) {
                $q->latest()->limit(1);
            }]);

        if ($request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
        }

        $students = $query->latest()->paginate(12);
        return view('admin.status.students', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tagged_user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:text,image,video',
            'content' => 'required_if:type,text|nullable|string',
            'background_color' => 'nullable|string',
            'media_file' => 'required_if:type,image|required_if:type,video|nullable|file'
        ]);

        $content = $request->content;

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/statuses', $fileName);
            $content = str_replace('public/', '', $path);
        }

        Status::create([
            'user_id' => $request->user_id,
            'tagged_user_id' => $request->tagged_user_id,
            'type' => $request->type,
            'content' => $content,
            'background_color' => $request->background_color,
            'expires_at' => now()->addHours(24),
            'is_active' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Status posted successfully!'
        ]);
    }

    public function searchUsers(Request $request)
    {
        $search = $request->search;
        $users = \App\Models\User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function destroy(Status $status)
    {
        if ($status->type !== 'text' && $status->content) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($status->content);
        }
        
        $status->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Status deleted successfully!'
        ]);
    }

    public function getDetails(Status $status)
    {
        $status->load(['reactions.user', 'comments.user']);
        return response()->json([
            'status' => true,
            'reactions' => $status->reactions,
            'comments' => $status->comments
        ]);
    }
}

