<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\User\UserResource;
use App\Models\AppSettingToggle;
use App\Models\User;
use App\Models\Event;
use App\Models\UserFavouriteProfile;
use App\Models\UserLocation;
use App\Models\UserRecentSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
     *             @OA\Property(property="longitude", type="number", format="float", example=-74.0060, description="Longitude coordinate"),
     *             @OA\Property(property="city", type="string", example="New York", description="Optional city name (auto-resolved from GPS if omitted)"),
     *             @OA\Property(property="ghost_mode", type="boolean", example=false, description="Optional ghost mode state")
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
            'city' => 'nullable|string',
            'ghost_mode' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first());
        }

        $user = auth()->user();

        if (!$user) {
            Log::warning('[MAP_LOCATION] Unauthorized attempt to update location');
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        Log::info('[MAP_LOCATION] Received location update request', [
            'user_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'input_city' => $request->input('city'),
            'ghost_mode' => $request->input('ghost_mode'),
        ]);

        // Prioritize explicit city if passed in request, otherwise resolve via reverse geocoding
        $city = $request->filled('city') ? trim($request->input('city')) : null;
        if (empty($city)) {
            $city = $this->getCity($request->latitude, $request->longitude);
        }

        Log::info('[MAP_LOCATION] City determined for coordinates', [
            'user_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'city' => $city,
        ]);

        $locationData = [
            'lat' => $request->latitude,
            'lng' => $request->longitude,
            'last_pinged_at' => now(),
        ];

        if (!empty($city)) {
            $locationData['city'] = $city;
        }

        if ($request->has('ghost_mode')) {
            $locationData['ghost_mode'] = $request->boolean('ghost_mode');
        } elseif ($request->has('is_ghost_mode')) {
            $locationData['ghost_mode'] = $request->boolean('is_ghost_mode');
        } else {
            $existingGhost = $user->userLocation?->ghost_mode ?? $user->appSettingToggle?->ghost_mode;
            if ($existingGhost !== null) {
                $locationData['ghost_mode'] = (bool) $existingGhost;
            }
        }

        Log::info('[MAP_LOCATION] Saving locationData to user_locations table', [
            'user_id' => $user->id,
            'locationData' => $locationData,
        ]);

        try {
            $location = UserLocation::updateOrCreate(
                ['user_id' => $user->id],
                $locationData
            );
            Log::info('[MAP_LOCATION] user_locations record saved successfully', [
                'user_id' => $user->id,
                'location_id' => $location->id,
                'saved_city' => $location->city ?? null,
                'saved_lat' => $location->lat,
                'saved_lng' => $location->lng,
                'saved_ghost_mode' => $location->ghost_mode ?? null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('[MAP_LOCATION] Database QueryException when saving user_locations: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error_code' => $e->getCode(),
                'sql' => $e->getSql() ?? null,
            ]);

            // Gracefully handle case where migration hasn't been run yet (missing city / ghost_mode column)
            if (str_contains($e->getMessage(), 'Unknown column') || $e->getCode() == '42S22') {
                Log::warning('[MAP_LOCATION] ATTENTION: Column missing in user_locations table on this database! Please run `php artisan migrate`. Retrying update without city/ghost_mode columns.');
                unset($locationData['city'], $locationData['ghost_mode']);
                $location = UserLocation::updateOrCreate(
                    ['user_id' => $user->id],
                    $locationData
                );
            } else {
                throw $e;
            }
        }

        // Always sync city to user profile living_in_city if available
        if (!empty($city)) {
            try {
                if ($user->profile) {
                    $user->profile->update(['living_in_city' => $city]);
                    Log::info('[MAP_LOCATION] User profile living_in_city updated', [
                        'user_id' => $user->id,
                        'living_in_city' => $city,
                    ]);
                } else {
                    $user->profile()->create(['living_in_city' => $city]);
                    Log::info('[MAP_LOCATION] User profile created with living_in_city', [
                        'user_id' => $user->id,
                        'living_in_city' => $city,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('[MAP_LOCATION] Error updating user profile living_in_city: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                ]);
            }
        }

        if ($request->has('ghost_mode')) {
            AppSettingToggle::updateOrCreate(
                ['user_id' => $user->id],
                ['ghost_mode' => $request->boolean('ghost_mode')]
            );
        }

        $isGhostMode = false;
        if (isset($location->ghost_mode)) {
            $isGhostMode = (bool) $location->ghost_mode;
        } else {
            $isGhostMode = (bool) ($user->appSettingToggle?->ghost_mode ?? false);
        }
        $location->city = $city;
        $location->ghost_mode = $isGhostMode;
        $location->map_dimmed = $isGhostMode;

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

            // Automatically record recent search internally
            try {
                UserRecentSearch::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'item_type' => 'keyword',
                        'query_text' => trim($search),
                    ],
                    [
                        'updated_at' => now(),
                    ]
                );
            } catch (\Throwable $e) {
                logger("Error saving recent keyword search: " . $e->getMessage());
            }
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

        $users = $query->with(['profile', 'userLocation', 'appSettingToggle'])
            ->take($limit)
            ->get();

        // Eagerly resolve is_favourite for each user relative to authenticated viewer
        if ($user && $users->isNotEmpty()) {
            $favouriteUserIds = UserFavouriteProfile::where('user_id', $user->id)
                ->whereIn('favourite_user_id', $users->pluck('id'))
                ->pluck('favourite_user_id')
                ->flip()
                ->toArray();

            foreach ($users as $u) {
                $u->is_favourite = isset($favouriteUserIds[$u->id]);
            }
        }

        $currentUserGhostMode = (bool) ($user->appSettingToggle?->ghost_mode ?? false);

        return response()->json([
            'status'        => true,
            'response_code' => 200,
            'message'       => 'Users on map retrieved successfully',
            'data'          => UserResource::collection($users),
        ], 200)
            ->header('X-Ghost-Mode', $currentUserGhostMode ? '1' : '0')
            ->header('X-Map-Dimmed', $currentUserGhostMode ? '1' : '0');
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
     * Internal helper to add or update a recent search item.
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
     *     @OA\Response(
     *         response=200,
     *         description="User favourite status toggled successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User added to favourites"),
     *             @OA\Property(property="is_favourite", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="is_favourite", type="boolean", example=true),
     *                 @OA\Property(property="user_id", type="integer", example=12)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Something went wrong")
     * )
     */
    public function toggleFavourite($userId)
    {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return $this->responseJson(false, 401, 'Unauthorized');
            }

            $user = User::find($userId);
            if (!$user) {
                return $this->responseJson(false, 404, 'User not found');
            }

            if ((int) $user->id === (int) $authUser->id) {
                return $this->responseJson(false, 422, 'You cannot favourite yourself');
            }

            $favouriteUser = UserFavouriteProfile::query()
                ->where('user_id', $authUser->id)
                ->where('favourite_user_id', $userId)
                ->first();

            if ($favouriteUser) {
                $favouriteUser->delete();
                Log::info('[FAVOURITE] User removed from favourites', [
                    'user_id' => $authUser->id,
                    'favourite_user_id' => $userId,
                ]);

                return response()->json([
                    'status'        => true,
                    'response_code' => 200,
                    'message'       => 'User removed from favourites',
                    'is_favourite'  => false,
                    'data'          => [
                        'is_favourite' => false,
                        'user_id'      => (int) $userId,
                    ],
                ], 200);
            } else {
                UserFavouriteProfile::create([
                    'user_id'           => $authUser->id,
                    'favourite_user_id' => $userId,
                ]);
                Log::info('[FAVOURITE] User added to favourites', [
                    'user_id' => $authUser->id,
                    'favourite_user_id' => $userId,
                ]);

                return response()->json([
                    'status'        => true,
                    'response_code' => 200,
                    'message'       => 'User added to favourites',
                    'is_favourite'  => true,
                    'data'          => [
                        'is_favourite' => true,
                        'user_id'      => (int) $userId,
                    ],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('[FAVOURITE] Error toggling favourite: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return $this->responseJson(false, 500, 'Something went wrong');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/map/ghost-mode",
     *     summary="Toggle Ghost Mode on the map",
     *     description="Toggles Ghost Mode on/off. If enabled, it disables it; if disabled, it enables it. When active, exact GPS location is hidden, only city and approximate 3-10km region are shown, and the map appears dimmed.",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="ghost_mode", type="boolean", example=true, description="Optional explicit boolean. If omitted, automatically toggles current state.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ghost mode toggled successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Ghost mode enabled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ghost_mode", type="boolean", example=true),
     *                 @OA\Property(property="map_dimmed", type="boolean", example=true),
     *                 @OA\Property(property="city", type="string", example="New York")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function toggleGhostMode(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseJson(false, 401, 'Unauthorized');
        }

        $userLocation = UserLocation::where('user_id', $user->id)->latest('id')->first();
        $toggle = AppSettingToggle::firstOrCreate(
            ['user_id' => $user->id],
            ['ghost_mode' => false]
        );

        // Determine current status directly from user_locations table
        $currentStatus = false;
        if ($userLocation && isset($userLocation->ghost_mode)) {
            $currentStatus = (bool) $userLocation->ghost_mode;
        } else {
            $currentStatus = (bool) $toggle->ghost_mode;
        }

        // Check user input: if explicitly provided use it; otherwise toggle (if enable then disable and vice versa)
        if ($request->has('ghost_mode')) {
            $newStatus = $request->boolean('ghost_mode');
        } elseif ($request->has('is_ghost_mode')) {
            $newStatus = $request->boolean('is_ghost_mode');
        } elseif ($request->has('isGhostMode')) {
            $newStatus = $request->boolean('isGhostMode');
        } elseif ($request->has('ghostMode')) {
            $newStatus = $request->boolean('ghostMode');
        } elseif ($request->has('status')) {
            $newStatus = $request->boolean('status');
        } elseif ($request->has('enable')) {
            $newStatus = $request->boolean('enable');
        } else {
            $newStatus = !$currentStatus;
        }

        Log::info('[GHOST_MODE] Toggle request processed', [
            'user_id' => $user->id,
            'current_status' => $currentStatus,
            'requested_ghost_mode' => $request->input('ghost_mode') ?? $request->input('is_ghost_mode'),
            'new_status' => $newStatus,
        ]);

        // Determine approximate_region_radius_km (default 10 km, between 3 and 10 km)
        $radius = 10;
        if ($request->filled('approximate_region_radius_km') && is_numeric($request->approximate_region_radius_km)) {
            $radius = min(max((int) $request->approximate_region_radius_km, 3), 10);
        } elseif ($userLocation && isset($userLocation->approximate_region_radius_km) && $userLocation->approximate_region_radius_km > 0) {
            $radius = min(max((int) $userLocation->approximate_region_radius_km, 3), 10);
        }

        // Update AppSettingToggle
        $toggle->ghost_mode = $newStatus;
        $toggle->save();

        // Resolve and cache city if location is available
        $city = $userLocation?->city ?? $user->profile?->living_in_city;
        if ($userLocation && $userLocation->lat && $userLocation->lng) {
            $resolvedCity = $this->getCity($userLocation->lat, $userLocation->lng);
            if ($resolvedCity) {
                $city = $resolvedCity;
            }
        }

        // Update UserLocation with city, ghost_mode, and approximate_region_radius_km
        $updateData = [
            'ghost_mode' => $newStatus,
            'approximate_region_radius_km' => $radius,
        ];
        if (!empty($city)) {
            $updateData['city'] = $city;
        }

        try {
            if ($userLocation) {
                // Update all user_locations records for this user so they are always in sync
                UserLocation::where('user_id', $user->id)->update($updateData);
                $userLocation->refresh();
                Log::info('[GHOST_MODE] user_locations record updated', [
                    'user_id' => $user->id,
                    'updateData' => $updateData,
                ]);
            } else {
                // Create user_locations record if none exists so user_locations table reflects ghost_mode
                $userLocation = UserLocation::create(array_merge([
                    'user_id' => $user->id,
                    'lat' => 0.0,
                    'lng' => 0.0,
                    'last_pinged_at' => now(),
                ], $updateData));
                Log::info('[GHOST_MODE] user_locations record created for ghost_mode', [
                    'user_id' => $user->id,
                    'location_id' => $userLocation->id,
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('[GHOST_MODE] QueryException when updating user_locations: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
            if (str_contains($e->getMessage(), 'Unknown column') || $e->getCode() == '42S22') {
                Log::warning('[GHOST_MODE] Column missing in user_locations on server DB! Run `php artisan migrate`.');
            } else {
                throw $e;
            }
        }

        if (!empty($city)) {
            try {
                if ($user->profile) {
                    $user->profile->update(['living_in_city' => $city]);
                } else {
                    $user->profile()->create(['living_in_city' => $city]);
                }
            } catch (\Throwable $e) {
                Log::error('[GHOST_MODE] Error updating user profile living_in_city: ' . $e->getMessage());
            }
        }

        $message = $newStatus ? 'Ghost mode enabled successfully' : 'Ghost mode disabled successfully';

        return $this->responseJson(true, 200, $message, [
            'ghost_mode' => $newStatus,
            'is_ghost_mode' => $newStatus,
            'map_dimmed' => $newStatus,
            'city' => $city,
        ]);
    }

    /**
     * Alias for toggleGhostMode to ensure backwards compatibility.
     */
    public function updateGhostMode(Request $request)
    {
        return $this->toggleGhostMode($request);
    }

    /**
     * Reverse geocode coordinates to find city name.
     * Uses API keys configured in .env (GEOAPIFY_API_KEY or GOOGLE_API_KEY).
     * Cached by rounded coordinates for high performance and reliability.
     */
    public function getCity($latitude, $longitude)
    {
        return self::resolveCity($latitude, $longitude);
    }

    /**
     * Static helper to resolve city name using GOOGLE_API_KEY from .env (with fallback).
     */
    public static function resolveCity($latitude, $longitude)
    {
        if (empty($latitude) || empty($longitude)) {
            Log::warning('[GEOCODING] Coordinates are empty', ['lat' => $latitude, 'lng' => $longitude]);
            return null;
        }

        $cacheKey = 'geo_city_' . round((float) $latitude, 2) . '_' . round((float) $longitude, 2);

        $cachedCity = Cache::get($cacheKey);
        if (!empty($cachedCity)) {
            Log::info("[GEOCODING] Cache hit for coordinates ($latitude, $longitude): $cachedCity");
            return $cachedCity;
        }

        $googleKey = env('GOOGLE_API_KEY')
            ?? config('constants.GEOLOCATION_API_KEY')
            ?? config('services.google.maps_api_key');

        $googleBackupKey = env('GOOGLE_API_KEY_BACKUP');

        $geoapifyKey = env('GEOAPIFY_API_KEY')
            ?? config('services.geoapify.api_key')
            ?? config('constants.GEOAPIFY_API_KEY');

        Log::info("[GEOCODING] Attempting to reverse geocode ($latitude, $longitude)", [
            'has_google_key' => !empty($googleKey),
            'google_key_prefix' => !empty($googleKey) ? substr($googleKey, 0, 8) . '...' : null,
            'has_google_backup_key' => !empty($googleBackupKey),
            'has_geoapify_key' => !empty($geoapifyKey),
            'geoapify_key_prefix' => !empty($geoapifyKey) ? substr($geoapifyKey, 0, 8) . '...' : null,
        ]);

        // 1. Primary: Use GOOGLE_API_KEY from .env with Google Geocoding API
        if (!empty($googleKey)) {
            $city = self::fetchCityFromGoogle($latitude, $longitude, $googleKey);
            if (!empty($city)) {
                Log::info("[GEOCODING] Resolved city via Google Maps API: $city");
                Cache::put($cacheKey, $city, now()->addDays(30));
                return $city;
            }

            // If GOOGLE_API_KEY holds a Geoapify / alternate key
            $cityGeo = self::fetchCityFromGeoapify($latitude, $longitude, $googleKey);
            if (!empty($cityGeo)) {
                Log::info("[GEOCODING] Resolved city via primary key on Geoapify: $cityGeo");
                Cache::put($cacheKey, $cityGeo, now()->addDays(30));
                return $cityGeo;
            }
        }

        // 2. Try Google Backup Key if present
        if (!empty($googleBackupKey)) {
            $city = self::fetchCityFromGoogle($latitude, $longitude, $googleBackupKey);
            if (!empty($city)) {
                Log::info("[GEOCODING] Resolved city via Google Backup Key: $city");
                Cache::put($cacheKey, $city, now()->addDays(30));
                return $city;
            }
        }

        // 3. Fallback: Try Geoapify if configured
        if (!empty($geoapifyKey)) {
            $city = self::fetchCityFromGeoapify($latitude, $longitude, $geoapifyKey);
            if (!empty($city)) {
                Log::info("[GEOCODING] Resolved city via Geoapify API: $city");
                Cache::put($cacheKey, $city, now()->addDays(30));
                return $city;
            }
        }

        Log::warning("[GEOCODING] Failed to resolve city for coordinates ($latitude, $longitude) from all configured providers");
        return null;
    }

    /**
     * Fetch city using Geoapify Reverse Geocoding API.
     */
    private static function fetchCityFromGeoapify($latitude, $longitude, $apiKey)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => sprintf('https://api.geoapify.com/v1/geocode/reverse?lat=%s&lon=%s&format=json&apiKey=%s', $latitude, $longitude, urlencode($apiKey)),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($curl);
        curl_close($curl);

        if ($httpCode !== 200 || empty($response)) {
            Log::warning('[GEOCODING][Geoapify] HTTP request failed', [
                'http_code' => $httpCode,
                'curl_error' => $curlErr,
                'response_snippet' => substr($response ?? '', 0, 200),
            ]);
            return null;
        }

        $data = json_decode($response, true);
        $result = $data['results'][0] ?? [];
        $city = $result['city']
            ?? $result['town']
            ?? $result['municipality']
            ?? $result['suburb']
            ?? $result['county']
            ?? $result['state']
            ?? null;

        Log::info('[GEOCODING][Geoapify] API response', [
            'http_code' => $httpCode,
            'resolved_city' => $city,
            'result_keys' => !empty($result) ? array_keys($result) : [],
        ]);

        return $city;
    }

    /**
     * Fetch city using Google Maps Geocoding API.
     */
    private static function fetchCityFromGoogle($latitude, $longitude, $apiKey)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => sprintf('https://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&key=%s', $latitude, $longitude, urlencode($apiKey)),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($curl);
        curl_close($curl);

        if ($httpCode !== 200 || empty($response)) {
            Log::warning('[GEOCODING][Google] HTTP request failed', [
                'http_code' => $httpCode,
                'curl_error' => $curlErr,
                'response_snippet' => substr($response ?? '', 0, 200),
            ]);
            return null;
        }

        $data = json_decode($response, true);
        $status = $data['status'] ?? 'UNKNOWN';

        if ($status !== 'OK') {
            Log::warning('[GEOCODING][Google] Google Geocoding returned non-OK status', [
                'status' => $status,
                'error_message' => $data['error_message'] ?? null,
            ]);
        }

        if (!empty($data['results'])) {
            foreach ($data['results'] as $result) {
                if (isset($result['address_components'])) {
                    foreach ($result['address_components'] as $component) {
                        if (in_array('locality', $component['types'])) {
                            return $component['long_name'];
                        }
                    }
                }
            }
            foreach ($data['results'] as $result) {
                if (isset($result['address_components'])) {
                    foreach ($result['address_components'] as $component) {
                        if (in_array('postal_town', $component['types'])) {
                            return $component['long_name'];
                        }
                    }
                }
            }
            foreach ($data['results'] as $result) {
                if (isset($result['address_components'])) {
                    foreach ($result['address_components'] as $component) {
                        if (in_array('administrative_area_level_2', $component['types'])) {
                            return $component['long_name'];
                        }
                    }
                }
            }
            foreach ($data['results'] as $result) {
                if (isset($result['address_components'])) {
                    foreach ($result['address_components'] as $component) {
                        if (in_array('administrative_area_level_1', $component['types'])) {
                            return $component['long_name'];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Compute approximate region coordinates within a 3-10 km radius for Ghost Mode.
     */
    public static function getApproximateCoordinates($latitude, $longitude, $userId = null, $radiusKm = 10.0)
    {
        $radius = min(max((float) $radiusKm, 3.0), 10.0);

        if (empty($latitude) || empty($longitude)) {
            return [
                'approximate_latitude' => null,
                'approximate_longitude' => null,
                'approximate_radius_km' => (int) $radius,
            ];
        }

        // Use deterministic seed based on user ID and day of year so the approximate region is stable for the day
        $seed = ($userId ?? 1) + (int) date('z');
        mt_srand($seed);

        // Distance between 50% and 90% of the selected radius (3-10 km)
        $distance = ($radius * 0.5) + (($radius * 0.4) * (mt_rand(0, 100) / 100));
        $angle = mt_rand(0, 360) * (M_PI / 180);

        mt_srand(); // Reset random seed

        $deltaLat = ($distance * cos($angle)) / 111.0;
        $deltaLng = ($distance * sin($angle)) / (111.0 * max(0.1, cos(deg2rad((float) $latitude))));

        $approxLat = round((float) $latitude + $deltaLat, 6);
        $approxLng = round((float) $longitude + $deltaLng, 6);

        return [
            'approximate_latitude' => $approxLat,
            'approximate_longitude' => $approxLng,
            'approximate_radius_km' => (int) $radius,
        ];
    }
}
