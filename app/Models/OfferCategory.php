<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id', 'category_id',
    ];


    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')
            ->select(['id', 'title']);
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }
}
