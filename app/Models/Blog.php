<?php

namespace App\Models;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }
    protected $guarded  = [];
    protected $appends = ['image_path'];
    public function getImagePathAttribute()
    {
        if (!$this->file || !file_exists(storage_path('app/public/blog/' . $this->file))) {
            return asset('assets/media/images/no-image.png');
        }
        return asset('storage/blog/' . $this->file);
    }
}
