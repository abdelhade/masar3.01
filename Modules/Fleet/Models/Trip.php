<?php

declare(strict_types=1);

namespace Modules\Fleet\Models;

use App\Models\User;
use Modules\Branches\Models\Branch;
use Modules\Fleet\Enums\TripStatus;
use Modules\Shipping\Models\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trip_number',
        'vehicle_id',
        'driver_id',
        'branch_id',
        'start_location',
        'end_location',
        'start_date',
        'end_date',
        'start_mileage',
        'end_mileage',
        'distance',
        'purpose',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => TripStatus::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'start_mileage' => 'decimal:2',
        'end_mileage' => 'decimal:2',
        'distance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);

        static::creating(function ($trip): void {
            if (!$trip->trip_number) {
                $trip->trip_number = static::generateTripNumber();
            }
            if (auth()->check()) {
                $trip->created_by = auth()->id();
            }
        });

        static::updating(function ($trip): void {
            if (auth()->check()) {
                $trip->updated_by = auth()->id();
            }

            // Calculate distance when end_mileage is set
            if ($trip->isDirty('end_mileage') && $trip->end_mileage && $trip->start_mileage) {
                $trip->distance = $trip->end_mileage - $trip->start_mileage;
            }

            // Update vehicle mileage when trip is completed
            if ($trip->isDirty('status') && $trip->status === TripStatus::COMPLETED && $trip->end_mileage) {
                $trip->vehicle->update(['current_mileage' => $trip->end_mileage]);
            }
        });
    }

    public static function generateTripNumber(): string
    {
        $lastTrip = static::withoutGlobalScopes()->latest('id')->first();
        $number = $lastTrip ? ((int) str_replace('TRIP-', '', $lastTrip->trip_number)) + 1 : 1;

        return 'TRIP-' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
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
