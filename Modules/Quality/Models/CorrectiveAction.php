<?php

namespace Modules\Quality\Models;

use App\Models\User;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectiveAction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'capa_number',
        'branch_id',
        'ncr_id',
        'action_type',
        'action_description',
        'root_cause_analysis',
        'preventive_measures',
        'responsible_person',
        'department_id',
        'planned_start_date',
        'planned_completion_date',
        'actual_start_date',
        'actual_completion_date',
        'completion_percentage',
        'implementation_notes',
        'verified_by',
        'verification_date',
        'verification_result',
        'is_effective',
        'status',
        'attachments',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'planned_start_date' => 'date',
        'planned_completion_date' => 'date',
        'actual_start_date' => 'date',
        'actual_completion_date' => 'date',
        'verification_date' => 'date',
        'is_effective' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($capa) {
            if (empty($capa->capa_number)) {
                $capa->capa_number = 'CAPA-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function nonConformanceReport(): BelongsTo
    {
        return $this->belongsTo(NonConformanceReport::class, 'ncr_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'department_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeOverdue($query)
    {
        return $query->where('planned_completion_date', '<', now())
                     ->whereNotIn('status', ['completed', 'verified', 'closed']);
    }

    // Helpers
    public function isOverdue(): bool
    {
        return $this->planned_completion_date < now() && 
               !in_array($this->status, ['completed', 'verified', 'closed']);
    }
}

