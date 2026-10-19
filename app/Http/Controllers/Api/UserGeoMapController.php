<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\User\UserResource;
use App\Models\User;
use App\Models\Event;
use App\Models\UserFavouriteProfile;
use App\Models\UserLocation;
use App\Models\UserRecentSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserGeoMapController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/map/location",
     *     summary="Add or update current GPS location of authenticated user",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude", "longitude"},
     *             @OA\Property(property="latitude", type="number", format="float", example=40.7128, description="Latitude coordinate"),
     *             @OA\Property(property="longitude", type="number", format="float", example=-74.0060, description="Longitude coordinate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function addOrUpdateCurrentLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first());
        }

        $user = auth()->user();

        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $location = UserLocation::updateOrCreate(
            ['user_id' => $user->id],
            [
                'lat' => $request->latitude,
                'lng' => $request->longitude,
                'last_pinged_at' => now(),
            ]
        );

        return $this->responseJson(true, 200, 'Location updated successfully', $location);
    }

    /**
     * @OA\Get(
     *     path="/api/map/users",
     *     summary="Get users on the map based on filters and proximity",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="latitude", in="query", required=false, @OA\Schema(type="number", format="float"), description="Latitude of center point"),
     *     @OA\Parameter(name="longitude", in="query", required=false, @OA\Schema(type="number", format="float"), description="Longitude of center point"),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string"), description="Search keyword matching name, username, display name, bio, or hobbies"),
     *     @OA\Parameter(name="max_distance", in="query", required=false, @OA\Schema(type="integer"), description="Maximum distance in miles (optional)"),
     *     @OA\Parameter(name="audience", in="query", required=false, @OA\Schema(type="string", enum={"all", "connections", "connections_of_friends"}, default="all"), description="Filter by audience connection type"),
     *     @OA\Parameter(name="gender", in="query", required=false, @OA\Schema(type="string", example="1,2"), description="Filter by gender identities. Accepts single or multiple comma-separated IDs (e.g. 1,2). Suggestions: 1:Male, 2:Female, 3:Trans Man, 4:Trans Woman, 5:Non-binary, 6:Agender, 7:Genderfluid, 8:Genderqueer, 9:Other"),
     *     @OA\Parameter(name="hobbies", in="query", required=false, @OA\Schema(type="array", @OA\Items(type="integer")), description="Filter by hobby item IDs"),
     *     @OA\Parameter(name="age_min", in="query", required=false, @OA\Schema(type="integer", default=18), description="Minimum age filter"),
     *     @OA\Parameter(name="age_max", in="query", required=false, @OA\Schema(type="integer", default=100), description="Maximum age filter"),
     *     @OA\Parameter(name="verified_only", in="query", required=false, @OA\Schema(type="boolean"), description="Filter only verified profiles"),
     *     @OA\Parameter(name="preset", in="query", required=false, @OA\Schema(type="string", enum={"popular_nearby", "new_members", "verified_profiles", "connections", "favorites", "now_online", "recently_online", "updated_recently"}), description="Suggested search preset filter"),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=30, maximum=30), description="Maximum number of users to retrieve (at most 30)"),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully"
     *     )
     * )
     */
    public function getUsersOnMap(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $lat = $request->latitude;
        $lng = $request->longitude;

        // Fallback to current user location if lat/lng not provided
        if (empty($lat) || empty($lng)) {
            $userLocation = UserLocation::query()->where('user_id', $user->id)->first();
            if ($userLocation) {
                $lat = $userLocation->lat;
                $lng = $userLocation->lng;
            } else {
                return $this->responseJson(false, 422, 'Latitude and longitude are required or you must update your location first.');
            }
        }

        // Haversine formula in miles
        $haversine = "(3959 * acos(least(1, greatest(-1, cos(radians(?)) * cos(radians(user_locations.lat)) * cos(radians(user_locations.lng) - radians(?)) + sin(radians(?)) * sin(radians(user_locations.lat))))))";

        // Popularity score subquery (profile visits + favourites + likes/swipes + accepted friends)
        $popularityRaw = "((SELECT COUNT(*) FROM visitor_histories WHERE visitor_histories.visited_id = users.id) " .
            "+ (SELECT COUNT(*) FROM user_favourite_profiles WHERE user_favourite_profiles.favourite_user_id = users.id) * 2 " .
            "+ (SELECT COUNT(*) FROM user_swipes WHERE user_swipes.target_user_id = users.id AND user_swipes.action IN ('like', 'super_like')) * 2 " .
            "+ (SELECT COUNT(*) FROM friend_requests WHERE (friend_requests.user_id = users.id OR friend_requests.friend_id = users.id) AND friend_requests.status = 'accepted'))";

        $query = User::query()
            ->selectRaw("users.*, {$haversine} as distance, {$popularityRaw} as popularity_score", [$lat, $lng, $lat])
            ->join('user_locations', 'users.id', '=', 'user_locations.user_id')
            ->where('users.id', '!=', $user->id);

        if ($request->filled('max_distance') && is_numeric($request->max_distance)) {
            $query->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, (float) $request->max_distance]);
        }

        // Exclude blocked users (both directions)
        $query->whereNotIn('users.id', function ($q) use ($user) {
            $q->select('blocked_user_id')->from('user_blocks')->where('user_id', $user->id);
        })->whereNotIn('users.id', function ($q) use ($user) {
            $q->select('user_id')->from('user_blocks')->where('blocked_user_id', $user->id);
        });

        // Respect privacy settings
        $query->whereHas('profile', function ($q) {
            $q->where('guest_mode', 0)
                ->where('no_one_at_all', 0);
        });

        // Filter by text search keyword (search bar on map overlay)
        $search = $request->input('search') ?? $request->input('keyword') ?? $request->input('query');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($pq) use ($search) {
                        $pq->where('display_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('about', 'like', "%{$search}%");
                    })
                    ->orWhereHas('hobbies', function ($hq) use ($search) {
                        $hq->where('hobby_items.name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by Audience
        $audience = $request->input('audience', 'all');
        if ($audience == 'connections') {
            $friendIds = $user->friends()->pluck('users.id')->toArray();
            $query->whereIn('users.id', $friendIds);
        } elseif ($audience == 'connections_of_friends') {
            $friendIds = $user->friends()->pluck('users.id')->toArray();
            $fofIds = DB::table('friend_requests')
                ->where('status', 'accepted')
                ->where(function ($q) use ($friendIds) {
                    $q->whereIn('user_id', $friendIds)->orWhereIn('friend_id', $friendIds);
                })
                ->get()
                ->flatMap(function ($item) use ($friendIds, $user) {
                    $ids = [$item->user_id, $item->friend_id];
                    return array_diff($ids, [$user->id]);
                })
                ->unique()
                ->toArray();
            $query->whereIn('users.id', $fofIds);
        }

        // Filter by Gender
        if ($request->has('gender') && !empty($request->input('gender'))) {
            $genders = is_array($request->input('gender')) ? $request->input('gender') : explode(',', $request->input('gender'));
            $genders = array_filter(array_map('trim', (array) $genders));
            $query->whereHas('profile', function ($q) use ($genders) {
                $q->whereIn('gender', $genders);
            });
        }

        // Filter by Hobbies
        if ($request->has('hobbies') && !empty($request->input('hobbies'))) {
            $hobbies = is_array($request->input('hobbies')) ? $request->input('hobbies') : explode(',', $request->input('hobbies'));
            $hobbies = array_filter(array_map('trim', (array) $hobbies));
            $query->whereHas('hobbies', function ($q) use ($hobbies) {
                $q->whereIn('hobby_items.id', $hobbies);
            });
        }

        // Filter by Age Range
        if ($request->has('age_min') || $request->has('age_max')) {
            $ageMin = (int) $request->input('age_min', 18);
            $ageMax = (int) $request->input('age_max', 100);
            $query->whereHas('profile', function ($q) use ($ageMin, $ageMax) {
                $q->whereBetween('age', [$ageMin, $ageMax]);
            });
        }

        // Filter Verified Profiles
        if ($request->boolean('verified_only') || $request->input('preset') == 'verified_profiles') {
            $query->where(function ($q) {
                $q->whereHas('profile', function ($p) {
                    $p->where('verified_profiles', 1);
                })->orWhereHas('kycVerification', function ($k) {
                    $k->where('status', 'approved');
                });
            });
        }

        // Filter by Presets
        $preset = $request->input('preset');
        if ($preset === 'popular_nearby') {
            // Nearest and more popular: handled in order by popularity_score and distance
        } elseif ($preset === 'new_members') {
            // New in system (last 30 days) and active/online
            $query->where('users.created_at', '>=', now()->subDays(30))
                ->where('user_locations.last_pinged_at', '>=', now()->subDays(7));
        } elseif ($preset === 'now_online') {
            // Currently online (last pinged in past 15 minutes)
            $query->where('user_locations.last_pinged_at', '>=', now()->subMinutes(15));
        } elseif ($preset === 'recently_online') {
            // Recently online (last 24 hours)
            $query->where('user_locations.last_pinged_at', '>=', now()->subHours(24));
        } elseif ($preset === 'updated_recently') {
            // User or profile updated in past 7 days
            $query->where(function ($q) {
                $q->where('users.updated_at', '>=', now()->subDays(7))
                    ->orWhereHas('profile', function ($p) {
                        $p->where('updated_at', '>=', now()->subDays(7));
                    });
            });
        } elseif ($preset === 'connections') {
            // Friends + Matches
            $friendIds = $user->friends()->pluck('users.id')->toArray();
            $matchedUserIds = DB::table('user_swipes')
                ->where('user_id', $user->id)
                ->where('is_match', true)
                ->pluck('target_user_id')
                ->toArray();
            $connectionIds = array_unique(array_merge($friendIds, $matchedUserIds));
            $query->whereIn('users.id', $connectionIds);
        } elseif ($preset === 'favorites') {
            // User's favorited profiles
            $favouriteIds = DB::table('user_favourite_profiles')
                ->where('user_id', $user->id)
                ->pluck('favourite_user_id')
                ->toArray();
            $query->whereIn('users.id', $favouriteIds);
        }

        $limit = 30;
        if ($request->filled('limit')) {
            $limit = min(max((int) $request->input('limit'), 1), 30);
        } elseif ($request->filled('per_page')) {
            $limit = min(max((int) $request->input('per_page'), 1), 30);
        }

        // Apply ordering based on preset
        if ($preset === 'popular_nearby') {
            $query->orderByDesc('popularity_score')
                ->orderBy('distance', 'asc');
        } elseif ($preset === 'new_members') {
            $query->orderBy('distance', 'asc')
                ->orderByDesc('users.created_at');
        } elseif ($preset === 'now_online' || $preset === 'recently_online') {
            $query->orderBy('distance', 'asc')
                ->orderByDesc('user_locations.last_pinged_at');
        } elseif ($preset === 'updated_recently') {
            $query->orderBy('distance', 'asc')
                ->orderByDesc('users.updated_at');
        } else {
            $query->orderBy('distance', 'asc');
        }

        $users = $query->with(['profile', 'userLocation'])
            ->take($limit)
            ->get();

        return $this->responseJson(true, 200, 'Users on map retrieved successfully', UserResource::collection($users));
    }

    /**
     * @OA\Get(
     *     path="/api/map/events",
     *     summary="Get nearby active and upcoming events on the map based on proximity",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="latitude", in="query", required=false, @OA\Schema(type="number", format="float"), description="Latitude of center point"),
     *     @OA\Parameter(name="longitude", in="query", required=false, @OA\Schema(type="number", format="float"), description="Longitude of center point"),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string"), description="Search events by title, description, location, host name, or tags"),
     *     @OA\Parameter(name="max_distance", in="query", required=false, @OA\Schema(type="integer"), description="Maximum distance in miles (optional)"),
     *     @OA\Parameter(name="audience", in="query", required=false, @OA\Schema(type="string"), description="Filter events by audience type (e.g. Friends, All, Community)"),
     *     @OA\Parameter(name="tag", in="query", required=false, @OA\Schema(type="string"), description="Filter events by tag (e.g. Party, Nightlife)"),
     *     @OA\Parameter(name="is_interested", in="query", required=false, @OA\Schema(type="boolean"), description="Filter events by user interest status"),
     *     @OA\Parameter(name="is_joined", in="query", required=false, @OA\Schema(type="boolean"), description="Filter events by user joined status"),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=30, maximum=30), description="Maximum number of events to retrieve (at most 30)"),
     *     @OA\Response(
     *         response=200,
     *         description="Events retrieved successfully"
     *     )
     * )
     */
    public function getEventsOnMap(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $lat = $request->latitude;
        $lng = $request->longitude;

        // Fallback to current user location if lat/lng not provided
        if (empty($lat) || empty($lng)) {
            $userLocation = UserLocation::query()->where('user_id', $user->id)->first();
            if ($userLocation) {
                $lat = $userLocation->lat;
                $lng = $userLocation->lng;
            } else {
                return $this->responseJson(false, 422, 'Latitude and longitude are required or you must update your location first.');
            }
        }

        // Haversine formula in miles for events table
        $haversine = "(3959 * acos(least(1, greatest(-1, cos(radians(?)) * cos(radians(CAST(events.lat AS DECIMAL(10, 6)))) * cos(radians(CAST(events.lng AS DECIMAL(10, 6))) - radians(?)) + sin(radians(?)) * sin(radians(CAST(events.lat AS DECIMAL(10, 6))))))))";

        $today = now()->toDateString();
        $currentTime = now()->format('H:i');

        $query = Event::query()
            ->selectRaw("events.*, {$haversine} as distance", [$lat, $lng, $lat])
            ->whereNotNull('events.lat')
            ->whereNotNull('events.lng')
            ->where('events.lat', '!=', '')
            ->where('events.lng', '!=', '')
            ->where('events.is_active', 1)
            // Active and not ended: event_date is in the future, or today and not past end_time
            ->where(function ($q) use ($today, $currentTime) {
                $q->where('events.event_date', '>', $today)
                    ->orWhere(function ($sub) use ($today, $currentTime) {
                        $sub->where('events.event_date', '=', $today)
                            ->where(function ($inner) use ($currentTime) {
                                $inner->whereNull('events.end_time')
                                    ->orWhere('events.end_time', '')
                                    ->orWhere('events.is_all_day', 1)
                                    ->orWhere('events.end_time', '>=', $currentTime);
                            });
                    });
            });

        // Filter by max_distance if provided
        if ($request->filled('max_distance') && is_numeric($request->max_distance)) {
            $query->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, (float) $request->max_distance]);
        }

        // Filter by search keyword
        $search = $request->input('search') ?? $request->input('keyword') ?? $request->input('query');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('events.title', 'like', "%{$search}%")
                    ->orWhere('events.description', 'like', "%{$search}%")
                    ->orWhere('events.about', 'like', "%{$search}%")
                    ->orWhere('events.location', 'like', "%{$search}%")
                    ->orWhere('events.location_string', 'like', "%{$search}%")
                    ->orWhere('events.host_name', 'like', "%{$search}%")
                    ->orWhere('events.tags', 'like', "%{$search}%");
            });
        }

        // Filter by audience
        if ($request->filled('audience')) {
            $query->where('events.audience', 'like', "%{$request->audience}%");
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->where('events.tags', 'like', "%{$request->tag}%");
        }

        // Filter by user interested status
        if ($request->has('is_interested') && $request->is_interested !== null && $request->is_interested !== '') {
            $isInterested = filter_var($request->is_interested, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isInterested !== null) {
                if ($isInterested) {
                    $query->whereHas('participants', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('status', 'interested');
                    });
                } else {
                    $query->whereDoesntHave('participants', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('status', 'interested');
                    });
                }
            }
        }

        // Filter by user joined status
        if ($request->has('is_joined') && $request->is_joined !== null && $request->is_joined !== '') {
            $isJoined = filter_var($request->is_joined, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isJoined !== null) {
                if ($isJoined) {
                    $query->whereHas('participants', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('status', 'joined');
                    });
                } else {
                    $query->whereDoesntHave('participants', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('status', 'joined');
                    });
                }
            }
        }

        $limit = 30;
        if ($request->filled('limit')) {
            $limit = min(max((int) $request->input('limit'), 1), 30);
        } elseif ($request->filled('per_page')) {
            $limit = min(max((int) $request->input('per_page'), 1), 30);
        }

        $events = $query->withCount([
            'joinedUsers as joined_count',
            'interestedUsers as interested_count'
        ])
            ->orderBy('distance', 'asc')
            ->take($limit)
            ->get();

        $events->transform(function ($event) use ($user) {
            $dist = isset($event->distance) ? (float) $event->distance : null;
            $event->distance = $dist !== null ? round($dist, 2) : null;
            $event->distance_in_km = $dist !== null ? round($dist * 1.60934, 2) : null;

            $event->user_joined = $event->joinedUsers()->where('user_id', $user->id)->exists();
            $event->user_interested = $event->interestedUsers()->where('user_id', $user->id)->exists();
            return $event;
        });

        return $this->responseJson(true, 200, 'Events on map retrieved successfully', $events);
    }

    /**
     * @OA\Get(
     *     path="/api/map/suggested-searches",
     *     summary="Get list of suggested search preset cards",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string"), description="Optional keyword to filter suggested search options"),
     *     @OA\Response(
     *         response=200,
     *         description="Suggested searches retrieved successfully"
     *     )
     * )
     */
    public function getSuggestedSearches(Request $request)
    {
        $suggested = [
            [
                "key" => "popular_nearby",
                "title" => "Popular nearby",
                "icon" => "🔥",
                "badge" => null,
                "filter_params" => ["preset" => "popular_nearby"]
            ],
            [
                "key" => "new_members",
                "title" => "New members",
                "icon" => "✨",
                "badge" => null,
                "filter_params" => ["preset" => "new_members"]
            ],
            [
                "key" => "verified_profiles",
                "title" => "Verified profiles",
                "icon" => "✅",
                "badge" => null,
                "filter_params" => ["preset" => "verified_profiles", "verified_only" => true]
            ],
            [
                "key" => "connections",
                "title" => "Connections",
                "icon" => "🤝",
                "badge" => "Friends + Matches",
                "filter_params" => ["preset" => "connections", "audience" => "connections"]
            ],
            [
                "key" => "favorites",
                "title" => "Favorites",
                "icon" => "⭐",
                "badge" => null,
                "filter_params" => ["preset" => "favorites"]
            ],
            [
                "key" => "now_online",
                "title" => "Now Online",
                "icon" => "🟢",
                "badge" => null,
                "filter_params" => ["preset" => "now_online"]
            ],
            [
                "key" => "recently_online",
                "title" => "Recently Online",
                "icon" => "🟡",
                "badge" => null,
                "filter_params" => ["preset" => "recently_online"]
            ],
            [
                "key" => "updated_recently",
                "title" => "Updated Recently",
                "icon" => "👋",
                "badge" => "Profile updated",
                "filter_params" => ["preset" => "updated_recently"]
            ]
        ];

        $search = $request->input('search') ?? $request->input('query');
        if (!empty($search)) {
            $suggested = array_values(array_filter($suggested, function ($item) use ($search) {
                return stripos($item['title'], $search) !== false || stripos($item['key'], $search) !== false;
            }));
        }

        return $this->responseJson(true, 200, "Suggested searches retrieved successfully", $suggested);
    }

    /**
     * @OA\Get(
     *     path="/api/map/recent",
     *     summary="Get list of recent search items for the authenticated user",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Recent searches retrieved successfully")
     * )
     */
    public function getRecentSearches()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $recents = UserRecentSearch::query()->where('user_id', $user->id)
            ->with(['targetUser.profile', 'targetEvent'])
            ->orderBy('updated_at', 'desc')
            ->take(15)
            ->get();

        return $this->responseJson(true, 200, 'Recent searches retrieved successfully', $recents);
    }

    /**
     * @OA\Post(
     *     path="/api/map/recent",
     *     summary="Add or update a recent search item",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"item_type"},
     *             @OA\Property(property="item_type", type="string", enum={"keyword", "user", "event", "place", "filter_preset"}),
     *             @OA\Property(property="query_text", type="string"),
     *             @OA\Property(property="target_id", type="integer"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Recent search added successfully")
     * )
     */
    public function addRecentSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_type' => 'required|string|in:keyword,user,event,place,filter_preset',
            'query_text' => 'nullable|string|max:255',
            'target_id' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 400, $validator->errors()->first());
        }

        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $attributes = [
            'user_id' => $user->id,
            'item_type' => $request->item_type,
        ];

        if ($request->filled('target_id')) {
            $attributes['target_id'] = $request->target_id;
        } else {
            $attributes['query_text'] = $request->query_text;
        }

        $recent = UserRecentSearch::updateOrCreate(
            $attributes,
            [
                'query_text' => $request->query_text,
                'target_id' => $request->target_id,
                'metadata' => $request->metadata,
                'updated_at' => now(),
            ]
        );

        return $this->responseJson(true, 200, 'Recent search saved successfully', $recent);
    }

    /**
     * @OA\Delete(
     *     path="/api/map/recent/clear",
     *     summary="Clear all recent search history",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Recent searches cleared successfully")
     * )
     */
    public function clearRecentSearches()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        UserRecentSearch::query()->where('user_id', $user->id)->delete();

        return $this->responseJson(true, 200, 'All recent searches cleared successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/map/recent/{id}",
     *     summary="Delete a single recent search item",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Recent search item deleted successfully")
     * )
     */
    public function deleteRecentSearch($id)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $item = UserRecentSearch::query()->where('user_id', $user->id)->where('id', $id)->first();
        if (!$item) {
            return $this->responseJson(false, 404, 'Recent search item not found');
        }

        $item->delete();

        return $this->responseJson(true, 200, 'Recent search item deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/map/favourite/{user_id}",
     *     summary="Toggle favourite status of a user",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user_id", in="path", required=true, description="ID of the user to toggle favourite status", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User added or removed from favourites"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Something went wrong")
     * )
     */
    public function toggleFavourite($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return $this->responseJson(false, 404, 'User not found');
            }
            $favouriteUser = UserFavouriteProfile::query()->where('user_id', auth()->user()->id)->where('favourite_user_id', $userId)->first();
            if ($favouriteUser) {
                $favouriteUser->delete();
                return $this->responseJson(true, 200, 'User removed from favourites');
            } else {
                UserFavouriteProfile::create([
                    'user_id' => auth()->user()->id,
                    'favourite_user_id' => $userId,
                ]);
                return $this->responseJson(true, 200, 'User added into favouritespapi');
            }
        } catch (\Exception $e) {
            logger($e->getMessage() . '-' . $e->getLine() . '-' . $e->getFile());
            return $this->responseJson(false, 500, 'Something went wrong');
        }
    }
}
