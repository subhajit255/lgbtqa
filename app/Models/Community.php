<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class Community extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['image_path'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }

    public function getImagePathAttribute()
    {
        if (!$this->image || !file_exists(storage_path('app/public/communities/' . $this->image))) {
            return asset('assets/media/svg/files/blank-image.png');
        }
        return asset('storage/communities/' . $this->image);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->hasMany(CommunityMember::class, 'community_id');
    }

    public function activeMembers()
    {
        return $this->belongsToMany(User::class, 'community_members', 'community_id', 'user_id')
            ->wherePivot('status', 'active')
            ->withTimestamps();
    }

    public function pendingRequests()
    {
        return $this->hasMany(CommunityMember::class, 'community_id')
            ->where('status', 'pending');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }
}
