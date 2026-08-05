<?php

namespace App\Http\Resources\Api\User;

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
