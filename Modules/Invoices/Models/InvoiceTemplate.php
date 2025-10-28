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
        'column_widths',
        'column_order',
    ];

    protected $casts = [
        'visible_columns' => 'array',
        'is_active' => 'boolean',
        'column_widths' => 'array',
        'column_order' => 'array',
    ];

    /**
     * الأعمدة المتاحة للفواتير
     */
    public static function availableColumns(): array
    {
        return [
            'item_name' => 'اسم الصنف',
            'unit' => 'الوحدة',
            'quantity' => 'الكمية',
            'length' => 'الطول',
            'width' => 'العرض',
            'height' => 'الارتفاع',
            'density' => 'الكثافة',
            'price' => 'السعر',
            'discount' => 'الخصم',
            'sub_value' => 'القيمة',
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

    public function getColumnWidth(string $columnKey): int
    {
        return $this->column_widths[$columnKey] ?? 10; // القيمة الافتراضية 10%
    }

    // أضف دالة للحصول على الأعمدة مرتبة
    public function getOrderedColumns(): array
    {
        if (empty($this->column_order)) {
            return $this->visible_columns ?? [];
        }

        return $this->column_order;
    }
}
