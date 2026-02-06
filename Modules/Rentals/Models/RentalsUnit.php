<?php

namespace Modules\Rentals\Models;

use Modules\Rentals\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RentalsUnit extends Model
{
    protected $fillable = [
        'unit_type',
        'item_id',
        'building_id',
        'name',
        'floor',
        'area',
        'status',
        'details',
    ];

    protected $attributes = [
        'status' => UnitStatus::AVAILABLE,
        'unit_type' => 'building',
    ];

    protected $casts = [
        'details' => 'array',
        'status' => UnitStatus::class,
    ];

    public function building()
    {
        return $this->belongsTo(RentalsBuilding::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Item::class);
    }

    public function leases()
    {
        return $this->hasMany(RentalsLease::class, 'unit_id');
    }

    public function scopeBuildings(Builder $query)
    {
        return $query->where('unit_type', 'building');
    }

    public function scopeItems(Builder $query)
    {
        return $query->where('unit_type', 'item');
    }
}
