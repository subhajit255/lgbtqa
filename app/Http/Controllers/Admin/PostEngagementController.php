<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLove;
use App\Models\PostStar;
use App\Models\PostEmoji;

class PostEngagementController extends BaseController
{
    public function list($uuid)
    {
        $id = uuidtoid($uuid, 'posts');
        $post = Post::with([
            'media',
            'comments' => function($q) { $q->whereNull('parent_id')->orderBy('created_at', 'desc'); },
            'comments.user',
            'comments.replies.user',
            'loves.user', 
            'stars.user', 
            'emojis.user'
        ])->find($id);

        if (!$post) {
            abort(404);
        }

        return view('admin.post.engagements', compact('post'));
    }

    public function deleteComment($id)
    {
        $comment = PostComment::find($id);
        if ($comment) {
            $comment->delete();
            return $this->responseJson(true, 200, "Comment Deleted Successfully");
        }
        return $this->responseJson(false, 404, "Comment not found");
    }

    public function deleteLove($id)
    {
        $love = PostLove::find($id);
        if ($love) {
            $love->delete();
            return $this->responseJson(true, 200, "Love reaction Deleted Successfully");
        }
        return $this->responseJson(false, 404, "Not found");
    }

    public function deleteStar($id)
    {
        $star = PostStar::find($id);
        if ($star) {
            $star->delete();
            return $this->responseJson(true, 200, "Star rating Deleted Successfully");
        }
        return $this->responseJson(false, 404, "Not found");
    }

    public function deleteEmoji($id)
    {
        $emoji = PostEmoji::find($id);
        if ($emoji) {
            $emoji->delete();
            return $this->responseJson(true, 200, "Emoji reaction Deleted Successfully");
        }
        return $this->responseJson(false, 404, "Not found");
    }

    public function addEngagement(Request $request)
    {
        $request->validate([
            'post_id' => 'required',
            'user_id' => 'required|exists:users,id',
            'engagement_type' => 'required|in:love,comment,star,emoji',
        ]);

        $postId = $request->post_id;
        $userId = $request->user_id;
        $type = $request->engagement_type;
        $post = Post::find($postId);

        if (!$post) return $this->responseJson(false, 404, "Post not found");

        if ($type === 'love') {
            PostLove::firstOrCreate(['post_id' => $postId, 'user_id' => $userId]);
        } elseif ($type === 'comment') {
            $request->validate(['comment_text' => 'required|string']);
            PostComment::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'comment' => $request->comment_text
            ]);
        } elseif ($type === 'star') {
            $request->validate(['star_count' => 'required|integer|min:1|max:5']);
            PostStar::updateOrCreate(
                ['post_id' => $postId, 'user_id' => $userId],
                ['star_count' => $request->star_count]
            );
        } elseif ($type === 'emoji') {
            $request->validate(['emoji_code' => 'required|string']);
            PostEmoji::updateOrCreate(
                ['post_id' => $postId, 'user_id' => $userId, 'emoji' => $request->emoji_code],
                []
            );
        }

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget("post_{$postId}_engagements");

        return $this->responseJson(true, 200, ucfirst($type) . " added successfully");
    }

    public function searchUsers(Request $request)
    {
        $search = $request->term;
        $users = \App\Models\User::where('user_type', '!=', 1)
            ->where(function($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                }
            })
            ->limit(20)
            ->get(['id', 'name', 'email', 'profile_image']);

        $formatted = [];
        foreach ($users as $user) {
            $formatted[] = [
                'id' => $user->id,
                'text' => $user->name . ' (' . $user->email . ')'
            ];
        }

        return response()->json(['results' => $formatted]);
    }
}
