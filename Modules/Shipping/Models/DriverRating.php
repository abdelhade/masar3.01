<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class DriverRating extends Model
{
    protected $fillable = [
        'driver_id',
        'order_id',
        'rating',
        'comment',
        'customer_name',
        'rated_by',
    ];

    protected static function booted()
    {
        static::created(function ($rating) {
            $rating->driver->updateRating();
        });
        
        static::updated(function ($rating) {
            $rating->driver->updateRating();
        });
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ratedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'rated_by');
    }
}
