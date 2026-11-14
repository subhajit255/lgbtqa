<?php

namespace App\Http\Resources\Api\User;

use App\Http\Controllers\Api\UserGeoMapController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = auth()->user();
        $isSelf = $viewer && $viewer->id === $this->id;

        // Check if ghost mode is enabled for this user (checks user_locations and app_setting_toggles)
        $isGhostMode = false;
        if ($this->userLocation && isset($this->userLocation->ghost_mode)) {
            $isGhostMode = (bool) $this->userLocation->ghost_mode;
        } elseif ($this->relationLoaded('appSettingToggle')) {
            $isGhostMode = (bool) ($this->appSettingToggle?->ghost_mode ?? false);
        } else {
            $isGhostMode = (bool) ($this->appSettingToggle?->ghost_mode ?? false);
        }

        $rawLat = $this->userLocation?->lat;
        $rawLng = $this->userLocation?->lng;

        // Get city name (stored or resolved via Geoapify reverse geocoding)
        $city = $this->userLocation?->city ?? $this->profile?->living_in_city;
        if (!$city && $rawLat && $rawLng) {
            $city = UserGeoMapController::resolveCity($rawLat, $rawLng);
        }

        // When Ghost Mode is enabled:
        // - Exact location is hidden from all users (latitude & longitude = null)
        // - Only city is shown (no GPS coordinates)
        // - Optional approximate region (3-10 km radius)
        if ($isGhostMode && !$isSelf) {
            $latitude = null;
            $longitude = null;
            $radius = (int) ($this->userLocation?->approximate_region_radius_km ?? 5);
            $radius = min(max($radius, 3), 10);

            $approx = UserGeoMapController::getApproximateCoordinates($rawLat, $rawLng, $this->id, $radius);
            $approxLat = $approx['approximate_latitude'];
            $approxLng = $approx['approximate_longitude'];
            $approxRadius = $approx['approximate_radius_km'];

            $distance = $this->when(isset($this->distance), function () {
                return round(ceil((float) $this->distance / 3) * 3, 0);
            }) ?? 0;

            $distanceInKm = $this->when(isset($this->distance), function () {
                return round(ceil(((float) $this->distance * 1.60934) / 5) * 5, 0);
            }) ?? 0;
        } else {
            $latitude = $rawLat;
            $longitude = $rawLng;
            $approxLat = null;
            $approxLng = null;
            $approxRadius = null;

            $distance = $this->when(isset($this->distance), function () {
                return round((float) $this->distance, 2);
            }) ?? 0;

            $distanceInKm = $this->when(isset($this->distance), function () {
                return round((float) $this->distance * 1.60934, 2);
            }) ?? 0;
        }

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'unique_code' => $this->unique_code,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'phone_code' => $this->phone_code,
            'profile_image' => $this->image_path,
            'bio' => $this->bio,
            'dob' => $this->profile?->dob ? \Carbon\Carbon::parse($this->profile->dob)->format('Y-m-d') : null,
            'age' => $this->profile?->age ?? null,
            'gender' => $this->profile?->gender ?? null,
            'is_active' => $this->is_active,
            'is_approve' => $this->is_approve,
            'is_blocked' => $this->is_blocked,
            'is_delete' => $this->is_delete,
            'distance' => $distance,
            'distance_in_km' => $distanceInKm,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'city' => $city,
            'is_ghost_mode' => $isGhostMode,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'approximate_latitude' => $approxLat,
            'approximate_longitude' => $approxLng,
            'approximate_radius_km' => $approxRadius,
        ];
    }
}
