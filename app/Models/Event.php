<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['image_path', 'host_image_path'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }

    public function getImagePathAttribute()
    {
        if (!$this->image || !file_exists(storage_path('app/public/events/' . $this->image))) {
            return asset('assets/media/svg/files/blank-image.png');
        }
        return asset('storage/events/' . $this->image);
    }

    public function getHostImagePathAttribute()
    {
        if (!$this->host_image || !file_exists(storage_path('app/public/events/' . $this->host_image))) {
            return asset('assets/media/avatars/blank.png');
        }
        return asset('storage/events/' . $this->host_image);
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class, 'event_id');
    }

    public function joinedUsers()
    {
        return $this->belongsToMany(User::class, 'event_participants', 'event_id', 'user_id')
            ->wherePivot('status', 'joined')
            ->withTimestamps();
    }

    public function interestedUsers()
    {
        return $this->belongsToMany(User::class, 'event_participants', 'event_id', 'user_id')
            ->wherePivot('status', 'interested')
            ->withTimestamps();
    }
}
