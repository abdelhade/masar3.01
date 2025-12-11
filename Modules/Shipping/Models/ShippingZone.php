<?php

namespace Modules\Shipping\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'base_rate',
        'rate_per_kg',
        'estimated_days',
        'is_active',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
        
        static::creating(function ($zone) {
            if (auth()->check()) {
                $zone->created_by = auth()->id();
            }
        });
        
        static::updating(function ($zone) {
            if (auth()->check()) {
                $zone->updated_by = auth()->id();
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
