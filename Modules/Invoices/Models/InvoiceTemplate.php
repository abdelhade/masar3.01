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

    public static function availableColumns(): array
    {
        return [
            'item_name' => 'اسم الصنف',
            'code' => 'كود الصنف',
            'unit' => 'الوحدة',
            'quantity' => 'الكمية',
            'batch_number' => 'رقم الدفعة',      // ✅ جديد
            'expiry_date' => 'تاريخ الصلاحية',   // ✅ جديد
            'length' => 'الطول',
            'width' => 'العرض',
            'height' => 'الارتفاع',
            'density' => 'الكثافة',
            'price' => 'السعر',
            'discount' => 'الخصم',
            'sub_value' => 'القيمة',
        ];
    }


    public static function invoiceTypeNames(): array
    {
        return [
            10 => 'فاتورة مبيعات',
            11 => 'فاتورة مشتريات',
            12 => 'مردود مبيعات',
            13 => 'مردود مشتريات',
            14 => 'أمر بيع',
            15 => 'أمر شراء',
            16 => 'عرض سعر لعميل',
            17 => 'عرض سعر من مورد',
            18 => 'فاتورة توالف',
            19 => 'أمر صرف',
            20 => 'أمر إضافة',
            21 => 'تحويل من مخزن لمخزن',
            22 => 'أمر حجز',
            24 => 'فاتورة خدمة',
            25 => 'طلب احتياج',
        ];
    }

    public static function getInvoiceTypeName(int $typeId): string
    {
        return self::invoiceTypeNames()[$typeId] ?? "نوع غير معروف";
    }

    public function invoiceTypes(): HasMany
    {
        return $this->hasMany(InvoiceTypeTemplate::class, 'template_id');
    }

    public static function getDefaultForType(int $invoiceType): ?self
    {
        return self::whereHas('invoiceTypes', function ($query) use ($invoiceType) {
            $query->where('invoice_type', $invoiceType)
                ->where('is_default', true);
        })
            ->where('is_active', true)
            ->first();
    }

    public static function getForType(int $invoiceType)
    {
        return self::whereHas('invoiceTypes', function ($query) use ($invoiceType) {
            $query->where('invoice_type', $invoiceType);
        })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function hasColumn(string $columnKey): bool
    {
        return in_array($columnKey, $this->visible_columns ?? []);
    }

    public function getColumnWidth(string $columnKey): int
    {
        return $this->column_widths[$columnKey] ?? 10;
    }

    public function getOrderedColumns(): array
    {
        // Define the fixed order that matches the blade file structure
        $canonicalOrder = [
            'item_name',
            'code',
            'unit',
            'quantity',
            'batch_number',
            'expiry_date',
            'length',
            'width',
            'height',
            'density',
            'price',
            'discount',
            'sub_value',
        ];

        $visible = $this->visible_columns ?? [];
        
        // Return visible columns sorted by their position in canonicalOrder
        // Columns not in canonicalOrder will be appended at the end
        return collect($visible)->sortBy(function ($col) use ($canonicalOrder) {
            $index = array_search($col, $canonicalOrder);
            return $index === false ? 999 : $index;
        })->values()->toArray();
    }
}
