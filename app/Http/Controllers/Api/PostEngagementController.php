<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLove;
use App\Models\PostComment;
use App\Models\PostStar;
use App\Models\PostEmoji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostEngagementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/utilities/emojis",
     *     summary="Get predefined emojis",
     *     tags={"Post Engagements"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getEmojis()
    {
        $emojis = [
            ['code' => 'LIKE', 'symbol' => '👍'],
            ['code' => 'LOVE', 'symbol' => '❤️'],
            ['code' => 'HAHA', 'symbol' => '😂'],
            ['code' => 'WOW', 'symbol' => '😮'],
            ['code' => 'SAD', 'symbol' => '😢'],
            ['code' => 'ANGRY', 'symbol' => '😡'],
        ];

        return response(['status' => true, 'data' => $emojis]);
    }

    /**
     * @OA\Post(
     *     path="/api/posts/{post_id}/love",
     *     summary="Toggle love on a post",
     *     tags={"Post Engagements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function toggleLove(Request $request, $postId)
    {
        $post = Post::find($postId);
        if (!$post) return response(['status' => false, 'message' => 'Post not found'], 404);

        $user = auth()->user();
        $love = PostLove::where('post_id', $postId)->where('user_id', $user->id)->first();
        
        if ($love) {
            $love->delete();
            $action = 'removed';
        } else {
            PostLove::create(['post_id' => $postId, 'user_id' => $user->id]);
            $action = 'added';
        }

        Cache::forget("post_{$postId}_engagements");

        return response(['status' => true, 'message' => "Love $action successfully."]);
    }

    /**
     * @OA\Post(
     *     path="/api/posts/{post_id}/comment",
     *     summary="Add a comment to a post",
     *     tags={"Post Engagements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="comment", type="string", example="Great post!"),
     *             @OA\Property(property="parent_id", type="integer", example=1, description="Optional parent comment ID for replies")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function addComment(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:post_comments,id'
        ]);

        $post = Post::find($postId);
        if (!$post) return response(['status' => false, 'message' => 'Post not found'], 404);

        $comment = PostComment::create([
            'post_id' => $postId,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id ?? null,
            'comment' => $request->comment
        ]);

        Cache::forget("post_{$postId}_engagements");

        return response(['status' => true, 'message' => "Comment added successfully.", 'data' => $comment]);
    }

    /**
     * @OA\Post(
     *     path="/api/posts/{post_id}/star",
     *     summary="Toggle a star rating on a post",
     *     tags={"Post Engagements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="star_count", type="integer", example=1, description="Optional. Defaults to 1.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function addStar(Request $request, $postId)
    {
        $request->validate(['star_count' => 'nullable|integer|min:1|max:5']);
        $post = Post::find($postId);
        if (!$post) return response(['status' => false, 'message' => 'Post not found'], 404);

        $user = auth()->user();
        $star = PostStar::where('post_id', $postId)->where('user_id', $user->id)->first();

        if ($star) {
            $star->delete();
            $action = 'removed';
            $data = null;
        } else {
            $data = PostStar::create([
                'post_id' => $postId, 
                'user_id' => $user->id,
                'star_count' => $request->star_count ?? 1
            ]);
            $action = 'added';
        }

        Cache::forget("post_{$postId}_engagements");

        return response(['status' => true, 'message' => "Star $action successfully.", 'data' => $data]);
    }

    /**
     * @OA\Post(
     *     path="/api/posts/{post_id}/emoji",
     *     summary="React to a post with an emoji",
     *     tags={"Post Engagements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="emoji", type="string", example="HAHA")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function addEmoji(Request $request, $postId)
    {
        $request->validate(['emoji' => 'required|string|max:50']);
        $post = Post::find($postId);
        if (!$post) return response(['status' => false, 'message' => 'Post not found'], 404);

        $emoji = PostEmoji::firstOrCreate(
            ['post_id' => $postId, 'user_id' => auth()->id(), 'emoji' => $request->emoji]
        );

        Cache::forget("post_{$postId}_engagements");

        return response(['status' => true, 'message' => "Emoji reacted successfully.", 'data' => $emoji]);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{post_id}/engagements",
     *     summary="Get post engagements (loves, comments, stars, emojis)",
     *     tags={"Post Engagements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getEngagements($postId)
    {
        $post = Post::find($postId);
        if (!$post) return response(['status' => false, 'message' => 'Post not found'], 404);

        $cacheKey = "post_{$postId}_engagements";
        $engagements = Cache::remember($cacheKey, 3600, function () use ($postId) {
            $loves = PostLove::where('post_id', $postId)->count();
            $starsAvg = PostStar::where('post_id', $postId)->avg('star_count') ?? 0;
            $starsCount = PostStar::where('post_id', $postId)->count();

            $emojis = PostEmoji::where('post_id', $postId)
                ->select('emoji', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('emoji')
                ->get();

            // Fetch top level comments with one level of replies for performance
            $comments = PostComment::with([
                'user' => function ($query) { $query->select('id', 'name', 'profile_image'); },
                'replies.user' => function ($query) { $query->select('id', 'name', 'profile_image'); }
            ])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

            return [
                'loves' => $loves,
                'stars' => [
                    'average' => round($starsAvg, 1),
                    'total_ratings' => $starsCount
                ],
                'emojis' => $emojis,
                'comments' => $comments,
            ];
        });

        return response(['status' => true, 'data' => $engagements]);
    }
}
