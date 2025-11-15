<?php

namespace Modules\Quality\Models;

use App\Models\User;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierRating extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'rating_date',
        'period_type',
        'period_start',
        'period_end',
        'quality_score',
        'total_inspections',
        'passed_inspections',
        'failed_inspections',
        'pass_rate',
        'delivery_score',
        'total_deliveries',
        'on_time_deliveries',
        'on_time_rate',
        'documentation_score',
        'certificates_required',
        'certificates_received',
        'ncrs_raised',
        'critical_ncrs',
        'major_ncrs',
        'minor_ncrs',
        'overall_score',
        'rating',
        'strengths',
        'weaknesses',
        'improvement_required',
        'recommended_actions',
        'supplier_status',
        'rated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'rating_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'approved_at' => 'datetime',
        'quality_score' => 'decimal:2',
        'pass_rate' => 'decimal:2',
        'delivery_score' => 'decimal:2',
        'on_time_rate' => 'decimal:2',
        'documentation_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($rating) {
            // Auto-calculate pass rate
            if ($rating->total_inspections > 0) {
                $rating->pass_rate = ($rating->passed_inspections / $rating->total_inspections) * 100;
            }
            
            // Auto-calculate on-time rate
            if ($rating->total_deliveries > 0) {
                $rating->on_time_rate = ($rating->on_time_deliveries / $rating->total_deliveries) * 100;
            }
            
            // Auto-calculate overall score (weighted average)
            $rating->overall_score = (
                ($rating->quality_score * 0.5) +
                ($rating->delivery_score * 0.3) +
                ($rating->documentation_score * 0.2)
            );
            
            // Auto-determine rating
            $rating->rating = match(true) {
                $rating->overall_score >= 90 => 'excellent',
                $rating->overall_score >= 75 => 'good',
                $rating->overall_score >= 60 => 'acceptable',
                $rating->overall_score >= 50 => 'poor',
                default => 'unacceptable'
            };
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'supplier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ratedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeExcellent($query)
    {
        return $query->where('rating', 'excellent');
    }

    public function scopePoor($query)
    {
        return $query->whereIn('rating', ['poor', 'unacceptable']);
    }
}

