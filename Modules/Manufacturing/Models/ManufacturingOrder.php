<?php

namespace Modules\Manufacturing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Branches\Models\Branch;

class ManufacturingOrder extends Model
{
    protected $fillable = [
        'order_number',
        'template_name',
        'branch_id',
        'status',
        'description',
        'item_id',
        'estimated_duration',
        'is_template',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'estimated_duration' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(
            ManufacturingStage::class,
            'manufacturing_order_stage',
            'manufacturing_order_id',
            'manufacturing_stage_id'
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
            ->withTimestamps()
            ->orderBy('manufacturing_order_stage.order', 'asc');
    }

    public function calculateTotals(): void
    {
        $this->estimated_duration = $this->stages()->sum('manufacturing_order_stage.estimated_duration');
        $this->save();
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;

        return 'MO-' . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeOrders($query)
    {
        return $query->where('is_template', false);
    }
}
