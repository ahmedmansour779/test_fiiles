<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferSchema extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_product_quantity', 'last_product_quantity', 'discount', 'title', 'admin_id'
    ];

    protected $hidden = [
        'admin_id'
    ];
}
