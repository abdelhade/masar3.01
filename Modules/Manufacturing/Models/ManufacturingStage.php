<?php

namespace Modules\Manufacturing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Branches\Models\Branch;

class ManufacturingStage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'branch_id',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders()
    {
        return $this->belongsToMany(
            ManufacturingOrder::class,
            'manufacturing_order_stage',
            'manufacturing_stage_id',
            'manufacturing_order_id'
        )
            ->withPivot([
                'order',
                'quantity',
                'estimated_duration',
                'actual_duration',
                'status',
                'notes',
                'is_active',
                'started_at',
                'completed_at',
                'assigned_to'
            ])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
