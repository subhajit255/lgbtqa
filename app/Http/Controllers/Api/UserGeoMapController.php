<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserRecentSearch;
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
     *             @OA\Property(property="longitude", type="number", format="float", example=-74.0060, description="Longitude coordinate"),
     *             @OA\Property(property="lat", type="number", format="float", example=40.7128, description="Alias for latitude"),
     *             @OA\Property(property="lng", type="number", format="float", example=-74.0060, description="Alias for longitude")
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
        $lat = $request->input('latitude', $request->input('lat'));
        $lng = $request->input('longitude', $request->input('lng'));

        $request->merge([
            'lat' => $lat,
            'lng' => $lng,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 400, $validator->errors()->first());
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
     *     @OA\Parameter(name="max_distance", in="query", required=false, @OA\Schema(type="integer", default=50), description="Maximum distance in miles"),
     *     @OA\Parameter(name="audience", in="query", required=false, @OA\Schema(type="string", enum={"all", "connections", "connections_of_friends"}, default="all"), description="Filter by audience connection type"),
     *     @OA\Parameter(name="gender", in="query", required=false, @OA\Schema(type="array", @OA\Items(type="integer")), description="Filter by gender identities"),
     *     @OA\Parameter(name="hobbies", in="query", required=false, @OA\Schema(type="array", @OA\Items(type="integer")), description="Filter by hobby item IDs"),
     *     @OA\Parameter(name="age_min", in="query", required=false, @OA\Schema(type="integer", default=18), description="Minimum age filter"),
     *     @OA\Parameter(name="age_max", in="query", required=false, @OA\Schema(type="integer", default=100), description="Maximum age filter"),
     *     @OA\Parameter(name="verified_only", in="query", required=false, @OA\Schema(type="boolean"), description="Filter only verified profiles"),
     *     @OA\Parameter(name="preset", in="query", required=false, @OA\Schema(type="string", enum={"new_members", "now_online", "recently_online", "updated_recently", "verified_profiles", "popular_nearby", "connections"}), description="Suggested search preset filter"),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=20), description="Items per page"),
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

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        // Fallback to current user location if lat/lng not provided
        if (is_null($lat) || is_null($lng)) {
            $userLoc = UserLocation::query()->where('user_id', $user->id)->first();
            if ($userLoc) {
                $lat = $userLoc->lat;
                $lng = $userLoc->lng;
            } else {
                return $this->responseJson(false, 400, 'Latitude and longitude are required or you must update your location first.');
            }
        }

        $maxDistance = (int) $request->input('max_distance', 50);

        // Haversine formula in miles
        $haversine = "(3959 * acos(cos(radians(?)) * cos(radians(user_locations.lat)) * cos(radians(user_locations.lng) - radians(?)) + sin(radians(?)) * sin(radians(user_locations.lat))))";

        $query = User::query()
            ->selectRaw("users.*, {$haversine} as distance", [$lat, $lng, $lat])
            ->join('user_locations', 'users.id', '=', 'user_locations.user_id')
            ->where('users.id', '!=', $user->id)
            ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $maxDistance]);

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

        // Filter by Audience
        $audience = $request->input('audience', 'all');
        if ($audience === 'connections') {
            $friendIds = $user->friends()->pluck('users.id')->toArray();
            $query->whereIn('users.id', $friendIds);
        } elseif ($audience === 'connections_of_friends') {
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
            $query->whereHas('profile', function ($q) use ($genders) {
                $q->whereIn('gender', $genders);
            });
        }

        // Filter by Hobbies
        if ($request->has('hobbies') && !empty($request->input('hobbies'))) {
            $hobbies = is_array($request->input('hobbies')) ? $request->input('hobbies') : explode(',', $request->input('hobbies'));
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
        if ($request->boolean('verified_only') || $request->input('preset') === 'verified_profiles') {
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
        if ($preset === 'new_members') {
            $query->where('users.created_at', '>=', now()->subDays(14));
        } elseif ($preset === 'now_online') {
            $query->where('user_locations.last_pinged_at', '>=', now()->subMinutes(15));
        } elseif ($preset === 'recently_online') {
            $query->where('user_locations.last_pinged_at', '>=', now()->subHours(24));
        } elseif ($preset === 'updated_recently') {
            $query->where(function ($q) {
                $q->where('users.updated_at', '>=', now()->subDays(7))
                    ->orWhereHas('profile', function ($p) {
                        $p->where('updated_at', '>=', now()->subDays(7));
                    });
            });
        } elseif ($preset === 'connections') {
            $friendIds = $user->friends()->pluck('users.id')->toArray();
            $query->whereIn('users.id', $friendIds);
        }

        $perPage = (int) $request->input('per_page', 20);
        $users = $query->with(['profile', 'userLocation'])
            ->orderBy('distance', 'asc')
            ->paginate($perPage);

        return $this->responseJsonPaginated(true, 200, 'Users on map retrieved successfully', $users);
    }

    /**
     * @OA\Get(
     *     path="/api/map/suggested-searches",
     *     summary="Get list of suggested search preset cards",
     *     tags={"Map"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Suggested searches retrieved successfully"
     *     )
     * )
     */
    public function getSuggestedSearches()
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

        $recents = UserRecentSearch::where('user_id', $user->id)
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

        UserRecentSearch::where('user_id', $user->id)->delete();

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

        $item = UserRecentSearch::where('user_id', $user->id)->where('id', $id)->first();
        if (!$item) {
            return $this->responseJson(false, 404, 'Recent search item not found');
        }

        $item->delete();

        return $this->responseJson(true, 200, 'Recent search item deleted successfully');
    }
}
