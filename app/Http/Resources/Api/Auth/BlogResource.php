<?php

namespace App\Http\Resources\Api\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'short_desc' => Str::limit($this->description, 50, $end = '...'),
            'description' => $this->description,
            'image' => $this->image_path,
            'slug' => $this->slug,
            'meta_data' => $this->meta_data,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'added_time' => Carbon::parse($this->created_at)->format('F j, Y, g:i a'),
        ];
    }
}
