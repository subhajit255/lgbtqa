<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorHistory extends Model
{
    protected $guarded = [];

    public function visitor()
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }

    public function visited()
    {
        return $this->belongsTo(User::class, 'visited_id');
    }
}
