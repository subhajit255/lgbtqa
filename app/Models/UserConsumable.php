<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConsumable extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'active_private_browsing_until' => 'datetime',
        'active_profile_spotlight_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
