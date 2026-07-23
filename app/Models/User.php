<?php

namespace App\Models;

use App\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\HasApiTokens;
use Webpatser\Uuid\Uuid;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRolesAndPermissions, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
            if (empty($model->unique_code)) {
                $model->unique_code = self::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(10));
        } while (self::where('unique_code', $code)->exists());

        return $code;
    }

    protected $appends = ['image_path', 'is_online', 'last_seen_at'];

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'pause_notifications' => 'boolean',
    ];

    public function getImagePathAttribute()
    {
        if (!$this->profile_image || !file_exists(storage_path('app/public/profile/' . $this->profile_image))) {
            return asset('assets/media/avatars/blank.png');
        }
        return asset('storage/profile/' . $this->profile_image);
    }

    public function getIsOnlineAttribute()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    public function getLastSeenAtAttribute()
    {
        return Cache::get('user-last-seen-' . $this->id);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function appSettingToggle()
    {
        return $this->hasOne(AppSettingToggle::class);
    }

    public function audienceVisibility()
    {
        return $this->hasOne(AudienceVisibility::class);
    }

    public function hobbies()
    {
        return $this->belongsToMany(HobbyItem::class, 'user_hobby', 'user_id', 'hobby_item_id');
    }

    public function userHobbies()
    {
        return $this->hasMany(UserHobby::class, 'user_id');
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    public function kycVerification()
    {
        return $this->hasOne(KycVerification::class);
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'user_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'friend_id');
    }

    public function friends()
    {
        // Accepted friends where this user was either the sender or receiver
        return User::whereIn('id', function($query) {
            $query->select('friend_id')->from('friend_requests')->where('user_id', $this->id)->where('status', 'accepted')
                  ->union(
                      $query->newQuery()->select('user_id')->from('friend_requests')->where('friend_id', $this->id)->where('status', 'accepted')
                  );
        });
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'user_id', 'blocked_user_id');
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function userLocation()
    {
        return $this->hasOne(UserLocation::class);
    }
}
