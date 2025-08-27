<?php

namespace Modules\Rentals\Models;

use Modules\Rentals\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Model;

class RentalsUnit extends Model
{
    protected $fillable = [
        'building_id',
        'name',
        'floor',
        'area',
        'status',
        'details',
    ];
    protected $attributes = [
        'status' => UnitStatus::AVAILABLE,
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function building()
    {
        return $this->belongsTo(RentalsBuilding::class);
    }

    public function leases()
    {
        return $this->hasMany(RentalsLease::class, 'unit_id');
    }
}
