<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id', 'brand_id',
    ];

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }
}
