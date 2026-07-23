<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taggedUser()
    {
        return $this->belongsTo(User::class, 'tagged_user_id');
    }

    public function reactions()
    {
        return $this->hasMany(StatusReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(StatusComment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('expires_at', '>', now());
    }
}
