<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'vehicle_type',
        'is_available'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
