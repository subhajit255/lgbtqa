<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppSettingToggle extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['distance_range'];

    protected $casts = [
        'stealth_mode' => 'boolean',
        'ghost_mode' => 'boolean',
        'two_factor_auth' => 'boolean',
        'biometric_login' => 'boolean',
        'login_alerts' => 'boolean',
        'show_in_discovery' => 'boolean',
        'location_based' => 'boolean',
        'match_by_interests' => 'boolean',
        'pride_events_nearby' => 'boolean',
        'message_friends_only' => 'boolean',
        'message_community' => 'boolean',
        'message_open' => 'boolean',
        'notify_new_message' => 'boolean',
        'notify_event_reminder' => 'boolean',
        'notify_friend_requests' => 'boolean',
        'notify_post_interactions' => 'boolean',
        'notify_mentions_tags' => 'boolean',
        'notify_profile_visits' => 'boolean',
        'notify_marketing_updates' => 'boolean',
        'push_notification' => 'boolean',
        'email_notification' => 'boolean',
    ];

    public function getDistanceRangeAttribute()
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?: $this->user;
        return $user?->profile?->distance_range ?? 1;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
