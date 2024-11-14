<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id', 'product_id',
    ];


    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')
            ->select(['id', 'title']);
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }
}
