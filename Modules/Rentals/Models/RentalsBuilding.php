<?php

namespace Modules\Rentals\Models;

use Illuminate\Database\Eloquent\Model;

class RentalsBuilding extends Model
{
    protected $fillable = [
        'name',
        'address',
        'floors',
        'area',
        'details',
    ];

    protected $casts = [
        'floors' => 'integer',
    ];

    public function units()
    {
        return $this->hasMany(RentalsUnit::class, 'building_id');
    }

    public function accBuilding()
    {
        return $this->hasMany(RentalsUnit::class, 'building_id');
    }
}
