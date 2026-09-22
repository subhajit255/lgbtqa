<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use \Webpatser\Uuid\Uuid;

class UserSubscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
