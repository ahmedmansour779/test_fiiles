<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferGuestUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id', 'guest_user_id',
    ];

    public function guest_user()
    {
        return $this->hasOne(GuestUser::class, 'id', 'guest_user_id');
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }
}
