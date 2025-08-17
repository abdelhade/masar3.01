<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'tracking_number',
        'shipping_company_id',
        'customer_name',
        'customer_address',
        'weight',
        'status'
    ];

    public function shippingCompany()
    {
        return $this->belongsTo(ShippingCompany::class);
    }
}
