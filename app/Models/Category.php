<?php

namespace App\Models;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
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
        if (!$this->file || !file_exists(storage_path('app/public/category/' . $this->file))) {
            return asset('assets/media/images/no-image.png');
        }
        return asset('storage/category/' . $this->file);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    public function attribute(): HasMany
    {
        return $this->hasMany(Attribute::class, 'category_id');
    }
}
