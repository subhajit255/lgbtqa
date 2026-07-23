<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['image_path', 'online_members_count'];

    protected $casts = [
        'is_group' => 'boolean',
        'is_public' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_participants');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function pinnedMessages()
    {
        return $this->hasMany(Message::class)->where('is_pinned', true)->latest();
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeDiscoverable($query)
    {
        return $query->where('is_group', true)->where('is_public', true);
    }

    public function getImagePathAttribute()
    {
        if ($this->image && file_exists(storage_path('app/public/groups/' . $this->image))) {
            return asset('storage/groups/' . $this->image);
        }
        return asset('assets/media/svg/files/blank-image.png');
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function getOnlineMembersCountAttribute()
    {
        if (!$this->is_group) {
            return 0;
        }

        return $this->participants()->get()->filter(function ($participant) {
            return \Illuminate\Support\Facades\Cache::has('user-is-online-' . $participant->user_id);
        })->count();
    }
}
