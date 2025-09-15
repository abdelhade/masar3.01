<?php

namespace Modules\Rentals\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class RentalsBuilding extends Model
{
    protected $fillable = [
        'name',
        'address',
        'floors',
        'area',
        'details',
        'branch_id',
    ];

    protected $casts = [
        'floors' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function units()
    {
        return $this->hasMany(RentalsUnit::class, 'building_id');
    }

    public function accBuilding()
    {
        return $this->hasMany(RentalsUnit::class, 'building_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
