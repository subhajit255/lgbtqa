<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\User;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    use UploadAble;

    public function index()
    {
        $groups = Chat::where('is_group', true)->withCount('participants')->latest()->get();

        return view('admin.groups.index', compact('groups'));
    }

    public function create()
    {
        $users = User::all();

        return view('admin.groups.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
            'is_public' => 'boolean',
            'admin_id' => 'required|exists:users,id',
            'tags' => 'nullable|string',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $this->uploadOne($image, 'groups', $imageName);
        }

        DB::beginTransaction();
        try {
            $chat = Chat::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $imageName,
                'is_group' => true,
                'is_public' => $request->is_public ?? false,
                'admin_id' => $request->admin_id,
                'tags' => $request->tags,
            ]);

            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $request->admin_id,
                'role' => ChatParticipant::ROLE_ADMIN,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Group created successfully',
                'url' => route('admin.groups.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(Chat $group)
    {
        $users = User::all();

        return view('admin.groups.edit', compact('group', 'users'));
    }

    public function update(Request $request, Chat $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:102400',
            'is_public' => 'boolean',
            'admin_id' => 'required|exists:users,id',
            'tags' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $this->uploadOne($image, 'groups', $imageName);
            $group->image = $imageName;
        }

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $group->image,
            'is_public' => $request->is_public ?? false,
            'admin_id' => $request->admin_id,
            'tags' => $request->tags,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Group updated successfully',
            'url' => route('admin.groups.index'),
        ]);
    }

    public function destroy(Chat $group)
    {
        $group->delete();

        return redirect()->route('admin.groups.index')->with('success', 'Group deleted successfully');
    }

    public function getMembers(Chat $group)
    {
        $members = $group->participants()
            ->with('user:id,name,profile_image,email')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->user->id,
                    'name' => $participant->user->name,
                    'email' => $participant->user->email,
                    'profile_image' => $participant->user->image_path,
                    'role' => $participant->role,
                    'participant_id' => $participant->id
                ];
            });

        return response()->json(['status' => true, 'data' => $members]);
    }

    public function searchUsers(Request $request, Chat $group)
    {
        $search = $request->search;
        $currentMemberIds = $group->participants()->pluck('user_id')->toArray();

        $users = User::whereNotIn('id', $currentMemberIds)
            ->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'profile_image'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image_path' => $user->image_path
                ];
            });

        return response()->json(['status' => true, 'data' => $users]);
    }

    public function addMember(Request $request, Chat $group)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        if ($group->participants()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['status' => false, 'message' => 'User is already a member']);
        }

        ChatParticipant::create([
            'chat_id' => $group->id,
            'user_id' => $request->user_id,
            'role' => ChatParticipant::ROLE_MEMBER
        ]);

        return response()->json(['status' => true, 'message' => 'Member added successfully']);
    }

    public function removeMember(Chat $group, User $user)
    {
        if ($group->admin_id == $user->id) {
            return response()->json(['status' => false, 'message' => 'Cannot remove group admin']);
        }

        $group->participants()->where('user_id', $user->id)->delete();

        return response()->json(['status' => true, 'message' => 'Member removed successfully']);
    }

    public function toggleLock(Chat $group)
    {
        $group->update(['is_locked' => ! $group->is_locked]);

        return back()->with('success', 'Group '.($group->is_locked ? 'locked' : 'unlocked').' successfully');
    }
}
