<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostMedia extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function getFilePathAttribute()
    {
        if (!$this->file) {
            return asset('assets/media/images/no-image.png');
        }

        if (str_starts_with($this->file, 'http://') || str_starts_with($this->file, 'https://')) {
            return $this->file;
        }

        // Case 1: File created via API (stored in public/assets/uploads/posts)
        if (str_starts_with($this->file, 'assets/uploads/posts/')) {
            return asset($this->file);
        }

        // Case 2: File created via Admin (stored in storage/app/public/posts)
        if (str_starts_with($this->file, 'storage/posts/')) {
            return asset($this->file);
        }

        if (file_exists(storage_path('app/public/posts/' . $this->file))) {
            return asset('storage/posts/' . $this->file);
        }

        if (file_exists(public_path('storage/posts/' . $this->file))) {
            return asset('storage/posts/' . $this->file);
        }

        if (file_exists(public_path('assets/uploads/posts/' . $this->file))) {
            return asset('assets/uploads/posts/' . $this->file);
        }

        return asset('storage/posts/' . $this->file);
    }

    public function getFileTypeAttribute($value)
    {
        $ext = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm', '3gp', 'flv', 'wmv', 'm4v'])) {
            return 'video';
        }
        return $value ? strtolower($value) : 'image';
    }
}
