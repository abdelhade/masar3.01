<?php

namespace Modules\Zatca\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZatcaInvoiceItem extends Model
{
    protected $fillable = [
        'zatca_invoice_id',
        'item_name',
        'quantity',
        'unit_price',
        'vat_rate',
        'vat_amount',
        'total_amount'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ZatcaInvoice::class, 'zatca_invoice_id');
    }
}
