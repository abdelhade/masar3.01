<?php

namespace Modules\Zatca\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZatcaInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'customer_name',
        'customer_vat',
        'customer_address',
        'subtotal',
        'vat_amount',
        'total_amount',
        'currency',
        'invoice_type',
        'xml_content',
        'qr_code',
        'zatca_status',
        'zatca_uuid',
        'zatca_hash',
        'zatca_response'
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'zatca_response' => 'array',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ZatcaInvoiceItem::class);
    }

    public function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return "INV-{$year}-" . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
