<?php

namespace App\Models;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BadgeStyle extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['icon_path'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::generate(4);
        });
    }

    public function getIconPathAttribute()
    {
        if (!$this->icon || !file_exists(storage_path('app/public/' . config('constants.SITE_BADGE_STYLE_UPLOAD_PATH') . $this->icon))) {
            return asset('assets/media/images/no-image.png');
        }
        return asset('storage/' . config('constants.SITE_BADGE_STYLE_UPLOAD_PATH') . $this->icon);
    }
}
