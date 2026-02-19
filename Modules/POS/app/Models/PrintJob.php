<?php

declare(strict_types=1);

namespace Modules\POS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    protected $fillable = [
        'printer_station_id',
        'transaction_id',
        'content',
        'status',
        'error_message',
        'attempts',
        'is_manual',
        'printed_at',
        'printed_by',
    ];

    protected $casts = [
        'is_manual' => 'boolean',
        'printed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Get the printer station that owns this print job.
     */
    public function printerStation(): BelongsTo
    {
        return $this->belongsTo(KitchenPrinterStation::class, 'printer_station_id');
    }

    /**
     * Get the transaction associated with this print job.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CashierTransaction::class, 'transaction_id');
    }

    /**
     * Get the user who printed this job manually.
     */
    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    /**
     * Mark the print job as successful.
     */
    public function markAsSuccess(): void
    {
        $this->update([
            'status' => 'success',
            'printed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark the print job as failed with an error message.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'attempts' => $this->attempts + 1,
        ]);
    }

    /**
     * Mark the print job as retrying.
     */
    public function markAsRetrying(): void
    {
        $this->update([
            'status' => 'retrying',
            'attempts' => $this->attempts + 1,
        ]);
    }
}
