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

class BatchTracking extends Model
{
    use SoftDeletes;

    protected $table = 'batch_tracking';

    protected $fillable = [
        'batch_number',
        'branch_id',
        'item_id',
        'production_date',
        'expiry_date',
        'quantity',
        'remaining_quantity',
        'supplier_id',
        'purchase_invoice_id',
        'manufacturing_order_id',
        'inspection_id',
        'quality_status',
        'quality_notes',
        'warehouse_id',
        'location',
        'parent_batches',
        'child_batches',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'parent_batches' => 'array',
        'child_batches' => 'array',
        'quantity' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'production_date' => 'date',
        'expiry_date' => 'date',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'supplier_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'warehouse_id');
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(OperHead::class, 'purchase_invoice_id');
    }

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(OperHead::class, 'manufacturing_order_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QualityInspection::class, 'inspection_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now())
                     ->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                     ->where('status', 'active');
    }

    // Helpers
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function isExpiringSoon($days = 30): bool
    {
        return $this->expiry_date && 
               $this->expiry_date <= now()->addDays($days) &&
               $this->expiry_date >= now();
    }
}

