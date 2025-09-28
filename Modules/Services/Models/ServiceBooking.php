<?php

namespace Modules\Services\Models;

use App\Models\User;
use App\Models\AccHead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceBooking extends Model
{
    use HasFactory;

    protected $table = 'service_bookings';
    protected $guarded = ['id'];

    protected $casts = [
        'booking_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
        'is_completed' => 'boolean',
        'is_cancelled' => 'boolean',
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    // }

    protected static function newFactory()
    {
        return \Modules\Services\Database\Factories\ServiceBookingFactory::new();
    }

    /**
     * Get the service that owns the booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the customer for the booking.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'customer_id');
    }

    /**
     * Get the employee assigned to the booking.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'employee_id');
    }

    /**
     * Get the user who created the booking.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the branch for the booking.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope a query to only include completed bookings.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', 1);
    }

    /**
     * Scope a query to only include pending bookings.
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', 0)->where('is_cancelled', 0);
    }

    /**
     * Scope a query to only include cancelled bookings.
     */
    public function scopeCancelled($query)
    {
        return $query->where('is_cancelled', 1);
    }

    /**
     * Get the booking status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_cancelled) {
            return 'cancelled';
        } elseif ($this->is_completed) {
            return 'completed';
        } else {
            return 'pending';
        }
    }

    /**
     * Get the booking status in Arabic.
     */
    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'cancelled' => 'ملغي',
            'completed' => 'مكتمل',
            'pending' => 'معلق',
            default => 'غير محدد'
        };
    }
}
