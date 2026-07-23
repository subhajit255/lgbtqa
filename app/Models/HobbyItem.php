<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webpatser\Uuid\Uuid;

class HobbyItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }

    public function hobby()
    {
        return $this->belongsTo(Hobby::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_hobby', 'hobby_item_id', 'user_id');
    }
}
