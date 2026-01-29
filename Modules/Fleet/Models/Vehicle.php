<?php

declare(strict_types=1);

namespace Modules\Fleet\Models;

use App\Models\User;
use Modules\Branches\Models\Branch;
use Modules\Fleet\Enums\VehicleStatus;
use Modules\Shipping\Models\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'plate_number',
        'vehicle_type_id',
        'driver_id',
        'branch_id',
        'name',
        'model',
        'year',
        'color',
        'chassis_number',
        'engine_number',
        'current_mileage',
        'status',
        'purchase_date',
        'purchase_cost',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => VehicleStatus::class,
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_mileage' => 'decimal:2',
        'is_active' => 'boolean',
        'insurance_renewal_date' => 'date',
    ];

    /**
     * التحقق من اقتراب موعد تجديد التأمين
     */
    public function isInsuranceRenewalSoon(): bool
    {
        if (!$this->insurance_renewal_date) {
            return false;
        }

        $notificationDate = $this->insurance_renewal_date->subDays($this->insurance_notification_days ?? 30);
        return now()->greaterThanOrEqualTo($notificationDate) && now()->lessThan($this->insurance_renewal_date);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);

        static::creating(function ($vehicle): void {
            if (!$vehicle->code) {
                $vehicle->code = static::generateCode();
            }
            if (auth()->check()) {
                $vehicle->created_by = auth()->id();
            }
        });

        static::updating(function ($vehicle): void {
            if (auth()->check()) {
                $vehicle->updated_by = auth()->id();
            }
        });
    }

    public static function generateCode(): string
    {
        $lastVehicle = static::withoutGlobalScopes()->latest('id')->first();
        $number = $lastVehicle ? ((int) str_replace('VEH-', '', $lastVehicle->code)) + 1 : 1;

        return 'VEH-' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
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
