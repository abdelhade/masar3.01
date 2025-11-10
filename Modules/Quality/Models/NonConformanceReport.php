<?php

namespace Modules\Quality\Models;

use App\Models\Item;
use App\Models\User;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NonConformanceReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ncr_number',
        'branch_id',
        'inspection_id',
        'item_id',
        'batch_number',
        'affected_quantity',
        'source',
        'detected_date',
        'detected_by',
        'problem_description',
        'root_cause',
        'severity',
        'estimated_cost',
        'actual_cost',
        'immediate_action',
        'disposition',
        'assigned_to',
        'target_closure_date',
        'actual_closure_date',
        'status',
        'attachments',
        'closed_by',
        'closure_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'affected_quantity' => 'decimal:3',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'detected_date' => 'datetime',
        'target_closure_date' => 'date',
        'actual_closure_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($ncr) {
            if (empty($ncr->ncr_number)) {
                $ncr->ncr_number = 'NCR-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QualityInspection::class, 'inspection_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function detectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'detected_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'ncr_id');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_closure_date', '<', now())
                     ->whereNotIn('status', ['closed', 'cancelled']);
    }
}

