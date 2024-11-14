<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => 'integer',
        'allow_coupon' => 'integer'
    ];


    protected $fillable = [
        'detail_title', 'listing_title', 'allow_coupon', 'start_time', 'end_time',
        'purchase_count_limit', 'total_value_limit', 'status',
        'shipping_rule_id', 'admin_id', 'offer_schema_id', 'vendor_id'
    ];

    protected $hidden = [
        'admin_id'
    ];

    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }


    public function vendor()
    {
        return $this->hasOne(Admin::class, 'id', 'vendor_id');
    }

    public function users()
    {
        return $this->hasMany(OfferUser::class, 'offer_id', 'id');
    }

    public function guest_users()
    {
        return $this->hasMany(OfferGuestUser::class, 'offer_id', 'id');
    }

    public function shipping_rule()
    {
        return $this->hasOne(ShippingRule::class, 'id', 'shipping_rule_id');
    }

    public function offer_schema()
    {
        return $this->hasOne(OfferSchema::class, 'id', 'offer_schema_id');

    }

    public function products()
    {
        return $this->hasMany(OfferProduct::class, 'offer_id', 'id');
    }


    public function payment_methods()
    {
        return $this->hasMany(OfferPayment::class, 'offer_id', 'id');
    }

    public function categories()
    {
        return $this->hasMany(OfferCategory::class, 'offer_id', 'id');
    }

    public function brands()
    {
        return $this->hasMany(OfferBrand::class, 'offer_id', 'id');
    }
}
