<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentStatusHistory extends Model
{
    protected $fillable = [
        'shipment_id',
        'status',
        'notes',
        'location',
        'changed_by',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
