<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'image', 'status', 'featured', 'admin_id', 'slug'
    ];

    protected $hidden = [
        'admin_id'
    ];


    public function offer_brand()
    {
        return $this->hasOne(OfferBrand::class, 'brand_id', 'id');
    }
}
