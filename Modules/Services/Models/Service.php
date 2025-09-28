<?php

namespace Modules\Services\Models;

use App\Models\Note;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Services\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_taxable' => 'boolean',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    protected $attributes = [
        'is_active' => false,
        'is_taxable' => false,
        'price' => 0,
        'cost' => 0,
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    // }

    /**
     * Get the branch that owns the service.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the units for the service.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'service_unit_id');
    }


    /**
     * Get the service bookings.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(ServiceBooking::class);
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include inactive services.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }

    /**
     * Scope a query to only include taxable services.
     */
    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', 1);
    }

    /**
     * Scope a query to only include non-taxable services.
     */
    public function scopeNonTaxable($query)
    {
        return $query->where('is_taxable', 0);
    }

    public function serviceType(): belongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function serviceUnit(): belongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'service_unit_id');
    }


    protected static function newFactory()
    {
        return \Modules\Services\Database\Factories\ServiceFactory::new();
    }
}
