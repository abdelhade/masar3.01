<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'visible_columns',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'visible_columns' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * الأعمدة المتاحة للفواتير
     */
    public static function availableColumns(): array
    {
        return [
            'item_name' => 'اسم الصنف',
            'item_code' => 'كود الصنف',
            'unit' => 'الوحدة',
            'quantity' => 'الكمية',
            'price' => 'السعر',
            'discount' => 'الخصم',
            'sub_value' => 'القيمة الفرعية',
            'notes' => 'ملاحظات',
            'barcode' => 'الباركود',
            'expiry_date' => 'تاريخ الانتهاء',
            'batch_number' => 'رقم الدفعة',
        ];
    }

    /**
     * علاقة أنواع الفواتير
     */
    public function invoiceTypes(): HasMany
    {
        return $this->hasMany(InvoiceTypeTemplate::class, 'template_id');
    }

    /**
     * الحصول على النموذج الافتراضي لنوع فاتورة معين
     */
    public static function getDefaultForType(int $invoiceType): ?self
    {
        return self::whereHas('invoiceTypes', function ($query) use ($invoiceType) {
            $query->where('invoice_type', $invoiceType)
                ->where('is_default', true);
        })
            ->where('is_active', true)
            ->first();
    }

    /**
     * الحصول على جميع النماذج المتاحة لنوع فاتورة معين
     */
    public static function getForType(int $invoiceType)
    {
        return self::whereHas('invoiceTypes', function ($query) use ($invoiceType) {
            $query->where('invoice_type', $invoiceType);
        })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * التحقق من ظهور عمود معين
     */
    public function hasColumn(string $columnKey): bool
    {
        return in_array($columnKey, $this->visible_columns ?? []);
    }
}
