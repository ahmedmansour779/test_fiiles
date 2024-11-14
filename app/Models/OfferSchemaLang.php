<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferSchemaLang extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'lang', 'offer_schema_id'
    ];
}
