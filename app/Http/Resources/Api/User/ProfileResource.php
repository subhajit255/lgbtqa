<?php

namespace App\Http\Resources\Api\User;

use App\Traits\CommonFunction;
use App\Traits\NotificationTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\HobbyItemResource;

class ProfileResource extends JsonResource
{
    use CommonFunction;
    use NotificationTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request): array
    {
        $profile = $this->profile;

        $uniqueCode = $this->unique_code;
        $shareLink = null;
        $qrCodeUrl = null;

        if (!empty($uniqueCode)) {
            $shareLink = 'https://dummy-link.com/user/' . $uniqueCode;
            $qrDir = storage_path('app/public/qrcodes');
            if (!file_exists($qrDir)) {
                mkdir($qrDir, 0755, true);
            }
            $qrPath = $qrDir . '/' . $uniqueCode . '.png';
            if (!file_exists($qrPath)) {
                try {
                    $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($shareLink);
                    $imageContent = @file_get_contents($qrApiUrl);
                    if ($imageContent) {
                        file_put_contents($qrPath, $imageContent);
                    }
                } catch (\Exception $e) {
                    // Log or ignore
                }
            }
            if (file_exists($qrPath)) {
                $qrCodeUrl = asset('storage/qrcodes/' . $uniqueCode . '.png');
            }
        }

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'unique_code' => $uniqueCode,
            'share_link' => $shareLink,
            'qr_code' => $qrCodeUrl,
            'name' => $this->name,
            'email' => $this->email,
            'trusted_email' => $this->trusted_email,
            'is_trusted_email_verified' => (bool)$this->is_trusted_email_verified,
            'mobile_number' => $this->mobile_number,
            'user_type' => $this->user_type,
            'profile_image' => $this->image_path,
            'is_online' => (bool)$this->is_online,
            'last_seen_at' => $this->last_seen_at,
            'pause_notifications' => (bool)$this->pause_notifications,
            'created_at' => $this->created_at,

            // Profile Details
            'first_name' => $this->profile?->first_name ?? null,
            'last_name' => $this->profile?->last_name ?? null,
            'display_name' => $this->profile?->display_name ?? null,
            'about' => $this->profile?->about ?? null,
            'dob' => $this->profile?->dob ? \Carbon\Carbon::parse($this->profile->dob)->format('Y-m-d') : null,
            'age' => $this->profile?->age ?? null,
            'gender' => $this->profile?->gender ?? null,
            'orientation' => $this->profile?->orientation ?? null,
            'dating_preferences' => is_string($this->profile?->dating_preferences) ? (json_decode($this->profile->dating_preferences, true) ?: array_filter(explode(',', $this->profile->dating_preferences))) : null,
            'dating_preferances' => is_string($this->profile?->dating_preferences) ? (json_decode($this->profile->dating_preferences, true) ?: array_filter(explode(',', $this->profile->dating_preferences))) : null,
            'relationship_status' => $this->profile?->relationship_status ?? null,
            'relationshipStatus' => $this->profile?->relationship_status ?? null,
            'what_i_am_looking_for' => $this->profile?->what_i_am_looking_for ?? null,
            'looking_for' => $this->profile?->what_i_am_looking_for ?? null,
            'lookingFor' => $this->profile?->what_i_am_looking_for ?? null,
            'living_in_country' => $this->profile?->living_in_country ?? null,
            'living_in_city' => $this->profile?->living_in_city ?? null,
            'show_location_on_profile' => (bool)($this->profile?->show_location_on_profile ?? true),
            'currently_traveling' => (bool)($this->profile?->currently_traveling ?? false),
            'languages_spoken' => is_string($this->profile?->languages_spoken) ? (json_decode($this->profile->languages_spoken, true) ?: array_filter(explode(',', $this->profile->languages_spoken))) : null,
            'languagesSpoken' => is_string($this->profile?->languages_spoken) ? (json_decode($this->profile->languages_spoken, true) ?: array_filter(explode(',', $this->profile->languages_spoken))) : null,
            'languages' => is_string($this->profile?->languages_spoken) ? (json_decode($this->profile->languages_spoken, true) ?: array_filter(explode(',', $this->profile->languages_spoken))) : null,
            'language' => is_string($this->profile?->languages_spoken) ? (json_decode($this->profile->languages_spoken, true) ?: array_filter(explode(',', $this->profile->languages_spoken))) : null,
            'languages_learning' => is_string($this->profile?->languages_learning) ? (json_decode($this->profile->languages_learning, true) ?: array_filter(explode(',', $this->profile->languages_learning))) : null,
            'languagesLearning' => is_string($this->profile?->languages_learning) ? (json_decode($this->profile->languages_learning, true) ?: array_filter(explode(',', $this->profile->languages_learning))) : null,
            'languages_written' => is_string($this->profile?->languages_written) ? json_decode($this->profile->languages_written, true) : null,
            'languagesWritten' => is_string($this->profile?->languages_written) ? json_decode($this->profile->languages_written, true) : null,
            'username' => $this->username,
            'occupation' => $this->profile?->occupation ?? null,
            'weight' => $this->profile?->weight ?? null,
            'zodiac' => $this->profile?->zodiac ?? null,
            'location' => $this->profile?->location ?? null,
            'drinking' => $this->profile?->alcohol ?? null,
            'nationality' => is_string($this->profile?->nationality) ? (json_decode($this->profile->nationality, true) ?: $this->profile->nationality) : ($this->profile?->nationality ?? null),
            'coming_out_status' => $this->profile?->coming_out_status ?? null,
            'comingOutStatus' => $this->profile?->coming_out_status ?? null,
            'show_coming_out_status' => (bool)($this->profile?->show_coming_out_status ?? true),
            'showComingOutStatus' => (bool)($this->profile?->show_coming_out_status ?? true),
            'religion' => is_string($this->profile?->religion) ? (json_decode($this->profile->religion, true) ?: array_filter(explode(',', $this->profile->religion))) : null,
            'show_religion' => (bool)($this->profile?->show_religion ?? true),
            'showReligion' => (bool)($this->profile?->show_religion ?? true),
            'political_views' => is_string($this->profile?->political_views) ? (json_decode($this->profile->political_views, true) ?: array_filter(explode(',', $this->profile->political_views))) : null,
            'politicalViews' => is_string($this->profile?->political_views) ? (json_decode($this->profile->political_views, true) ?: array_filter(explode(',', $this->profile->political_views))) : null,
            'show_political_views' => (bool)($this->profile?->show_political_views ?? true),
            'showPoliticalViews' => (bool)($this->profile?->show_political_views ?? true),
            'music_tests' => is_string($this->profile?->music_tests) ? (json_decode($this->profile->music_tests, true) ?: array_filter(explode(',', $this->profile->music_tests))) : null,
            'Music_taste' => is_string($this->profile?->music_tests) ? (json_decode($this->profile->music_tests, true) ?: array_filter(explode(',', $this->profile->music_tests))) : null,
            'music_taste' => is_string($this->profile?->music_tests) ? (json_decode($this->profile->music_tests, true) ?: array_filter(explode(',', $this->profile->music_tests))) : null,
            'musicTests' => is_string($this->profile?->music_tests) ? (json_decode($this->profile->music_tests, true) ?: array_filter(explode(',', $this->profile->music_tests))) : null,
            'musicTaste' => is_string($this->profile?->music_tests) ? (json_decode($this->profile->music_tests, true) ?: array_filter(explode(',', $this->profile->music_tests))) : null,
            'height' => $this->profile?->height ?? null,
            'body_type' => $this->profile?->body_type ?? null,
            'bodyType' => $this->profile?->body_type ?? null,
            'eye_color' => $this->profile?->eye_color ?? null,
            'eyeColor' => $this->profile?->eye_color ?? null,
            'eye' => $this->profile?->eye_color ?? null,
            'hair_color' => $this->profile?->hair_color ?? null,
            'hairColor' => $this->profile?->hair_color ?? null,
            'hair_length' => $this->profile?->hair_length ?? null,
            'tattoos' => $this->profile?->tattoos ?? null,
            'confirm_18_plus' => $this->profile?->confirm_18_plus ?? false,
            'sex_importance' => $this->profile?->sex_importance ?? null,
            'importance' => $this->profile?->sex_importance ?? null,
            'role_position' => $this->profile?->role_position ?? null,
            'role' => $this->profile?->role_position ?? null,
            'dating_pace' => $this->profile?->dating_pace ?? null,
            'datingPace' => $this->profile?->dating_pace ?? null,
            'presentation_preference' => $this->profile?->presentation_preference ?? null,
            'presentation' => $this->profile?->presentation_preference ?? null,
            'all_show_on_profile' => (bool)($this->profile?->all_show_on_profile ?? true),
            'AllshowOnProfile' => (bool)($this->profile?->all_show_on_profile ?? true),
            'allShowOnProfile' => (bool)($this->profile?->all_show_on_profile ?? true),
            'private_album' => $this->profile?->private_album ?? false,
            'agreed_to_terms' => $this->profile?->agreed_to_terms ?? false,
            'alcohol' => $this->profile?->alcohol ?? null,
            'smoking' => $this->profile?->smoking ?? null,
            'exercise' => $this->profile?->exercise ?? null,
            'diet' => $this->profile?->diet ?? null,
            'sleep_rhythm' => $this->profile?->sleep_rhythm ?? null,
            'sleepRhythm' => $this->profile?->sleep_rhythm ?? null,
            'kids_have' => $this->profile?->kids_have ?? null,
            'kidsHave' => $this->profile?->kids_have ?? null,
            'kids_future' => $this->profile?->kids_future ?? null,
            'kidsFuture' => $this->profile?->kids_future ?? null,
            'pets_current' => is_string($this->profile?->pets_current) ? (json_decode($this->profile->pets_current, true) ?: array_filter(explode(',', $this->profile->pets_current))) : null,
            'petsCurrent' => is_string($this->profile?->pets_current) ? (json_decode($this->profile->pets_current, true) ?: array_filter(explode(',', $this->profile->pets_current))) : null,
            'pets_future' => $this->profile?->pets_future ?? null,
            'petsFuture' => $this->profile?->pets_future ?? null,
            'living_preference' => $this->profile?->living_preference ?? null,
            'livingPreference' => $this->profile?->living_preference ?? null,
            'travel_importance' => $this->profile?->travel_importance ?? null,
            'travelImportance' => $this->profile?->travel_importance ?? null,
            'preferred_communication' => is_string($this->profile?->preferred_communication) ? (json_decode($this->profile->preferred_communication, true) ?: array_filter(explode(',', $this->profile->preferred_communication))) : null,
            'preferredCommunication' => is_string($this->profile?->preferred_communication) ? (json_decode($this->profile->preferred_communication, true) ?: array_filter(explode(',', $this->profile->preferred_communication))) : null,
            'love_language' => is_string($this->profile?->love_language) ? (json_decode($this->profile->love_language, true) ?: array_filter(explode(',', $this->profile->love_language))) : null,
            'loveLanguage' => is_string($this->profile?->love_language) ? (json_decode($this->profile->love_language, true) ?: array_filter(explode(',', $this->profile->love_language))) : null,
            'social_energy' => $this->profile?->social_energy ?? null,
            'socialEnergy' => $this->profile?->social_energy ?? null,
            'personality_type' => $this->profile?->personality_type ?? null,
            'personalityType' => $this->profile?->personality_type ?? null,
            'education' => $this->profile?->education ?? null,
            'age_range' => $this->profile?->age_range ?? 1,
            'age_range_min' => $this->profile?->age_range_min ?? null,
            'age_range_max' => $this->profile?->age_range_max ?? null,
            'distance_range' => $this->profile?->distance_range ?? 1,
            'friends' => $this->profile?->friends ?? true,
            'dates' => $this->profile?->dates ?? true,
            'events_and_communities' => $this->profile?->events_and_communities ?? true,
            'hookups' => $this->profile?->hookups ?? true,
            'guest_mode' => $this->profile?->guest_mode ?? false,
            'verified_profiles' => $this->profile?->verified_profiles ?? false,
            'invite_only_access' => $this->profile?->invite_only_access ?? false,
            'no_tracking' => $this->profile?->no_tracking ?? false,
            'everyone' => $this->profile?->everyone ?? true,
            'selected_groups' => $this->profile?->selected_groups ?? false,
            'no_one_at_all' => $this->profile?->no_one_at_all ?? false,

            // Hobbies, Interests, and Values
            'hobbies' => $this->relationLoaded('hobbies')
                ? $this->hobbies->filter(fn($item) => $item->hobby && ($item->hobby->type ?? 1) == 1)
                ->groupBy('hobby_id')
                ->map(function ($items) {
                    $hobby = $items->first()->hobby;
                    return [
                        'id' => $hobby->id,
                        'uuid' => $hobby->uuid,
                        'title' => $hobby->title,
                        'items' => HobbyItemResource::collection($items),
                    ];
                })->values()->all()
                : [],
            'interests' => $this->relationLoaded('hobbies')
                ? HobbyItemResource::collection($this->hobbies->filter(fn($item) => ($item->hobby?->type ?? 1) == 6))
                : [],
            'values' => $this->relationLoaded('hobbies')
                ? HobbyItemResource::collection($this->hobbies->filter(fn($item) => ($item->hobby?->type ?? 1) == 5))
                : [],

            // Gallery
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries')),

            // KYC Verification
            'kyc_verification' => $this->kycVerification ? [
                'status' => $this->kycVerification->status,
                'badge_style' => $this->kycVerification->badgeStyle ? [
                    'id' => $this->kycVerification->badgeStyle->id,
                    'name' => $this->kycVerification->badgeStyle->name,
                    'icon' => $this->kycVerification->badgeStyle->icon_path,
                ] : null,
                'badge_color' => $this->kycVerification->badgeColor ? [
                    'id' => $this->kycVerification->badgeColor->id,
                    'name' => $this->kycVerification->badgeColor->name,
                    'color_code' => $this->kycVerification->badgeColor->color_code,
                ] : null,
            ] : [
                'status' => 'not uploaded yet',
                'badge_style' => null,
                'badge_color' => null,
            ],
        ];
    }
}
