<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'service_invoice_items';
    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected $attributes = [
        'quantity' => 1,
        'discount_percentage' => 0,
        'discount_amount' => 0,
        'tax_percentage' => 0,
        'tax_amount' => 0,
    ];

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->calculateLineTotal();
        });

        static::saved(function ($item) {
            $item->invoice->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->invoice->calculateTotals();
        });
    }

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ServiceInvoice::class, 'service_invoice_id');
    }

    /**
     * Get the service for this item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the service unit for this item.
     */
    public function serviceUnit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class);
    }

    /**
     * Calculate line total
     */
    public function calculateLineTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $this->discount_amount = ($subtotal * $this->discount_percentage) / 100;
        $discountedAmount = $subtotal - $this->discount_amount;
        $this->tax_amount = ($discountedAmount * $this->tax_percentage) / 100;
        $this->line_total = $discountedAmount + $this->tax_amount;
    }

    /**
     * Get formatted line total
     */
    public function getFormattedLineTotalAttribute(): string
    {
        return number_format($this->line_total, 2) . ' ر.س';
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format($this->unit_price, 2) . ' ر.س';
    }

    protected static function newFactory()
    {
        return \Modules\Services\Database\Factories\ServiceInvoiceItemFactory::new();
    }
}
