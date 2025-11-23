<?php

namespace Modules\MyResources\Models;

use App\Models\User;
use App\Models\Employee;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Modules\Maintenance\Models\PeriodicMaintenanceSchedule;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Resource extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'resource_category_id',
        'resource_type_id',
        'resource_status_id',
        'branch_id',
        'employee_id',
        'serial_number',
        'model_number',
        'manufacturer',
        'purchase_date',
        'purchase_cost',
        'daily_rate',
        'hourly_rate',
        'current_location',
        'supplier_id',
        'warranty_expiry',
        'last_maintenance_date',
        'next_maintenance_date',
        'specifications',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);

        static::creating(function ($resource) {
            if (!$resource->code) {
                $resource->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $lastResource = static::withoutGlobalScopes()->latest('id')->first();
        $nextId = $lastResource ? $lastResource->id + 1 : 1;

        return 'RES-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'resource_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class, 'resource_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ResourceStatus::class, 'resource_status_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ResourceStatusHistory::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ResourceDocument::class);
    }

    public function maintenanceSchedules(): MorphMany
    {
        return $this->morphMany(PeriodicMaintenanceSchedule::class, 'maintainable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function currentAssignment()
    {
        return $this->assignments()
            ->where('status', 'active')
            ->where('assignment_type', 'current')
            ->first();
    }

    public function upcomingAssignments(): HasMany
    {
        return $this->assignments()
            ->where('status', 'scheduled')
            ->where('assignment_type', 'upcoming')
            ->orderBy('start_date');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('resource_category_id', $categoryId);
    }

    public function scopeByType($query, int $typeId)
    {
        return $query->where('resource_type_id', $typeId);
    }

    public function scopeByStatus($query, int $statusId)
    {
        return $query->where('resource_status_id', $statusId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%");
        });
    }
}

