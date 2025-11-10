<?php

namespace Modules\Quality\Models;

use App\Models\Item;
use App\Models\User;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityStandard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id',
        'branch_id',
        'standard_code',
        'standard_name',
        'description',
        'specifications',
        'chemical_properties',
        'physical_properties',
        'test_method',
        'sample_size',
        'test_frequency',
        'acceptance_threshold',
        'max_defects_allowed',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'specifications' => 'array',
        'chemical_properties' => 'array',
        'physical_properties' => 'array',
        'acceptance_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(QualityInspection::class, 'quality_standard_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }
}

