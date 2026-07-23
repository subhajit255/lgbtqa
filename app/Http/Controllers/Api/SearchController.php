<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\Community;
use App\Models\Event;
use App\Models\Post;
use App\Models\UserBlock;
use App\Http\Resources\Api\User\ProfileResource;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Global Search across people, communities, events, and posts",
     *     description="Search for entities by matching type (people, community, event, post, places). Includes filters for age range, max distance, verified profiles, what they're looking for, sexual orientation, relationship status, and interests.",
     *     tags={"Search"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keyword to match against entities (e.g. name/username/profile display name for people, name/description/tags for communities, title/description/location/host_name for events, title/description for posts)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Specific type of content to search. Allowed values: people, community, event, post, places. If null, returns all matching types.",
     *         required=false,
     *         @OA\Schema(type="string", enum={"people", "community", "event", "post", "places"})
     *     ),
     *     @OA\Parameter(
     *         name="min_age",
     *         in="query",
     *         description="Minimum age filter for people (e.g. 18)",
     *         required=false,
     *         @OA\Schema(type="integer", example=18)
     *     ),
     *     @OA\Parameter(
     *         name="max_age",
     *         in="query",
     *         description="Maximum age filter for people (e.g. 45)",
     *         required=false,
     *         @OA\Schema(type="integer", example=45)
     *     ),
     *     @OA\Parameter(
     *         name="max_distance",
     *         in="query",
     *         description="Maximum distance filter in kilometers for location/people (e.g. 50)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=50)
     *     ),
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         description="Latitude of current user location for distance calculation (e.g. 37.7749)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=37.7749)
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         in="query",
     *         description="Longitude of current user location for distance calculation (e.g. -122.4194)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=-122.4194)
     *     ),
     *     @OA\Parameter(
     *         name="verified_only",
     *         in="query",
     *         description="Filter only verified profiles (1 or true for verified profiles only, 0 or false for all)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="looking_for",
     *         in="query",
     *         description="Filter by what they are looking for. Accepts ID(s), name(s), array, or comma-separated string (e.g. 1, 2, 'Friends', 'Dating', 'Long-term relationship', 'Casual / nothing serious', 'Community / meetups')",
     *         required=false,
     *         @OA\Schema(type="string", example="1,2")
     *     ),
     *     @OA\Parameter(
     *         name="sexual_orientation",
     *         in="query",
     *         description="Filter by sexual orientation. Accepts ID(s), name(s), array, or comma-separated string (e.g. 1, 2, 'Gay', 'Lesbian', 'Bisexual', 'Pansexual', 'Straight', 'Queer')",
     *         required=false,
     *         @OA\Schema(type="string", example="Gay,Bisexual")
     *     ),
     *     @OA\Parameter(
     *         name="relationship_status",
     *         in="query",
     *         description="Filter by relationship status. Accepts ID(s), name(s), array, or comma-separated string (e.g. 1, 2, 'Single', 'Dating / seeing someone', 'In a relationship', 'Engaged', 'Married', 'Open relationship')",
     *         required=false,
     *         @OA\Schema(type="string", example="Single")
     *     ),
     *     @OA\Parameter(
     *         name="interests",
     *         in="query",
     *         description="Filter by interests/hobbies. Accepts ID(s), title(s), array, or comma-separated string (e.g. 1,2 or 'Music,Sports')",
     *         required=false,
     *         @OA\Schema(type="string", example="1,2")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Search results retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="people",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(
     *                     property="communities",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(
     *                     property="events",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(
     *                     property="posts",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:people,community,event,post,places',
            'search' => 'nullable|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0',
            'age_min' => 'nullable|integer|min:0',
            'age_max' => 'nullable|integer|min:0',
            'max_distance' => 'nullable|numeric|min:0',
            'distance' => 'nullable|numeric|min:0',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'verified_only' => 'nullable',
            'is_verified' => 'nullable',
            'verified_profiles_only' => 'nullable',
            'looking_for' => 'nullable',
            'what_i_am_looking_for' => 'nullable',
            'sexual_orientation' => 'nullable',
            'orientation' => 'nullable',
            'relationship_status' => 'nullable',
            'interests' => 'nullable',
            'hobbies' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), $validator->errors());
        }

        $search = $request->input('search');
        $type = $request->input('type');
        $userId = auth()->id();

        $data = [];

        // 1. People
        if (!$type || $type === 'people') {
            $query = User::where('id', '!=', $userId)
                ->where('user_type', 3)
                ->where('is_active', 1)
                ->where('is_blocked', 0);

            // Exclude blocked users
            if ($userId) {
                try {
                    $blockedIds = UserBlock::where('user_id', $userId)->pluck('blocked_user_id')
                        ->merge(UserBlock::where('blocked_user_id', $userId)->pluck('user_id'))
                        ->filter()->unique()->toArray();
                    if (!empty($blockedIds)) {
                        $query->whereNotIn('id', $blockedIds);
                    }
                } catch (\Exception $e) {
                    // Ignore if block table/model unavailable
                }
            }

            // Keyword Search
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%')
                      ->orWhereHas('profile', function ($pq) use ($search) {
                          $pq->where('display_name', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                      });
                });
            }

            // Filter: Age Range
            $minAge = $request->input('min_age') ?? $request->input('age_min') ?? $request->input('age_range_min');
            $maxAge = $request->input('max_age') ?? $request->input('age_max') ?? $request->input('age_range_max');

            if (!is_null($minAge) && $minAge !== '') {
                $minAgeInt = (int)$minAge;
                $query->whereHas('profile', function ($pq) use ($minAgeInt) {
                    $pq->where(function ($q) use ($minAgeInt) {
                        $q->where('age', '>=', $minAgeInt)
                          ->orWhereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?', [$minAgeInt]);
                    });
                });
            }

            if (!is_null($maxAge) && $maxAge !== '') {
                $maxAgeInt = (int)$maxAge;
                $query->whereHas('profile', function ($pq) use ($maxAgeInt) {
                    $pq->where(function ($q) use ($maxAgeInt) {
                        $q->where('age', '<=', $maxAgeInt)
                          ->orWhereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?', [$maxAgeInt]);
                    });
                });
            }

            // Filter: Max Distance
            $maxDistance = $request->input('max_distance') ?? $request->input('distance') ?? $request->input('distance_range');
            $lat = $request->input('lat') ?? auth()->user()?->lat;
            $lng = $request->input('lng') ?? auth()->user()?->lng;

            if (!is_null($maxDistance) && $maxDistance !== '') {
                $maxDistVal = (float)$maxDistance;
                if (!is_null($lat) && !is_null($lng) && $lat !== '' && $lng !== '') {
                    $latVal = (float)$lat;
                    $lngVal = (float)$lng;
                    $query->whereRaw(
                        "( 6371 * acos( cos( radians(?) ) * cos( radians( CAST(users.lat AS DECIMAL(10,6)) ) ) * cos( radians( CAST(users.lng AS DECIMAL(10,6)) ) - radians(?) ) + sin( radians(?) ) * sin( radians( CAST(users.lat AS DECIMAL(10,6)) ) ) ) ) <= ?",
                        [$latVal, $lngVal, $latVal, $maxDistVal]
                    );
                } else {
                    $query->whereHas('profile', function ($pq) use ($maxDistVal) {
                        $pq->where('distance_range', '<=', $maxDistVal);
                    });
                }
            }

            // Filter: Verified Profiles Only
            $verifiedOnly = $request->boolean('verified_only')
                || $request->boolean('verified_profiles_only')
                || $request->boolean('is_verified')
                || $request->boolean('verified_profiles')
                || $request->input('verified_only') === '1'
                || $request->input('verified_only') === 1;

            if ($verifiedOnly) {
                $query->where(function ($q) {
                    $q->whereHas('kycVerification', function ($kq) {
                        $kq->where('status', 'approved');
                    })->orWhereHas('profile', function ($pq) {
                        $pq->where('verified_profiles', 1);
                    })->orWhere('is_verified_email', 1);
                });
            }

            // Filter: What They're Looking For
            $lookingForRaw = $request->input('looking_for') ?? $request->input('what_theyre_looking_for') ?? $request->input('what_i_am_looking_for');
            if (!is_null($lookingForRaw) && $lookingForRaw !== '') {
                $lookingForList = $this->parseInputArray($lookingForRaw);
                $lookingForIds = [];
                $whatImLookingForMap = function_exists('getWhatImLookingFor') ? getWhatImLookingFor() : [];
                foreach ($lookingForList as $val) {
                    if (is_numeric($val)) {
                        $lookingForIds[] = (int)$val;
                    } else {
                        $valLower = strtolower(trim($val));
                        foreach ($whatImLookingForMap as $id => $name) {
                            if (strtolower($name) === $valLower || str_contains(strtolower($name), $valLower) || str_contains($valLower, strtolower($name))) {
                                $lookingForIds[] = (int)$id;
                            }
                        }
                    }
                }
                $lookingForIds = array_unique($lookingForIds);

                if (!empty($lookingForIds)) {
                    $query->whereHas('profile', function ($pq) use ($lookingForIds) {
                        $pq->whereIn('what_i_am_looking_for', $lookingForIds);
                        // Also check boolean flags if applicable
                        if (in_array(1, $lookingForIds)) { $pq->orWhere('friends', 1); }
                        if (in_array(2, $lookingForIds) || in_array(3, $lookingForIds)) { $pq->orWhere('dates', 1); }
                        if (in_array(8, $lookingForIds)) { $pq->orWhere('events_and_communities', 1); }
                        if (in_array(6, $lookingForIds)) { $pq->orWhere('hookups', 1); }
                    });
                }
            }

            // Filter: Sexual Orientation
            $orientationRaw = $request->input('sexual_orientation') ?? $request->input('orientation');
            if (!is_null($orientationRaw) && $orientationRaw !== '') {
                $orientationList = $this->parseInputArray($orientationRaw);
                $orientationIds = [];
                $orientationMap = function_exists('getOrientation') ? getOrientation() : [];
                foreach ($orientationList as $val) {
                    if (is_numeric($val)) {
                        $orientationIds[] = (int)$val;
                    } else {
                        $valLower = strtolower(trim($val));
                        foreach ($orientationMap as $id => $name) {
                            if (strtolower($name) === $valLower || str_contains(strtolower($name), $valLower) || str_contains($valLower, strtolower($name))) {
                                $orientationIds[] = (int)$id;
                            }
                        }
                    }
                }
                $orientationIds = array_unique($orientationIds);

                if (!empty($orientationIds)) {
                    $query->whereHas('profile', function ($pq) use ($orientationIds) {
                        $pq->whereIn('orientation', $orientationIds);
                    });
                }
            }

            // Filter: Relationship Status
            $relRaw = $request->input('relationship_status');
            if (!is_null($relRaw) && $relRaw !== '') {
                $relList = $this->parseInputArray($relRaw);
                $relStatusIds = [];
                $relMap = function_exists('getRelationshipStatus') ? getRelationshipStatus() : [];
                foreach ($relList as $val) {
                    if (is_numeric($val)) {
                        $relStatusIds[] = (int)$val;
                    } else {
                        $valLower = strtolower(trim($val));
                        foreach ($relMap as $id => $name) {
                            if (strtolower($name) === $valLower || str_contains(strtolower($name), $valLower) || str_contains($valLower, strtolower($name))) {
                                $relStatusIds[] = (int)$id;
                            }
                        }
                    }
                }
                $relStatusIds = array_unique($relStatusIds);

                if (!empty($relStatusIds)) {
                    $query->whereHas('profile', function ($pq) use ($relStatusIds) {
                        $pq->whereIn('relationship_status', $relStatusIds);
                    });
                }
            }

            // Filter: Interests / Hobbies
            $interestsRaw = $request->input('interests') ?? $request->input('hobbies');
            if (!is_null($interestsRaw) && $interestsRaw !== '') {
                $interestsList = $this->parseInputArray($interestsRaw);
                $interestIds = [];
                $interestNames = [];
                foreach ($interestsList as $val) {
                    if (is_numeric($val)) {
                        $interestIds[] = (int)$val;
                    } else {
                        $interestNames[] = trim($val);
                    }
                }

                if (!empty($interestIds) || !empty($interestNames)) {
                    $query->whereHas('hobbies', function ($hq) use ($interestIds, $interestNames) {
                        $hq->where(function ($q) use ($interestIds, $interestNames) {
                            if (!empty($interestIds)) {
                                $q->whereIn('hobby_items.id', $interestIds);
                            }
                            if (!empty($interestNames)) {
                                foreach ($interestNames as $name) {
                                    $q->orWhere('hobby_items.title', 'like', '%' . $name . '%');
                                }
                            }
                        });
                    });
                }
            }

            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;
            $paginations = [];

            $usersPaginator = $query->with(['profile', 'kycVerification', 'hobbies'])->latest()->paginate($perPage, ['*'], 'page_number', $page);
            $data['people'] = ProfileResource::collection($usersPaginator->items());
            $paginations['people'] = $this->getPaginatorMeta($usersPaginator);
        }

        // 2. Communities
        if (!$type || $type === 'community' || $type === 'places') {
            $query = Community::where('is_active', 1);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('tags', 'like', '%' . $search . '%');
                });
            }

            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $communitiesPaginator = $query->with('creator')->latest()->paginate($perPage, ['*'], 'page_number', $page);
            $data['communities'] = $communitiesPaginator->getCollection()->map(function ($community) use ($userId) {
                $community->members_count = $community->members()->where('status', 'active')->count();
                $membership = $community->members()->where('user_id', $userId)->first();
                $community->user_membership_status = $membership ? $membership->status : null;
                $community->user_role = $membership ? $membership->role : null;
                return $community;
            })->values()->all();
            $paginations['communities'] = $this->getPaginatorMeta($communitiesPaginator);
        }

        // 3. Events
        if (!$type || $type === 'event' || $type === 'places') {
            $query = Event::where('is_active', 1);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('location', 'like', '%' . $search . '%')
                      ->orWhere('host_name', 'like', '%' . $search . '%');
                });
            }

            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $eventsPaginator = $query->latest('event_date')->paginate($perPage, ['*'], 'page_number', $page);
            $data['events'] = $eventsPaginator->getCollection()->map(function ($event) use ($userId) {
                $event->joined_count = $event->joinedUsers()->count();
                $event->interested_count = $event->interestedUsers()->count();
                $event->user_joined = $event->joinedUsers()->where('user_id', $userId)->exists();
                $event->user_interested = $event->interestedUsers()->where('user_id', $userId)->exists();
                return $event;
            })->values()->all();
            $paginations['events'] = $this->getPaginatorMeta($eventsPaginator);
        }

        // 4. Posts
        if (!$type || $type === 'post') {
            $query = Post::where('status', 'active');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $perPage = $request->input('per_page') ?? 10;
            $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

            $postsPaginator = $query->with(['user.profile', 'user.kycVerification', 'media', 'loves', 'comments', 'stars', 'emojis'])->latest()->paginate($perPage, ['*'], 'page_number', $page);
            $data['posts'] = PostResource::collection($postsPaginator->items());
            $paginations['posts'] = $this->getPaginatorMeta($postsPaginator);
        }

        $response = [
            'status' => true,
            'response_code' => 200,
            'message' => 'Search results retrieved successfully',
            'data' => $data,
            'paginations' => $paginations
        ];

        if ($type) {
            if ($type === 'people' && isset($paginations['people'])) {
                $response['pagination'] = $paginations['people'];
            } elseif ($type === 'community' && isset($paginations['communities'])) {
                $response['pagination'] = $paginations['communities'];
            } elseif ($type === 'event' && isset($paginations['events'])) {
                $response['pagination'] = $paginations['events'];
            } elseif ($type === 'post' && isset($paginations['posts'])) {
                $response['pagination'] = $paginations['posts'];
            }
        }

        return response()->json($response);
    }

    /**
     * Parse input that can be string, json, array or comma-separated.
     */
    private function parseInputArray($input): array
    {
        if (is_null($input) || $input === '') {
            return [];
        }
        if (is_array($input)) {
            return array_values(array_filter($input, fn($v) => !is_null($v) && $v !== ''));
        }
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, fn($v) => !is_null($v) && $v !== ''));
            }
            return array_values(array_filter(array_map('trim', explode(',', $input)), fn($v) => $v !== ''));
        }
        return [(string)$input];
    }
}

