<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\PostCategoryResource;
use App\Http\Resources\PostResource;
use App\Models\Community;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostMedia;
use App\Traits\CommonFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseController
{
    use CommonFunction;

    /**
     * @OA\Get(
     *     path="/api/posts/feed",
     *     summary="Get the post feed",
     *     description="Retrieves a paginated list of posts, prioritizing the most recent ones. Includes relationships such as user, media, loves, comments, stars, and emojis.",
     *     operationId="feed",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Feed retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Feed retrieved successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="user_id", type="integer"),
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="profile_image", type="string")
     *                         ),
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *
     *                             @OA\Items(
     *                                 type="object",
     *
     *                                 @OA\Property(property="id", type="integer"),
     *                                 @OA\Property(property="file", type="string"),
     *                                 @OA\Property(property="file_type", type="string")
     *                             )
     *                         ),
     *                         @OA\Property(property="loves_count", type="integer"),
     *                         @OA\Property(property="comments_count", type="integer"),
     *                         @OA\Property(property="stars_count", type="integer"),
     *                         @OA\Property(property="emojis_count", type="integer")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="links",
     *                     type="object",
     *                     @OA\Property(property="first", type="string"),
     *                     @OA\Property(property="last", type="string"),
     *                     @OA\Property(property="prev", type="string", nullable=true),
     *                     @OA\Property(property="next", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="meta",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer"),
     *                     @OA\Property(property="from", type="integer"),
     *                     @OA\Property(property="last_page", type="integer"),
     *                     @OA\Property(property="path", type="string"),
     *                     @OA\Property(property="per_page", type="integer"),
     *                     @OA\Property(property="to", type="integer"),
     *                     @OA\Property(property="total", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function feed(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);

            $currentUser = $request->user();

            // Fetch posts ordered by created_at descending (recent first)
            $posts = Post::with(['user.profile', 'user.kycVerification', 'media', 'loves', 'comments', 'stars', 'emojis'])
                ->where('status', 'active')
                ->where(function ($query) use ($currentUser) {
                    $query->whereIn('user_id', function ($q) use ($currentUser) {
                        $q->select('friend_id')->from('friend_requests')
                            ->where('user_id', $currentUser->id)->where('status', 'accepted')
                            ->union(
                                $q->newQuery()->select('user_id')->from('friend_requests')
                                    ->where('friend_id', $currentUser->id)->where('status', 'accepted')
                            );
                    })->orWhere('user_id', $currentUser->id);
                })
                ->latest()
                ->paginate($perPage);

            $postsResource = PostResource::collection($posts);

            if ($request->input('page', 1) == 1) {
                return $postsResource->additional([
                    'status' => true,
                    'message' => 'Feed retrieved successfully!',
                    'communities' => $this->getRecommendedCommunities(),
                    'events' => $this->getRecommendedEvents(),
                    'nearby_users' => $this->getNearbyUsers(),
                    'matches' => $this->getMatches(),
                ]);
            }

            return $postsResource->additional([
                'status' => true,
                'message' => 'Feed retrieved successfully!',
            ]);
        } catch (\Exception $e) {
            return $this->responseJson(false, 500, 'Failed to retrieve feed.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/posts/post-categories",
     *     summary="Get list of active post categories",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search post categories by title or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Community hubs retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="is_active", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function postCategories(Request $request)
    {
        $query = PostCategory::where(['is_active' => 1]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $postCategories = $query->get();
        return $this->responseJson(
            true,
            200,
            'Post categories retrieved successfully',
            PostCategoryResource::collection($postCategories)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/posts/create",
     *     summary="Create a new post",
     *     description="Creates a new post with a title, description, and optional media files.",
     *     operationId="createPost",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 type="object",
     *
     *                 @OA\Property(property="title", type="string", description="Title of the post", example="My awesome weekend"),
     *                 @OA\Property(property="description", type="string", description="Detailed description", example="Had a great time at the beach!"),
     *                 @OA\Property(property="visibility", type="string", description="Visibility of the post (PUBLIC, FRIENDS, PRIVATE)", example="PUBLIC"),
     *                 @OA\Property(property="post_category_id", type="integer", description="Post category ID", example=1),
     *                 @OA\Property(
     *                     property="media[]",
     *                     type="array",
     *                     description="Attach images or videos (max 100MB each)",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Post created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post created successfully!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function createPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'nullable|in:PUBLIC,FRIENDS,PRIVATE',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi|max:102400', // 100MB limit
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('description') && !$request->hasFile('media')) {
                $validator->errors()->add('description', 'A post must have either text description or media files.');
            }
        });

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), $validator->errors());
        }

        try {
            DB::beginTransaction();

            $title = $request->title;
            if (empty($title)) {
                if ($request->filled('description')) {
                    $title = substr($request->description, 0, 30);
                } else {
                    $title = 'Post by ' . auth()->user()->name;
                }
            }

            $post = Post::create([
                'post_category_id' => $request->post_category_id ?? null,
                'user_id' => auth()->id(),
                'title' => $title,
                'description' => $request->description,
                'visibility' => $request->visibility ?? 'PUBLIC',
                'status' => 'active',
            ]);

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $fileType = in_array(strtolower($extension), ['mp4', 'mov', 'avi']) ? 'video' : 'image';

                    // Generate a unique filename
                    $filename = time() . '_' . uniqid() . '.' . $extension;

                    // Store file in public/uploads/posts
                    $file->move(public_path('assets/uploads/posts'), $filename);

                    PostMedia::create([
                        'post_id' => $post->id,
                        'file' => 'assets/uploads/posts/' . $filename,
                        'file_type' => $fileType,
                    ]);
                }
            }

            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Post Created',
                'description' => 'Your post "' . $title . '" has been created successfully.',
                'type' => 'post_create',
                'for' => 2,
                'is_read' => 0,
                'is_active' => 1,
            ]);

            DB::commit();

            $post->load(['user.profile', 'user.kycVerification', 'media', 'loves', 'comments', 'stars', 'emojis']);

            return response()->json([
                'status' => true,
                'message' => 'Post created successfully!',
                'data' => new PostResource($post)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseJson(false, 500, 'Failed to create post. Please try again.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/posts/update/{id}",
     *     summary="Update an existing post",
     *     description="Update the title, description, and manage media of a post. Because PHP doesn't handle multipart/form-data well on PUT requests, we use POST.",
     *     operationId="updatePost",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID to update",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 type="object",
     *                 required={"title"},
     *
     *                 @OA\Property(property="title", type="string", description="Title of the post", example="Updated awesome weekend"),
     *                 @OA\Property(property="description", type="string", description="Detailed description", example="Added a new photo!"),
     *                 @OA\Property(
     *                     property="remove_media",
     *                     type="array",
     *                     description="Array of PostMedia IDs to remove",
     *
     *                     @OA\Items(type="integer")
     *                 ),
     *
     *                 @OA\Property(
     *                     property="new_media[]",
     *                     type="array",
     *                     description="Attach new images or videos (max 100MB each)",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post updated successfully!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only post owner can update"),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function updatePost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'remove_media' => 'nullable|array',
            'remove_media.*' => 'integer|exists:post_media,id',
            'new_media' => 'nullable|array',
            'new_media.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi|max:102400',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), $validator->errors());
        }

        $post = Post::find($id);

        if (! $post) {
            return $this->responseJson(false, 404, 'Post not found', []);
        }

        if ($post->user_id !== auth()->id()) {
            return $this->responseJson(false, 403, 'You are not authorized to update this post.', []);
        }

        try {
            DB::beginTransaction();

            $title = $request->title;
            if (empty($title)) {
                if ($request->filled('description')) {
                    $title = substr($request->description, 0, 30);
                } else {
                    $title = 'Post by ' . auth()->user()->name;
                }
            }

            $post->update([
                'title' => $title,
                'description' => $request->description,
            ]);

            // Handle removing old media
            if ($request->has('remove_media') && is_array($request->remove_media)) {
                $mediaToRemove = PostMedia::whereIn('id', $request->remove_media)
                    ->where('post_id', $post->id)
                    ->get();

                foreach ($mediaToRemove as $media) {
                    $filePath = public_path($media->file);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $media->delete();
                }
            }

            // Handle adding new media
            if ($request->hasFile('new_media')) {
                foreach ($request->file('new_media') as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $fileType = in_array(strtolower($extension), ['mp4', 'mov', 'avi']) ? 'video' : 'image';

                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $file->move(public_path('assets/uploads/posts'), $filename);

                    PostMedia::create([
                        'post_id' => $post->id,
                        'file' => 'assets/uploads/posts/' . $filename,
                        'file_type' => $fileType,
                    ]);
                }
            }

            DB::commit();

            $post->load(['user.profile', 'user.kycVerification', 'media', 'loves', 'comments', 'stars', 'emojis']);

            return response()->json([
                'status' => true,
                'message' => 'Post updated successfully!',
                'data' => new PostResource($post)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseJson(false, 500, 'Failed to update post. Please try again.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/delete/{id}",
     *     summary="Delete a post",
     *     description="Soft deletes a specific post and removes it from the feed. Only the post author can perform this action.",
     *     operationId="deletePost",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID to delete",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post deleted successfully!")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only post owner can delete"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function deletePost($id)
    {
        $post = Post::find($id);

        if (! $post) {
            return $this->responseJson(false, 404, 'Post not found', []);
        }

        if ($post->user_id !== auth()->id()) {
            return $this->responseJson(false, 403, 'You are not authorized to delete this post.', []);
        }

        try {
            DB::beginTransaction();

            // Note: Since Post uses SoftDeletes, this will soft delete the post.
            // If we wanted to hard delete and clean up files immediately, we'd do $post->forceDelete() and unlink media files.
            $post->delete();

            DB::commit();

            return $this->responseJson(true, 200, 'Post deleted successfully!', []);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseJson(false, 500, 'Failed to delete post.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/posts/user/{userId}",
     *     summary="Get a user's post feed",
     *     description="Retrieves a paginated list of active posts created by a specific user.",
     *     operationId="userFeed",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID to fetch posts for",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User feed retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User feed retrieved successfully!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function userFeed(Request $request, $userId)
    {
        try {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return $this->responseJson(false, 404, 'User not found.', []);
            }

            $perPage = $request->input('per_page', 15);

            $posts = Post::with(['user.profile', 'user.kycVerification', 'media', 'loves', 'comments', 'stars', 'emojis'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->latest()
                ->paginate($perPage);

            return PostResource::collection($posts)->additional([
                'status' => true,
                'message' => 'User feed retrieved successfully!',
            ]);
        } catch (\Exception $e) {
            return $this->responseJson(false, 500, 'Failed to retrieve user feed.', ['error' => $e->getMessage()]);
        }
    }

    private function getRecommendedCommunities()
    {
        return Community::where('is_active', 1)
            ->withCount(['members' => function ($q) {
                $q->where('status', 'active');
            }])
            ->with('creator')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($c) {
                $category = 'LGBTQ+ Community';
                if (stripos($c->name, 'Lesbian') !== false || stripos($c->description, 'Lesbian') !== false) {
                    $category = 'Lesbian Community';
                }

                return [
                    'id' => $c->id,
                    'uuid' => $c->uuid,
                    'name' => $c->name,
                    'image_path' => $c->image_path,
                    'type' => $c->type,
                    'tags' => $c->tags,
                    'members_count' => $c->members_count,
                    'category' => $category,
                    'creator_username' => $c->creator->name ?? 'system',
                    'time_created_diff' => $c->created_at ? $c->created_at->diffForHumans() : 'now',
                ];
            });
    }

    private function getRecommendedEvents()
    {
        return Event::where('is_active', 1)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($e) {
                $dateFormatted = $e->event_date;
                try {
                    $dateFormatted = \Carbon\Carbon::parse($e->event_date)->format('M d • D');
                } catch (\Throwable $err) {
                }

                $attendees = $e->joinedUsers()
                    ->take(3)
                    ->get()
                    ->map(function ($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->name,
                            'image_path' => $u->image_path,
                        ];
                    });

                return [
                    'id' => $e->id,
                    'uuid' => $e->uuid,
                    'title' => $e->title,
                    'description' => $e->description,
                    'image_path' => $e->image_path,
                    'event_date_formatted' => $dateFormatted,
                    'location' => $e->location,
                    'going_count' => $e->joinedUsers()->count(),
                    'time_range' => "{$e->start_time} – {$e->end_time}",
                    'attendees' => $attendees,
                ];
            });
    }

    private function getNearbyUsers()
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return [];
        }

        return \App\Models\User::where('id', '!=', $currentUser->id)
            ->whereHas('profile')
            ->with('profile')
            ->take(5)
            ->get()
            ->map(function ($u) {
                // Mock slightly offset coordinates close to Zurich (Switzerland base)
                $latBase = 47.3769;
                $lngBase = 8.5417;
                $randOffsetLat = rand(-200, 200) / 10000;
                $randOffsetLng = rand(-200, 200) / 10000;

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'image_path' => $u->image_path,
                    'city' => $u->profile->living_in_city ?? 'Zurich',
                    'country' => $u->profile->living_in_country ?? 'Switzerland',
                    'latitude' => $latBase + $randOffsetLat,
                    'longitude' => $lngBase + $randOffsetLng,
                ];
            });
    }

    private function getMatches()
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return [];
        }

        $currentUserHobbies = $currentUser->hobbies->pluck('id')->toArray();

        return \App\Models\User::where('id', '!=', $currentUser->id)
            ->whereHas('profile')
            ->with(['profile', 'hobbies'])
            ->take(5)
            ->get()
            ->map(function ($u) use ($currentUserHobbies) {
                $otherUserHobbies = $u->hobbies->pluck('id')->toArray();
                $overlap = count(array_intersect($currentUserHobbies, $otherUserHobbies));

                // Match score algorithm: base 70% + 5% per common hobby, capped at 98%
                $score = min(98, 70 + ($overlap * 5));
                if ($score == 70) {
                    $score = rand(75, 95); // default fallback
                }

                $dob = $u->profile->dob;
                $age = $u->profile->age;
                if (!$age && $dob) {
                    try {
                        $age = \Carbon\Carbon::parse($dob)->age;
                    } catch (\Throwable $err) {
                    }
                }

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'age' => $age ?? rand(20, 30),
                    'image_path' => $u->image_path,
                    'city' => $u->profile->living_in_city ?? 'Zurich',
                    'country' => $u->profile->living_in_country ?? 'Switzerland',
                    'match_percentage' => "{$score}% match",
                ];
            });
    }
}
