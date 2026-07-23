<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KycVerification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'govt_id_image',
        'identity_image',
        'badge_style_id',
        'badge_color_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badgeStyle()
    {
        return $this->belongsTo(BadgeStyle::class);
    }

    public function badgeColor()
    {
        return $this->belongsTo(BadgeColor::class);
    }

    public function getGovtIdImageUrlAttribute()
    {
        if ($this->govt_id_image) {
            return asset('storage/kyc/' . $this->govt_id_image);
        }
        return null;
    }

    public function getIdentityImageUrlAttribute()
    {
        if ($this->identity_image) {
            return asset('storage/kyc/' . $this->identity_image);
        }
        return null;
    }
}
