<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class SubscriptionCollection extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid ?? null,
            'since' => Carbon::parse($this->created_at)->diffForHumans(),
            'name' => $this->plan?->name,
            'description' => $this->plan?->description,
            'features' => $this->plan?->features,
            'price' => $this->price,
            'currency' => $this->plan?->currency,
            'interval' => $this->plan?->billing_cycle,
            'started_on' => Carbon::parse($this->starts_at)->diffForHumans(),
            'ended_at' => Carbon::parse($this->expires_at)->diffForHumans(),
        ];
    }
}
