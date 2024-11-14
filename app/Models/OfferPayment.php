<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id', 'payment_method_id',
    ];


    public function payment_method()
    {
        return $this->hasOne(PaymentMethod::class, 'id', 'payment_method_id')
            ->select(['id', 'title']);
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }
}
