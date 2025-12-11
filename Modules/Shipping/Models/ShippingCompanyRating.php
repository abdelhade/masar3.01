<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCompanyRating extends Model
{
    protected $fillable = [
        'shipping_company_id',
        'shipment_id',
        'rating',
        'comment',
        'customer_name',
        'rated_by',
    ];

    public function shippingCompany()
    {
        return $this->belongsTo(ShippingCompany::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function ratedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'rated_by');
    }
}
