<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferMetaLang extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_meta_id', 'meta_title', 'meta_description', 'meta_keywords', 'lang'
    ];
}
