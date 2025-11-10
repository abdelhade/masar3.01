<?php

namespace Modules\Quality\Models;

use App\Models\User;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityAudit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'audit_number',
        'branch_id',
        'audit_title',
        'audit_type',
        'audit_scope',
        'planned_date',
        'actual_start_date',
        'actual_end_date',
        'lead_auditor_id',
        'audit_team',
        'external_auditor',
        'external_organization',
        'audit_objectives',
        'areas_covered',
        'standards_referenced',
        'checklist',
        'total_findings',
        'critical_findings',
        'major_findings',
        'minor_findings',
        'observations',
        'overall_result',
        'summary',
        'strengths',
        'weaknesses',
        'recommendations',
        'follow_up_date',
        'follow_up_status',
        'status',
        'attachments',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'audit_team' => 'array',
        'areas_covered' => 'array',
        'standards_referenced' => 'array',
        'checklist' => 'array',
        'attachments' => 'array',
        'planned_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'follow_up_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($audit) {
            if (empty($audit->audit_number)) {
                $audit->audit_number = 'AUD-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
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

    // Scopes
    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('planned_date', '>=', now())
                     ->where('planned_date', '<=', now()->addDays($days))
                     ->where('status', 'planned');
    }
}

