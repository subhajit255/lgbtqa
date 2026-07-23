<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusComment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function parent()
    {
        return $this->belongsTo(StatusComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(StatusComment::class, 'parent_id');
    }
}
