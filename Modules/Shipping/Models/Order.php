<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'driver_id',
        'shipment_id',
        'customer_name',
        'customer_address',
        'delivery_status'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
