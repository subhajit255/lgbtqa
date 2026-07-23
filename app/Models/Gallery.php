<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webpatser\Uuid\Uuid;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $appends = ['image_path'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }

    public function getImagePathAttribute()
    {
        if (!$this->file || !file_exists(storage_path('app/public/gallery/' . $this->file))) {
            return asset('assets/media/images/no-image.png');
        }
        return asset('storage/gallery/' . $this->file);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
