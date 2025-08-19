<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCompany extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'base_rate',
        'is_active'
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
