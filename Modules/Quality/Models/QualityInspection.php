<?php

namespace Modules\Quality\Models;

use App\Models\Item;
use App\Models\User;
use Modules\Accounts\Models\AccHead;
use App\Models\OperHead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QualityInspection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inspection_number',
        'branch_id',
        'item_id',
        'quality_standard_id',
        'batch_number',
        'quantity_inspected',
        'purchase_invoice_id',
        'manufacturing_order_id',
        'supplier_id',
        'inspection_type',
        'inspection_date',
        'inspector_id',
        'test_results',
        'pass_quantity',
        'fail_quantity',
        'pass_percentage',
        'result',
        'defects_found',
        'inspector_notes',
        'action_taken',
        'attachments',
        'approved_by',
        'approved_at',
        'approval_notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'test_results' => 'array',
        'attachments' => 'array',
        'quantity_inspected' => 'decimal:3',
        'pass_quantity' => 'decimal:3',
        'fail_quantity' => 'decimal:3',
        'pass_percentage' => 'decimal:2',
        'inspection_date' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($inspection) {
            // Auto-calculate pass percentage
            if ($inspection->quantity_inspected > 0) {
                $inspection->pass_percentage = ($inspection->pass_quantity / $inspection->quantity_inspected) * 100;
            }
        });

        static::updating(function ($inspection) {
            // Auto-calculate pass percentage on update
            if ($inspection->quantity_inspected > 0) {
                $inspection->pass_percentage = ($inspection->pass_quantity / $inspection->quantity_inspected) * 100;
            }
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'supplier_id');
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(OperHead::class, 'purchase_invoice_id');
    }

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(OperHead::class, 'manufacturing_order_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function nonConformanceReport(): HasOne
    {
        return $this->hasOne(NonConformanceReport::class, 'inspection_id');
    }

    public function batchTracking(): HasOne
    {
        return $this->hasOne(BatchTracking::class, 'inspection_id');
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('result', 'fail');
    }

    public function scopeByInspectionType($query, $type)
    {
        return $query->where('inspection_type', $type);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

