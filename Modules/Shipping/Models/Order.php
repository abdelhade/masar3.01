<?php

namespace Modules\Shipping\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'driver_id',
        'shipment_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'delivery_status',
        'scheduled_date',
        'scheduled_time_from',
        'scheduled_time_to',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'delivery_notes',
        'delivery_attempts',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
        
        static::creating(function ($order) {
            if (auth()->check()) {
                $order->created_by = auth()->id();
            }
        });
        
        static::updating(function ($order) {
            if (auth()->check()) {
                $order->updated_by = auth()->id();
            }
            
            if ($order->isDirty('delivery_status')) {
                if ($order->delivery_status === 'assigned' && !$order->assigned_at) {
                    $order->assigned_at = now();
                }
                if ($order->delivery_status === 'in_transit' && !$order->picked_up_at) {
                    $order->picked_up_at = now();
                }
                if ($order->delivery_status === 'delivered' && !$order->delivered_at) {
                    $order->delivered_at = now();
                    $order->driver->increment('completed_deliveries');
                    $order->driver->is_available = true;
                    $order->driver->save();
                }
            }
        });
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function rating()
    {
        return $this->hasOne(DriverRating::class);
    }
}
