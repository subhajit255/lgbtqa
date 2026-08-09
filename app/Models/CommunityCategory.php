<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_category_community');
    }
}
