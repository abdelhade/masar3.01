<?php

declare(strict_types=1);

namespace Modules\Fleet\Models;

use App\Models\User;
use Modules\Branches\Models\Branch;
use Modules\Fleet\Enums\FuelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'trip_id',
        'branch_id',
        'fuel_date',
        'fuel_type',
        'quantity',
        'cost',
        'mileage_at_fueling',
        'station_name',
        'receipt_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fuel_type' => FuelType::class,
        'fuel_date' => 'date',
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2',
        'mileage_at_fueling' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);

        static::creating(function ($fuelRecord): void {
            if (auth()->check()) {
                $fuelRecord->created_by = auth()->id();
            }
        });

        static::updating(function ($fuelRecord): void {
            if (auth()->check()) {
                $fuelRecord->updated_by = auth()->id();
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
