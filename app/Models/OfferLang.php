<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferLang extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_title', 'listing_title', 'lang', 'offer_id'
    ];
}
