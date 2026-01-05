<?php

namespace Modules\Invoices\Services;

class CurrencyNormalizationService
{
    /**
     * معالجة وتحويل بيانات الفاتورة بناءً على العملة
     * @param object $component كائن الـ Livewire الممرر
     * @return array مصفوفة تحتوي على بيانات الهيدر والبنود جاهزة للحفظ
     */
    public function normalize($component)
    {
        // 1. تحديد بيانات العملة وسعر الصرف
        $rate = 1;
        $currencyId = 1; // Fallback ID

        // التحقق من تفعيل تعدد العملات
        if (function_exists('isMultiCurrencyEnabled') && isMultiCurrencyEnabled()) {
            // الأولوية للقيمة اللي جاية من الكومبوننت، ثم الافتراضي
            $rate = isset($component->currency_rate) && $component->currency_rate > 0 ? $component->currency_rate : 1;
            $currencyId = $component->currency_id ?? (function_exists('getDefaultCurrency') ? getDefaultCurrency()->id : 1);
        }

        // 2. تجهيز بيانات الرأس (OperHead Data)
        $headerData = [
            // البيانات الأساسية (بدون تحويل)
            'branch_id'     => $component->branch_id,
            'pro_type'      => $component->type,
            'pro_date'      => $component->pro_date,
            'accural_date'  => $component->accural_date,
            'pro_id'        => $component->pro_id,
            'serial_number' => $component->serial_number,
            'acc1'          => $component->acc1_id,
            'acc2'          => $component->acc2_id,
            'empid'         => $component->emp_id,
            'delivery_id'   => $component->delivery_id,
            'notes'         => $component->notes,
            'info'          => $component->notes,
            'template_id'   => $component->selectedTemplateId ?? null, // إضافة حقل التمبلت الموجود في الموديل

            // بيانات العملة (للتوثيق)
            'currency_id'   => $currencyId,
            'currency_rate' => $rate, // ✅ تم التعديل لتتوافق مع OperHead (بدل exchange_rate)

            // 🔥 الأرقام المالية (يتم تحويلها للعملة الأساسية) 🔥
            'pro_total'     => round(($component->subtotal ?? 0) * $rate, 2),
            'pro_disc'      => round(($component->discount_value ?? 0) * $rate, 2),
            'pro_disc_per'  => $component->discount_percentage ?? 0,
            'pro_plus'      => round(($component->additional_value ?? 0) * $rate, 2),
            'pro_plus_per'  => $component->additional_percentage ?? 0,
            'pro_tax'       => round(($component->vat_value ?? 0) * $rate, 2),
            'pro_tax_per'   => $component->vat_percentage ?? 0,
            'pro_tax_value' => round(($component->withholding_tax_value ?? 0) * $rate, 2),
            'pro_tax_value_per' => $component->withholding_tax_percentage ?? 0,
            'pro_value'     => round(($component->total_after_additional ?? 0) * $rate, 2), // الصافي النهائي بالعملة المحلية
            'paid'          => round(($component->received_from_client ?? 0) * $rate, 2),
        ];

        // 3. تجهيز بيانات البنود (Operation Items)
        $itemsData = [];
        if (isset($component->invoiceItems) && is_array($component->invoiceItems)) {
            foreach ($component->invoiceItems as $item) {
                $price = isset($item['price']) ? (float)$item['price'] : 0;
                $discount = isset($item['discount']) ? (float)$item['discount'] : 0;
                $subValue = isset($item['sub_value']) ? (float)$item['sub_value'] : 0;

                $itemsData[] = [
                    'item_id'    => $item['item_id'],
                    'unit_id'    => $item['unit_id'] ?? null,
                    'qty'        => isset($item['quantity']) ? (float)$item['quantity'] : 0,
                    'store_id'   => $component->acc2_id,
                    'notes'      => $item['notes'] ?? null,
                    'expire_date' => $item['expiry_date'] ?? null,
                    'batch_no'   => $item['batch_number'] ?? null,

                    // 🔥 القيم المالية للصنف (بعد التحويل) 🔥
                    'price'      => round($price * $rate, 2),
                    'discount'   => round($discount * $rate, 2),
                    'value'      => round($subValue * $rate, 2),
                ];
            }
        }

        return [
            'header' => $headerData,
            'items'  => $itemsData
        ];
    }
}
