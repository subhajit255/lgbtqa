<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $emojiSymbols = [
            'LIKE' => '👍',
            'LOVE' => '❤️',
            'HAHA' => '😂',
            'WOW' => '😮',
            'SAD' => '😢',
            'ANGRY' => '😡',
        ];

        $recentEmojiSymbols = [];
        if ($this->relationLoaded('emojis')) {
            $uniqueCodes = $this->emojis->pluck('emoji')->unique();
            foreach ($uniqueCodes as $code) {
                if (isset($emojiSymbols[$code])) {
                    $recentEmojiSymbols[] = $emojiSymbols[$code];
                }
            }
        }

        $userId = auth('api')->id() ?? auth()->id();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'user' => $this->whenLoaded('user', function () {
                $profile = $this->user->profile;
                $location = '';
                if ($profile) {
                    $locParts = array_filter([$profile->living_in_country, $profile->living_in_city]);
                    $location = implode(', ', $locParts);
                }
                $isVerified = $this->user->kycVerification()->where('status', 'approved')->exists();

                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'profile_image' => $this->user->image_path,
                    'location' => $location,
                    'is_verified' => $isVerified,
                ];
            }),
            'media' => PostMediaResource::collection($this->whenLoaded('media')),
            'loves_count' => $this->relationLoaded('loves') ? $this->loves->count() : 0,
            'comments_count' => $this->relationLoaded('comments') ? $this->comments->count() : 0,
            'stars_count' => $this->relationLoaded('stars') ? $this->stars->count() : 0,
            'emojis_count' => $this->relationLoaded('emojis') ? $this->emojis->count() : 0,
            'user_loved' => $userId && $this->relationLoaded('loves') ? $this->loves->where('user_id', $userId)->isNotEmpty() : false,
            'user_starred' => $userId && $this->relationLoaded('stars') ? $this->stars->where('user_id', $userId)->isNotEmpty() : false,
            'user_emoji' => $userId && $this->relationLoaded('emojis') ? ($this->emojis->where('user_id', $userId)->first()->emoji ?? null) : null,
            'recent_emojis' => $recentEmojiSymbols,
        ];
    }
}
