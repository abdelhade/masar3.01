<?php

namespace Modules\Quality\Models;

use App\Models\User;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCertificate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'certificate_number',
        'branch_id',
        'certificate_name',
        'certificate_type',
        'custom_type',
        'issuing_authority',
        'authority_contact',
        'issue_date',
        'expiry_date',
        'last_audit_date',
        'next_audit_date',
        'scope',
        'covered_items',
        'covered_processes',
        'status',
        'notify_before_expiry',
        'notification_days',
        'attachments',
        'certificate_cost',
        'renewal_cost',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'covered_items' => 'array',
        'covered_processes' => 'array',
        'attachments' => 'array',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'last_audit_date' => 'date',
        'next_audit_date' => 'date',
        'notify_before_expiry' => 'boolean',
        'certificate_cost' => 'decimal:2',
        'renewal_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = null)
    {
        return $query->where(function ($q) use ($days) {
            $q->where(function ($subQ) use ($days) {
                $notificationDays = $days ?? 90;
                $subQ->where('expiry_date', '<=', now()->addDays($notificationDays))
                     ->where('expiry_date', '>=', now());
            });
        })->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                     ->whereIn('status', ['active', 'renewal_pending']);
    }

    // Helpers
    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }

    public function isExpiringSoon(): bool
    {
        $notificationDate = now()->addDays($this->notification_days);
        return $this->expiry_date <= $notificationDate && $this->expiry_date >= now();
    }

    public function daysUntilExpiry(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }
}

