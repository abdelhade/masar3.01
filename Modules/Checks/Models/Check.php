<?php

namespace Modules\Checks\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounts\Models\AccHead;

class Check extends Model
{
    use HasFactory;

    protected $table = 'checks';

    protected $fillable = [
        'check_number',
        'bank_name',
        'account_number',
        'account_holder_name',
        'amount',
        'issue_date',
        'due_date',
        'payment_date',
        'status',
        'type',
        'payee_name',
        'payer_name',
        'notes',
        'reference_number',
        'attachments',
        'created_by',
        'approved_by',
        'approved_at',
        'oper_id',
        'invoice_id',
        'supplier_id',
        'customer_id',
        'handled_by',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'approved_at' => 'datetime',
            'amount' => 'decimal:2',
            'attachments' => 'array',
        ];
    }

    // Define the status constants
    const STATUS_PENDING = 'pending';

    const STATUS_CLEARED = 'cleared';

    const STATUS_BOUNCED = 'bounced';

    const STATUS_CANCELLED = 'cancelled';

    // Define the type constants
    const TYPE_INCOMING = 'incoming';

    const TYPE_OUTGOING = 'outgoing';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'معلق',
            self::STATUS_CLEARED => 'مصفى',
            self::STATUS_BOUNCED => 'مرتد',
            self::STATUS_CANCELLED => 'ملغى',
        ];
    }

    /**
     * Get all available types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_INCOMING => 'وارد',
            self::TYPE_OUTGOING => 'صادر',
        ];
    }

    /**
     * Get the user who created the check
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the check
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the operation head record
     */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\OperHead::class, 'oper_id');
    }

    /**
     * Get the invoice associated with the check
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\OperHead::class, 'invoice_id');
    }

    /**
     * Get the supplier associated with the check
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'supplier_id');
    }

    /**
     * Get the customer associated with the check
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'customer_id');
    }

    /**
     * Get the user who handled the check
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by due date range
     */
    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    /**
     * Check if the check is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING &&
               $this->due_date < now()->toDateString();
    }

    /**
     * Check if the check is cleared
     */
    public function isCleared(): bool
    {
        return $this->status === self::STATUS_CLEARED;
    }

    /**
     * Mark check as cleared
     */
    public function markAsCleared(?string $paymentDate = null): void
    {
        $this->update([
            'status' => self::STATUS_CLEARED,
            'payment_date' => $paymentDate ?? now()->toDateString(),
        ]);
    }

    /**
     * Mark check as bounced
     */
    public function markAsBounced(): void
    {
        $this->update([
            'status' => self::STATUS_BOUNCED,
            'payment_date' => null,
        ]);
    }

    /**
     * Cancel the check
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'payment_date' => null,
        ]);
    }

    /**
     * Approve the check
     */
    public function approve(int $userId): void
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CLEARED => 'success',
            self::STATUS_BOUNCED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'primary',
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Scope to filter overdue checks
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Scope to filter by date range
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get days until due date
     */
    public function daysUntilDue(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get days overdue
     */
    public function daysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Checks\Database\Factories\CheckFactory::new();
    }
}
