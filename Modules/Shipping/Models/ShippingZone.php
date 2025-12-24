<?php

declare(strict_types=1);

namespace Modules\Shipping\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'base_rate',
        'rate_per_kg',
        'estimated_days',
        'is_active',
        'branch_id',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
