<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bug extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'text',
        'image',
        'status',
    ];

    /**
     * Get the user that reported the bug.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL to the bug report image.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/bugs/' . $this->image);
        }
        return null;
    }
}
