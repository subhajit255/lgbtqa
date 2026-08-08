<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webpatser\Uuid\Uuid;

class PostCategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        if (!$this->image || !file_exists(storage_path('app/public/' . config('constants.SITE_POST_CATEGORY_UPLOAD_PATH') . $this->image))) {
            return asset('assets/media/images/no-image.png');
        }
        return asset('storage/' . config('constants.SITE_POST_CATEGORY_UPLOAD_PATH') . $this->image);
    }
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }
}
