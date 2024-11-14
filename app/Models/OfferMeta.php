<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferMeta extends Model
{
    use HasFactory;


    protected $fillable = [
        'admin_id', 'meta_title', 'meta_description', 'meta_keywords'
    ];

    protected $hidden = [
        'admin_id'
    ];
}
