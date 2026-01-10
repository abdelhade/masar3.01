<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use App\Models\Item;
use App\Models\OperHead;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Accounts\Models\AccHead;
use Modules\Invoices\Services\Invoice\DetailValueValidator;
use Modules\Invoices\Services\Invoice\DetailValueCalculator;

/**
 * Service for saving and managing invoices with proper discount and additional handling.
 *
 * This service handles invoice creation, modification, and deletion with accurate
 * detail_value calculation including item-level and invoice-level discounts and additions.
 */
class SaveInvoiceService
{
    /**
     * Create a new SaveInvoiceService instance.
     *
     * @param  DetailValueCalculator  $detailValueCalculator  Calculator for detail_value with discounts/additions
     * @param  DetailValueValidator  $detailValueValidator  Validator for calculated detail_value
     */
    public function __construct(
        private readonly DetailValueCalculator $detailValueCalculator,
        private readonly DetailValueValidator $detailValueValidator
    ) {}

    /**
     * Save invoice with accurate detail_value calculation.
     *
     * @param  \Livewire\Component  $component  Invoice component data from Livewire
     * @param  bool  $isEdit  Whether this is an edit operation
     * @return int|false Operation ID on success, false on failure
     *
     * @throws \Exception
     */
    public function saveInvoice(object $component, bool $isEdit = false): int|false
    {

        if (empty($component->invoiceItems)) {
            $component->dispatch('error', title: 'خطا!', text: 'لا يمكن حفظ الفاتورة بدون أصناف.', icon: 'error');

            return false;
        }
        $component->validate([
            'acc1_id' => 'required|exists:acc_head,id',
            'acc2_id' => 'required|exists:acc_head,id',
            'pro_date' => 'required|date',
            'invoiceItems.*.item_id' => 'required|exists:items,id',
            'invoiceItems.*.quantity' => 'required|numeric|min:0.001',
            'invoiceItems.*.price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'additional_percentage' => 'nullable|numeric|min:0|max:100',
            'received_from_client' => 'nullable|numeric|min:0',
            'invoiceItems.*.batch_number' => 'nullable|string|max:100',
            'invoiceItems.*.expiry_date' => 'nullable|date',
        ], [
            'invoiceItems.*.quantity.min' => 'الكمية يجب أن تكون أكبر من الصفر',
            'invoiceItems.*.price.min' => 'السعر يجب أن يكون قيمة موجبة',
            'invoiceItems.*.expiry_date.date' => 'تاريخ الصلاحية غير صحيح',
        ]);

        // ✅ إضافة جديدة: التحقق من تواريخ الصلاحية المنتهية (اختياري)
        $checkExpiredItems = setting('prevent_selling_expired_items', '1') == '1';

        if ($checkExpiredItems && in_array($component->type, [10, 12, 14, 16, 19, 22])) {
            foreach ($component->invoiceItems as $index => $item) {
                if (! empty($item['expiry_date'])) {
                    $expiryDate = \Carbon\Carbon::parse($item['expiry_date']);

                    if ($expiryDate->isPast()) {
                        $itemName = Item::find($item['item_id'])->name;
                        $component->dispatch(
                            'error',
                            title: 'تحذير!',
                            text: "الصنف '{$itemName}' منتهي الصلاحية بتاريخ: {$expiryDate->format('Y-m-d')}",
                            icon: 'warning'
                        );

                        return false;
                    }
                }
            }
        }

        // ✅ التحقق من حد الائتمان للعملاء في فواتير المبيعات فقط (type: 10)
        if ($component->type == 10) {
            $customer = DB::table('acc_head')->where('id', $component->acc1_id)->first();

            if ($customer && isset($customer->debit_limit) && $customer->debit_limit !== null) {
                // حساب الرصيد الحالي للعميل
                $currentBalance = $customer->balance ?? 0;

                // حساب قيمة الفاتورة الجديدة
                $invoiceTotal = $component->total_after_additional ?? 0;

                // حساب المدفوع من العميل
                $receivedFromClient = $component->received_from_client ?? 0;

                // حساب الرصيد بعد الفاتورة
                $balanceAfterInvoice = $currentBalance + ($invoiceTotal - $receivedFromClient);

                // التحقق من تجاوز الحد
                if ($balanceAfterInvoice > $customer->debit_limit) {
                    $component->dispatch(
                        'error',
                        title: 'تجاوز حد الائتمان!',
                        text: sprintf(
                            'تجاوز العميل حد الائتمان المسموح (الحد: %s، الرصيد بعد الفاتورة: %s)',
                            number_format($customer->debit_limit, 3),
                            number_format($balanceAfterInvoice, 3)
                        ),
                        icon: 'error'
                    );

                    return false;
                }
            }
        }

        // التحقق من الكميات المتاحة فقط للمبيعات والصرف
        foreach ($component->invoiceItems as $index => $item) {
            if (in_array($component->type, [10, 12, 18, 19, 21])) {
                // ✅ 1. Get unit factor for the item
                $unitFactor = 1;
                if ($item['unit_id']) {
                    $unitFactor = DB::table('item_units')
                        ->where('item_id', $item['item_id'])
                        ->where('unit_id', $item['unit_id'])
                        ->value('u_val') ?? 1;
                }

                // ✅ 2. Convert input quantity to base units
                $quantityInBaseUnits = $item['quantity'] * $unitFactor;

                // ✅ 3. Get available quantity in base units
                $availableQty = OperationItems::where('item_id', $item['item_id'])
                    ->where('detail_store', $component->type == 21 ? $component->acc1_id : $component->acc2_id)
                    ->selectRaw('SUM(qty_in - qty_out) as total')
                    ->value('total') ?? 0;

                if ($isEdit && $component->operationId) {
                    $previousQty = OperationItems::where('pro_id', $component->operationId)
                        ->where('item_id', $item['item_id'])
                        ->sum('qty_out') ?? 0;
                    $availableQty += $previousQty;
                }

                // استبدل شرط التحقق بهذا الكود:
                $allowNegative = (setting('invoice_allow_negative_quantity') ?? '0') == '1' && $component->type == 10;

                // ✅ 4. Compare base quantities
                if (! $allowNegative && $availableQty < $quantityInBaseUnits) {
                    $itemName = Item::find($item['item_id'])->name;
                    $component->dispatch(
                        'error',
                        title: 'خطا!',
                        text: 'الكمية غير متوفرة للصنف: ' . $itemName . ' (المتاح: ' . $availableQty . ')',
                        icon: 'error'
                    );

                    return false;
                }
            }
        }

        DB::beginTransaction();
        try {
            // ✅ جميع الحسابات تتم في Alpine.js (client-side)
            // القيم المحسوبة تأتي من Alpine.js: subtotal, discount_value, additional_value, total_after_additional
            // SaveInvoiceService يستقبل القيم الجاهزة من Livewire بدون إعادة حساب

            $isJournal = in_array($component->type, [10, 11, 12, 13, 18, 19, 20, 21, 23, 24]) ? 1 : 0;
            $isManager = $isJournal ? 0 : 1;
            $isReceipt = in_array($component->type, [10, 22, 13]);
            $isPayment = in_array($component->type, [11, 12]);

            $operationData = [
                'pro_type' => $component->type,
                'acc1' => $component->acc1_id,
                'acc2' => $component->acc2_id,
                'emp_id' => $component->emp_id,
                'emp2_id' => $component->delivery_id,
                'is_manager' => $isManager,
                'is_journal' => $isJournal,
                'is_stock' => 1,
                'pro_date' => $component->pro_date,
                // op2 may be provided by the create form when converting an existing operation
                'op2' => $component->op2 ?? request()->get('op2') ?? 0,
                'pro_value' => $component->total_after_additional,
                'fat_net' => $component->total_after_additional,
                'price_list' => $component->selectedPriceType,
                'accural_date' => $component->accural_date,
                'pro_serial' => $component->serial_number,
                'fat_disc_per' => $component->discount_percentage,
                'fat_disc' => $component->discount_value,
                'fat_plus_per' => $component->additional_percentage,
                'fat_plus' => $component->additional_value,
                'fat_total' => $component->subtotal,
                'info' => $component->notes,
                'status' => ($component->type == 14) ? ($component->status ?? 0) : 0,
                'acc_fund' => $component->cash_box_id ?: 0,
                'paid_from_client' => $component->received_from_client,
                'vat_percentage' => $component->vat_percentage ?? 0,
                'vat_value' => $component->vat_value ?? 0,
                'withholding_tax_percentage' => $component->withholding_tax_percentage ?? 0,
                'withholding_tax_value' => $component->withholding_tax_value ?? 0,
                'user' => Auth::id(),
                'branch_id' => $component->branch_id,
                'template_id' => $component->selectedTemplateId ?? null,
            ];

            // تحديث الفاتورة الحالية أو إنشاء جديدة
            if ($isEdit && $component->operationId) {
                $operation = OperHead::with('operationItems')->findOrFail($component->operationId);

                // حفظ معلومات الفاتورة القديمة قبل الحذف
                $oldOperationDate = $operation->pro_date;
                $oldItemIds = $operation->operationItems()
                    ->where('is_stock', 1)
                    ->pluck('item_id')
                    ->unique()
                    ->toArray();

                // حذف الأسطر القديمة التي كانت تحذف السجلات
                // $this->deleteRelatedRecords($operation->id);
                $operationData['pro_id'] = $operation->pro_id;
                $operation->update($operationData);

                // ✅ تحديث القيود (Delta Sync)
                $this->syncJournalEntries($operation, $component);
            } else {
                $operationData['pro_id'] = $component->pro_id;
                $operation = OperHead::create($operationData);

                if (! empty($operationData['op2'])) {
                    $parentId = $operationData['op2'];
                    $parent = OperHead::find($parentId);

                    if ($parent) {
                        $operation->parent_id = $parentId;
                        $operation->origin_id = $parent->origin_id ?: $parentId;

                        // ✅ تحديد workflow_state حسب النوع
                        $operation->workflow_state = $this->getWorkflowStateByType($operation->pro_type);
                        $operation->save();

                        // ✅ تسجيل الانتقال
                        $this->recordTransition(
                            $parentId,
                            $operation->id,
                            $parent->workflow_state,
                            $operation->workflow_state,
                            Auth::id(),
                            'convert_to_' . $operation->pro_type,
                            $component->branch_id
                        );

                        // ✅ تحديث حالة الـ parent
                        $parent->update([
                            'workflow_state' => $this->getWorkflowStateByType($operation->pro_type),
                            'is_locked' => 1, // قفل المستند الأصلي
                        ]);

                        // ✅ تحديث الـ root (أمر الاحتياج الأصلي)
                        $rootId = $parent->origin_id ?: $parent->id;
                        $root = OperHead::find($rootId);
                        if ($root && $root->id != $parent->id) {
                            $root->update([
                                'workflow_state' => $this->getWorkflowStateByType($operation->pro_type),
                                'is_locked' => 1, // قفل المستند الأصلي
                            ]);

                            // ✅ تسجيل انتقال إضافي للـ root
                            $this->recordTransition(
                                $rootId,
                                $operation->id,
                                $root->workflow_state,
                                $this->getWorkflowStateByType($operation->pro_type),
                                Auth::id(),
                                'root_update_to_' . $operation->pro_type,
                                $component->branch_id
                            );
                        }
                    }
                } else {
                    // ✅ إذا كان مستند جديد بدون parent، نحدث الـ workflow_state مباشرة
                    $operation->workflow_state = $this->getWorkflowStateByType($operation->pro_type);
                    $operation->save();
                }

                // إنشاء القيود للفواتير الجديدة
                $this->createJournalEntries($component, $operation);
            }

            // ✅ Calculate accurate detail_value for all items (Requirements 4.1, 4.2, 4.3)
            // Prepare invoice data for calculator including taxes
            $invoiceData = [
                'fat_disc' => $component->discount_value ?? 0,
                'fat_disc_per' => $component->discount_percentage ?? 0,
                'fat_plus' => $component->additional_value ?? 0,
                'fat_plus_per' => $component->additional_percentage ?? 0,
                'vat_value' => $component->vat_value ?? 0,
                'vat_percentage' => $component->vat_percentage ?? 0,
                'withholding_tax_value' => $component->withholding_tax_value ?? 0,
                'withholding_tax_percentage' => $component->withholding_tax_percentage ?? 0,
            ];

            // Calculate detail_value for all items with distributed invoice discounts/additions/taxes
            $calculatedItems = $this->calculateItemDetailValues($component->invoiceItems, $invoiceData);

            // ✅ استخدام syncInvoiceItems بدلاً من الحذف والإضافة
            if ($isEdit && $component->operationId) {
                $this->syncInvoiceItems($operation, $calculatedItems, $component);
            } else {
                // إضافة عناصر الفاتورة الجديدة
                $this->insertNewItems($operation, $calculatedItems, $component);
            }

            // ✅ Recalculate Manufacturing Chain if needed
            if (in_array($component->type, [11, 12, 20])) {
                $itemIds = array_unique(array_column($calculatedItems, 'item_id'));
                \Modules\Invoices\Services\RecalculationServiceHelper::recalculateManufacturingChain(
                    $itemIds,
                    $operation->pro_date
                );
            }

            // ✅ إعادة حساب average_cost والأرباح للعمليات اللاحقة (Ripple Effect)
            if ($isEdit && isset($oldItemIds) && isset($oldOperationDate)) {
                try {
                    if (in_array($component->type, [11, 12, 20, 59])) {
                        // recalculateAverageCost handles the "future" operations
                        \Modules\Invoices\Services\RecalculationServiceHelper::recalculateAverageCost($oldItemIds, $oldOperationDate);
                    }

                    if (! empty($oldItemIds)) {
                        \Modules\Invoices\Services\RecalculationServiceHelper::recalculateProfitsAndJournals(
                            $oldItemIds,
                            $oldOperationDate,
                            null,
                            null
                        );
                    }
                } catch (\Exception $e) {
                    return false;
                }
            } elseif (! $isEdit && in_array($component->type, [11, 12, 20, 59])) {
                try {
                    // For new items, we use the new items list (which we can derive from calculatedItems)
                    $newItemIds = array_unique(array_column($calculatedItems, 'item_id'));

                    if (! empty($newItemIds)) {
                        \Modules\Invoices\Services\RecalculationServiceHelper::recalculateAverageCost($newItemIds, $component->pro_date);

                        \Modules\Invoices\Services\RecalculationServiceHelper::recalculateProfitsAndJournals(
                            $newItemIds,
                            $component->pro_date,
                            $operation->id,
                            $operation->created_at?->format('Y-m-d H:i:s')
                        );
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }

            DB::commit();

            $component->dispatch(
                'swal',
                title: 'تم الحفظ!',
                text: $isEdit ? 'تم تحديث الفاتورة بنجاح.' : 'تم حفظ الفاتورة بنجاح.',
                icon: 'success'
            );

            return $operation->id;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('خطأ أثناء حفظ الفاتورة: ' . $e->getMessage());
            logger()->error($e->getTraceAsString());
            $component->dispatch(
                'error',
                title: 'خطأ!',
                text: 'فشل في حفظ الفاتورة: ' . $e->getMessage(),
                icon: 'error'
            );

            return false;
        }
    }


    /**
     * Calculate and validate detail_value for all invoice items.
     *
     * This method calculates the accurate detail_value for each item including:
     * - Item-level discounts and additions
     * - Proportionally distributed invoice-level discounts and additions
     *
     * @param  array  $items  Invoice items from component
     * @param  array  $invoiceData  Invoice-level data (fat_disc, fat_plus, etc.)
     * @return array Items with calculated and validated detail_value
     *
     * @throws \InvalidArgumentException if validation fails
     */
    private function calculateItemDetailValues(array $items, array $invoiceData): array
    {
        try {
            // Get all level settings from global helpers/settings
            $levels = [
                'discount_level' => (string) setting('discount_level', 'invoice_level'),
                'additional_level' => (string) setting('additional_level', 'invoice_level'),
                'vat_level' => (string) getVatLevel(),
                'withholding_tax_level' => (string) getWithholdingTaxLevel(),
            ];

            // Merge levels into invoice data for calculator/validator
            $invoiceDataWithLevels = \array_merge($invoiceData, $levels);

            // Transform items to match calculator expected format
            // Map Livewire field names to calculator expected names
            $transformedItems = array_map(function ($item) {
                return [
                    'item_price' => $item['price'] ?? 0,
                    'quantity' => $item['quantity'] ?? 0,
                    'item_discount' => $item['discount'] ?? 0,
                    'additional' => $item['additional'] ?? 0,
                    'item_vat_percentage' => $item['item_vat_percentage'] ?? 0,
                    'item_vat_value' => $item['item_vat_value'] ?? 0,
                    'item_withholding_tax_percentage' => $item['item_withholding_tax_percentage'] ?? 0,
                    'item_withholding_tax_value' => $item['item_withholding_tax_value'] ?? 0,
                ];
            }, $items);

            // Calculate invoice subtotal from all items
            $invoiceSubtotal = $this->detailValueCalculator->calculateInvoiceSubtotal($transformedItems, $levels);

            $calculatedItems = [];
            foreach ($items as $index => $item) {
                // Use the transformed item data for calculation
                $itemData = $transformedItems[$index];

                // 2. Validate levels and modes
                $this->detailValueValidator->validateLevels($itemData, $invoiceDataWithLevels);

                // 3. Calculate detail_value and breakdown
                $calculation = $this->detailValueCalculator->calculate(
                    $itemData,
                    $invoiceDataWithLevels,
                    $invoiceSubtotal
                );

                // 4. Validate final detail_value (Enhanced Multi-Level Validation)
                $this->detailValueValidator->validate(
                    $calculation['detail_value'],
                    (float) ($item['sub_value'] ?? 0),
                    $itemData,
                    $calculation
                );

                // Add calculated detail_value to item
                $item['calculated_detail_value'] = $calculation['detail_value'];
                $item['calculation_breakdown'] = $calculation;

                $calculatedItems[] = $item;
            }

            // Level 5: Final Cross-Verification of Invoice Totals
            $this->detailValueValidator->validateInvoiceTotals($calculatedItems, $invoiceData, $invoiceSubtotal);

            return $calculatedItems;
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {

            throw new \RuntimeException('Failed to calculate detail values: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete an invoice and trigger necessary recalculations
     *
     * @param  int  $operationId  The operation ID to delete
     * @return bool Success status
     *
     * @throws \Exception
     */
    public function deleteInvoice(int $operationId): bool
    {
        DB::beginTransaction();
        try {
            $operation = OperHead::with('operationItems')->findOrFail($operationId);

            // حفظ معلومات الفاتورة قبل الحذف
            $operationType = $operation->pro_type;
            $operationDate = $operation->pro_date;
            $itemIds = $operation->operationItems()
                ->where('is_stock', 1)
                ->pluck('item_id')
                ->unique()
                ->toArray();

            // حذف السجلات المرتبطة
            $this->deleteRelatedRecords($operationId);

            // حذف الفاتورة نفسها
            $operation->delete();

            DB::commit();

            // إعادة حساب average_cost والأرباح بعد الحذف
            if (in_array($operationType, [11, 12, 20, 59]) && ! empty($itemIds)) {
                try {
                    // إعادة حساب average_cost من تاريخ الفاتورة المحذوفة
                    RecalculationServiceHelper::recalculateAverageCost(
                        $itemIds,
                        $operationDate,
                        false, // forceQueue
                        true   // isDelete = true (recalculate from all operations)
                    );

                    // ✅ إعادة حساب سلسلة التصنيع إذا كانت فاتورة مشتريات (Requirements 16.1, 16.2)
                    if (in_array($operationType, [11, 12, 20])) {

                        RecalculationServiceHelper::recalculateManufacturingChain(
                            $itemIds,
                            $operationDate
                        );
                    }

                    // إعادة حساب الأرباح والقيود
                    RecalculationServiceHelper::recalculateProfitsAndJournals(
                        $itemIds,
                        $operationDate,
                        null, // لا نستثني أي فاتورة
                        null
                    );
                } catch (\Exception $e) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    private function getWorkflowStateByType($proType)
    {
        $states = [
            25 => 1, // طلب احتياج → submitted
            17 => 2, // عرض سعر من مورد → quoted
            15 => 3, // أمر شراء → purchase_order
            11 => 4, // فاتورة شراء → invoiced
            19 => 5, // إذن صرف → transferred
        ];

        return $states[$proType] ?? 0;
    }

    private function updateAverageCost($itemId, $quantity, $subValue, $currentCost, $unitId, $discountValue = 0, $subtotal = 0)
    {
        // 1. حساب الرصيد السابق بالوحدة الأساسية
        $oldQtyInBase = OperationItems::where('operation_items.item_id', $itemId)
            ->where('operation_items.is_stock', 1)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        // 2. الحصول على معامل التحويل للوحدة الحالية
        $currentUnitFactor = DB::table('item_units')
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->value('u_val') ?? 1;

        // 3. تحويل الكمية الحالية للوحدة الأساسية
        $quantityInBase = $quantity * $currentUnitFactor;

        // 4. حساب الكمية الجديدة بالوحدة الأساسية
        $newQtyInBase = $oldQtyInBase + $quantityInBase;

        // 5. تحديد القيمة المستخدمة في حساب التكلفة حسب طريقة معالجة الخصم
        $purchaseDiscountMethod = setting('purchase_discount_method', '2');
        $valueForCost = $subValue; // القيمة الافتراضية (قبل الخصم)

        if ($purchaseDiscountMethod == '1') {
            // الأوبشن 1: الخصم يُخصم من التكلفة
            // نحسب التكلفة بعد خصم نسبة الخصم من قيمة الصنف
            if ($subtotal > 0 && $discountValue > 0) {
                $itemDiscountRatio = $subValue / $subtotal; // نسبة هذا الصنف من الإجمالي
                $itemDiscount = $discountValue * $itemDiscountRatio; // خصم هذا الصنف
                $valueForCost = $subValue - $itemDiscount; // القيمة بعد الخصم
            }
        } else {
            // الأوبشن 2: الخصم كإيراد منفصل - التكلفة قبل الخصم (الافتراضي)
            $valueForCost = $subValue;
        }

        // 6. حساب التكلفة المتوسطة الجديدة
        if ($oldQtyInBase == 0 && $currentCost == 0) {
            $newCost = $quantityInBase > 0 ? $valueForCost / $quantityInBase : 0;
        } else {
            $oldValue = $oldQtyInBase * $currentCost;
            $totalValue = $oldValue + $valueForCost;
            $newCost = $newQtyInBase > 0 ? $totalValue / $newQtyInBase : $currentCost;
        }

        Item::where('id', $itemId)->update(['average_cost' => $newCost]);

        return $newCost;
    }

    private function deleteRelatedRecords($operationId)
    {
        // logger()->info('حذف البيانات المرتبطة بالفاتورة رقم: ' . $operationId);

        // حذف عناصر الفاتورة
        OperationItems::where('pro_id', $operationId)->delete();

        // حذف القيود المحاسبية
        $journalIds = JournalHead::where('op_id', $operationId)->pluck('journal_id');
        if ($journalIds->count() > 0) {
            JournalDetail::whereIn('journal_id', $journalIds)->delete();
            JournalHead::where('op_id', $operationId)->delete();
        }

        // حذف سندات القبض/الدفع المرتبطة
        $vouchers = OperHead::where('op2', $operationId)->get();
        foreach ($vouchers as $voucher) {
            $voucherJournalIds = JournalHead::where('op_id', $voucher->id)->pluck('journal_id');
            if ($voucherJournalIds->count() > 0) {
                JournalDetail::whereIn('journal_id', $voucherJournalIds)->delete();
                JournalHead::where('op_id', $voucher->id)->delete();
            }
            $voucher->delete();
        }
    }

    private function createJournalEntries($component, $operation)
    {
        $journalId = JournalHead::max('journal_id') + 1;
        $debit = $credit = null;

        // تحديد الحسابات المدينة والدائنة حسب نوع الفاتورة
        switch ($component->type) {
            case 10:
                $debit = $component->acc1_id;
                $credit = 47;
                break; // مبيعات
            case 11:
                $debit = $component->acc2_id;
                $credit = $component->acc1_id;
                break; // مشتريات
            case 12:
                $debit = 48;
                $credit = $component->acc1_id;
                break; // مردود مبيعات
            case 13:
                $debit = $component->acc1_id;
                $credit = $component->acc2_id;
                break; // مردود مشتريات
            case 18:
                $debit = $component->acc1_id;
                $credit = $component->acc2_id;
                break; // توالف
            case 19:
                $debit = $component->acc1_id;
                $credit = $component->acc2_id;
                break; // صرف
            case 20:
                $debit = $component->acc2_id;
                $credit = $component->acc1_id;
                break; // إضافة
            case 21:
                $debit = $component->acc2_id;  // المخزن الذي استلم البضاعة (مدين)
                $credit = $component->acc1_id; // المخزن الذي أرسل البضاعة (دائن)
                break; // تحويل
            case 24:
                $debit = $component->acc1_id;  // المصروفات المختارة (مدين)
                $credit = $component->acc2_id; // المورد (دائن)
                break; // فاتورة خدمه
        }

        // إنشاء رأس القيد
        JournalHead::create([
            'journal_id' => $journalId,
            'total' => $component->total_after_additional,
            'op2' => $operation->id,
            'op_id' => $operation->id,
            'pro_type' => $component->type,
            'date' => $component->pro_date,
            'details' => $component->notes,
            'user' => Auth::id(),
            'branch_id' => $component->branch_id,
        ]);

        // الطرف المدين
        if ($debit) {
            $debitAmount = $component->total_after_additional;

            // للمشتريات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if (in_array($component->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    // كلاهما يُضاف/يُخصم من التكلفة
                    $debitAmount = $component->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي للتكلفة
                    $debitAmount = $component->subtotal + $component->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    // الخصم للتكلفة، الإضافي منفصل
                    $debitAmount = $component->total_after_additional - $component->additional_value;
                } else {
                    // كلاهما منفصل
                    $debitAmount = $component->subtotal;
                }
            }

            // للمبيعات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if ($component->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    // كلاهما في القيد الأساسي
                    $debitAmount = $component->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي في الأساسي
                    $debitAmount = $component->subtotal + $component->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    // الخصم في الأساسي، الإضافي منفصل
                    $debitAmount = $component->subtotal - $component->discount_value;
                } else {
                    // كلاهما منفصل
                    $debitAmount = $component->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $debit,
                'debit' => $debitAmount,
                'credit' => 0,
                'type' => 1,
                'info' => $component->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);
        }

        // الطرف الدائن
        if ($credit) {
            $creditAmount = $component->total_after_additional;

            // للمشتريات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if (in_array($component->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    // كلاهما يُضاف/يُخصم من التكلفة
                    $creditAmount = $component->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي للتكلفة
                    $creditAmount = $component->subtotal + $component->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    // الخصم للتكلفة، الإضافي منفصل
                    $creditAmount = $component->total_after_additional - $component->additional_value;
                } else {
                    // كلاهما منفصل
                    $creditAmount = $component->subtotal;
                }
            }

            // للمبيعات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if ($component->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    // كلاهما في القيد الأساسي
                    $creditAmount = $component->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي في الأساسي
                    $creditAmount = $component->subtotal + $component->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    // الخصم في الأساسي، الإضافي منفصل
                    $creditAmount = $component->subtotal - $component->discount_value;
                } else {
                    // كلاهما منفصل
                    $creditAmount = $component->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $credit,
                'debit' => 0,
                'credit' => $creditAmount,
                'type' => 1,
                'info' => $component->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);
        }

        // قيد تكلفة البضاعة المباعة للمبيعات
        if (in_array($component->type, [10, 12, 19])) {
            $this->createCostOfGoodsJournal($component, $operation);
        }
        // قيد الخصم المسموح به للمبيعات
        if ($component->type == 10 && $component->discount_value > 0) {
            $salesDiscountMethod = setting('sales_discount_method', '1');

            if ($salesDiscountMethod == '1') {
                // الطريقة الحالية: من ح/ خصم مسموح به إلى ح/ المبيعات
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49, // حساب خصم مسموح به (Discount Allowed)
                    'debit' => $component->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 47, // حساب المبيعات
                    'debit' => 0,
                    'credit' => $component->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            } else {
                // الطريقة الثانية: قيد عكسي - من ح/ خصم مسموح به إلى ح/ العميل
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49, // حساب خصم مسموح به (Discount Allowed)
                    'debit' => $component->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // العميل
                    'debit' => 0,
                    'credit' => $component->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }

        // قيد الخصم المكتسب للمشتريات
        if (in_array($component->type, [11, 20]) && $component->discount_value > 0) {
            $purchaseDiscountMethod = setting('purchase_discount_method', '2');

            if ($purchaseDiscountMethod == '1') {
                // الأوبشن 1: الخصم يُخصم من التكلفة - لا يوجد قيد منفصل للخصم
                // لا نعمل أي قيد هنا
            } else {
                // الأوبشن 2: الخصم كإيراد منفصل (قيد عكسي) - الافتراضي
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // المورد (مدين)
                    'debit' => $component->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مكتسب - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 54, // حساب خصم مكتسب (Discount Received)
                    'debit' => 0,
                    'credit' => $component->discount_value,
                    'type' => 1,
                    'info' => 'خصم مكتسب - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }

        // قيد الإضافي للمشتريات
        if (in_array($component->type, [11, 20]) && $component->additional_value > 0) {
            $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

            if ($purchaseAdditionalMethod == '2') {
                // الأوبشن 2: كمصروف منفصل (قيد عكسي)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69, // حساب الإضافات (مدين)
                    'debit' => $component->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // المورد (دائن)
                    'debit' => 0,
                    'credit' => $component->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
            // الأوبشن 1: يُضاف للتكلفة (الحالي) - القيد الحالي موجود بالفعل
        }

        // قيد الإضافي للمبيعات
        if ($component->type == 10 && $component->additional_value > 0) {
            $salesAdditionalMethod = setting('sales_additional_method', '1');

            if ($salesAdditionalMethod == '2') {
                // الأوبشن 2: قيد منفصل للإضافي
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // العميل (مدين)
                    'debit' => $component->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69, // حساب الإضافات (دائن)
                    'debit' => 0,
                    'credit' => $component->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
            // الأوبشن 1: يُضاف للإيراد (الحالي) - القيد الحالي موجود بالفعل
        }

        // قيد ضريبة القيمة المضافة (إذا كانت مفعلة)
        if (isVatEnabled() && $component->vat_value > 0) {
            if ($component->type == 10) {
                // مبيعات: الضريبة من العميل
                // الحصول على كود الحساب من الإعدادات
                $vatSalesAccountCode = setting('vat_sales_account_code', '21040101');
                $vatSalesAccountId = $this->getAccountIdByCode($vatSalesAccountCode);

                if (! $vatSalesAccountId) {
                    return; // تخطي القيد إذا لم يتم العثور على الحساب
                }

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // العميل (مدين)
                    'debit' => $component->vat_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $vatSalesAccountId, // حساب ضريبة القيمة المضافة (دائن)
                    'debit' => 0,
                    'credit' => $component->vat_value,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            } elseif (in_array($component->type, [11, 20])) {
                // مشتريات: الضريبة للمورد
                // الحصول على كود الحساب من الإعدادات
                $vatPurchaseAccountCode = setting('vat_purchase_account_code', '21040102');
                $vatPurchaseAccountId = $this->getAccountIdByCode($vatPurchaseAccountCode);

                if (! $vatPurchaseAccountId) {
                    return; // تخطي القيد إذا لم يتم العثور على الحساب
                }

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $vatPurchaseAccountId, // حساب ضريبة مدفوعة (مدين)
                    'debit' => $component->vat_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // المورد (دائن)
                    'debit' => 0,
                    'credit' => $component->vat_value,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }

        // قيد الخصم من المنبع (Withholding Tax) - إذا كانت مفعلة
        if (isWithholdingTaxEnabled() && $component->withholding_tax_value > 0) {
            // الحصول على كود الحساب من الإعدادات
            $withholdingTaxAccountCode = setting('withholding_tax_account_code', '21040103');
            $withholdingTaxAccountId = $this->getAccountIdByCode($withholdingTaxAccountCode);

            if (! $withholdingTaxAccountId) {
                return; // تخطي القيد إذا لم يتم العثور على الحساب
            }

            if ($component->type == 10) {
                // مبيعات: الخصم من المنبع يُخصم من العميل
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $withholdingTaxAccountId, // حساب خصم من المنبع (مدين)
                    'debit' => $component->withholding_tax_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم من المنبع - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // العميل (دائن)
                    'debit' => 0,
                    'credit' => $component->withholding_tax_value,
                    'type' => 1,
                    'info' => 'خصم من المنبع - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            } elseif (in_array($component->type, [11, 20])) {
                // مشتريات: الخصم من المنبع يُخصم من المورد
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id, // المورد (مدين)
                    'debit' => $component->withholding_tax_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم من المنبع - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $withholdingTaxAccountId, // حساب خصم من المنبع (دائن)
                    'debit' => 0,
                    'credit' => $component->withholding_tax_value,
                    'type' => 1,
                    'info' => 'خصم من المنبع - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }
    }

    /**
     * Record an operation transition between two operhead records for audit and workflow tracking.
     */
    private function recordTransition(?int $fromId, ?int $toId, ?int $fromState, ?int $toState, ?int $userId, string $action, ?int $branchId = null): void
    {
        if (! $fromId || ! $toId) {
            return;
        }

        try {
            DB::table('operation_transitions')->insert([
                'from_operhead_id' => $fromId,
                'to_operhead_id' => $toId,
                'from_state' => $fromState ?? 0,
                'to_state' => $toState ?? 0,
                'user_id' => $userId,
                'action' => $action,
                'notes' => null,
                'created_at' => now(),
                'branch_id' => $branchId,
            ]);
        } catch (\Exception) {
            return;
        }
    }

    private function createCostOfGoodsJournal($component, $operation)
    {
        $costJournalId = JournalHead::max('journal_id') + 1;
        $costAllSales = $component->total_after_additional - $operation->profit - $component->additional_value;

        if ($costAllSales > 0) {
            JournalHead::create([
                'journal_id' => $costJournalId,
                'total' => $costAllSales,
                'op2' => $operation->id,
                'op_id' => $operation->id,
                'pro_type' => $component->type,
                'date' => $component->pro_date,
                'details' => 'قيد تكلفة البضاعة - ' . $component->notes,
                'user' => Auth::id(),
                'branch_id' => $component->branch_id,
            ]);

            JournalDetail::create([
                'journal_id' => $costJournalId,
                'account_id' => 16, // حساب تكلفة البضاعة المباعة
                'debit' => $costAllSales,
                'credit' => 0,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);

            JournalDetail::create([
                'journal_id' => $costJournalId,
                'account_id' => $component->acc2_id, // حساب المخزن
                'debit' => 0,
                'credit' => $costAllSales,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);
        }
    }

    /**
     * Get account ID by account code.
     *
     * @param  string  $accountCode  The account code to search for
     * @return int|null The account ID or null if not found
     */
    private function getAccountIdByCode(string $accountCode): ?int
    {
        return AccHead::where('code', $accountCode)->value('id');
    }

    private function createVoucher($component, $operation, $isReceipt, $isPayment)
    {
        $voucherValue = $component->received_from_client ?? $component->total_after_additional;
        $cashBoxId = is_numeric($component->cash_box_id) && $component->cash_box_id > 0
            ? (int) $component->cash_box_id
            : null;

        if (! $cashBoxId) {
            return; // لا يمكن إنشاء سند بدون صندوق
        }

        if ($isReceipt) {
            $proType = 1;
            $debitAccount = $cashBoxId;
            $creditAccount = $component->acc1_id;
            $voucherType = 'قبض';
        } elseif ($isPayment) {
            $proType = 2;
            $debitAccount = $component->acc1_id;
            $creditAccount = $cashBoxId;
            $voucherType = 'دفع';
        } else {
            return;
        }

        // إنشاء السند
        $voucher = OperHead::create([
            'pro_id' => $operation->pro_id,
            'pro_type' => $proType,
            'acc1' => $component->acc1_id,
            'acc2' => $cashBoxId,
            'pro_value' => $voucherValue,
            'pro_date' => $component->pro_date,
            'info' => 'سند ' . $voucherType . ' آلي مرتبط بعملية رقم ' . $operation->id,
            'op2' => $operation->id,
            'is_journal' => 1,
            'is_stock' => 0,
            'user' => Auth::id(),
            'branch_id' => $component->branch_id,
        ]);

        // إنشاء قيد السند
        $voucherJournalId = JournalHead::max('journal_id') + 1;

        JournalHead::create([
            'journal_id' => $voucherJournalId,
            'total' => $voucherValue,
            'op_id' => $voucher->id,
            'op2' => $operation->id,
            'pro_type' => $proType,
            'date' => $component->pro_date,
            'details' => 'قيد سند ' . $voucherType . ' آلي',
            'user' => Auth::id(),
            'branch_id' => $component->branch_id,
        ]);

        JournalDetail::create([
            'journal_id' => $voucherJournalId,
            'account_id' => $debitAccount,
            'debit' => $voucherValue,
            'credit' => 0,
            'type' => 1,
            'info' => 'سند ' . $voucherType,
            'op_id' => $voucher->id,
            'isdeleted' => 0,
            'branch_id' => $component->branch_id,
        ]);

        JournalDetail::create([
            'journal_id' => $voucherJournalId,
            'account_id' => $creditAccount,
            'debit' => 0,
            'credit' => $voucherValue,
            'type' => 1,
            'info' => 'سند ' . $voucherType,
            'op_id' => $voucher->id,
            'isdeleted' => 0,
            'branch_id' => $component->branch_id,
        ]);
    }
    /**
     * Delta Sync logic for Invoice Items
     * Updates existing items, Inserts new ones, Deletes removed ones.
     */
    private function syncInvoiceItems($operation, $calculatedItems, $component)
    {
        $existingItems = OperationItems::where('pro_id', $operation->id)->get()->keyBy('id');
        $processedItemIds = [];

        foreach ($calculatedItems as $invoiceItem) {
            $itemId = $invoiceItem['item_id'];
            $quantity = $invoiceItem['quantity'];
            $unitId = $invoiceItem['unit_id'];
            $price = $invoiceItem['price'];
            $subValue = $invoiceItem['calculated_detail_value'];
            $discount = $invoiceItem['discount'] ?? 0;
            $itemCost = Item::where('id', $itemId)->value('average_cost');
            $batchNumber = $invoiceItem['batch_number'] ?? null;
            $expiryDate = $invoiceItem['expiry_date'] ?? null;

            // Check if this item is an existing one being updated (has 'operation_item_id')
            $operationItemId = $invoiceItem['operation_item_id'] ?? null;

            // Fetch u_val explicitly
            $uVal = DB::table('item_units')
                ->where('item_id', $itemId)
                ->where('unit_id', $unitId)
                ->value('u_val') ?? 1;

            if ($operationItemId && $existingItems->has($operationItemId)) {
                // UPDATE existing item
                $opItem = $existingItems->get($operationItemId);

                // Convert Display Quantity to Base Quantity
                $baseQty = $quantity * $uVal; // Base Qty = Display Qty × Unit Value

                $updateData = [
                    'item_id' => $itemId,
                    'unit_id' => $unitId,
                    'unit_value' => $uVal, // ✅ Save unit_value
                    'qty_in' => in_array($component->type, [11, 12, 13, 20]) ? $baseQty : 0, // ✅ Base Quantity
                    'qty_out' => in_array($component->type, [10, 19]) ? $baseQty : 0, // ✅ Base Quantity
                    'fat_quantity' => $quantity, // ✅ Display Quantity
                    'item_price' => $uVal > 0 ? ($price / $uVal) : $price, // ✅ Base Price (للوحدة الأساسية)
                    'fat_price' => $price, // ✅ Display Price (لوحدة العرض)
                    'item_discount' => $discount,
                    'detail_value' => ($price * $quantity)
                        - $discount
                        + ($invoiceItem['additional'] ?? 0)
                        + ($invoiceItem['item_vat_value'] ?? 0)
                        - ($invoiceItem['item_withholding_tax_value'] ?? 0),
                    'notes' => $invoiceItem['notes'] ?? '',
                    'item_cost' => $itemCost,
                ];

                if ($component->type == 21) {
                    // Transfer logic fallback (Delete/Create for simplicity in type 21)
                    $opItem->delete();
                    $this->createSingleItem($operation, $invoiceItem, $component, $itemCost, $batchNumber, $expiryDate);
                } else {
                    $opItem->update($updateData);
                    // Batch info is updated via $updateData if columns exist in OperationItems
                }

                $processedItemIds[] = $operationItemId;
            } else {
                // INSERT new item
                $this->createSingleItem($operation, $invoiceItem, $component, $itemCost, $batchNumber, $expiryDate);
            }

            // ✅ Update Average Cost (Critical for 4.5, 6.1, 9.1 etc)
            if (in_array($component->type, [11, 12, 13, 20])) { // Purchase, Return, Add
                $this->updateAverageCost(
                    $itemId,
                    $quantity,
                    $subValue,
                    $itemCost,
                    $unitId,
                    $component->discount_value ?? 0,
                    $component->subtotal ?? 0
                );
            }
        }

        // DELETE removed items
        $itemsToDelete = $existingItems->except($processedItemIds);
        foreach ($itemsToDelete as $itemToDelete) {
            $itemToDelete->delete();
        }
    }

    /**
     * Helper to insert new items (used by both Create and Sync)
     */
    private function insertNewItems($operation, $calculatedItems, $component)
    {
        foreach ($calculatedItems as $invoiceItem) {
            $itemId = $invoiceItem['item_id'];
            $itemCost = Item::where('id', $itemId)->value('average_cost');
            $batchNumber = $invoiceItem['batch_number'] ?? null;
            $expiryDate = $invoiceItem['expiry_date'] ?? null;

            $this->createSingleItem($operation, $invoiceItem, $component, $itemCost, $batchNumber, $expiryDate);
        }
    }

    private function createSingleItem($operation, $invoiceItem, $component, $itemCost, $batchNumber, $expiryDate)
    {
        $itemId = $invoiceItem['item_id'];
        $quantity = $invoiceItem['quantity'];
        $unitId = $invoiceItem['unit_id'];
        $price = $invoiceItem['price'];
        $subValue = $invoiceItem['calculated_detail_value'];
        $discount = $invoiceItem['discount'] ?? 0;

        // Fetch u_val explicitly
        $uVal = DB::table('item_units')
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->value('u_val') ?? 1;

        // Convert Display Quantity to Base Quantity
        $baseQty = $quantity * $uVal; // Base Qty = Display Qty × Unit Value

        if ($component->type == 21) {
            // 1. خصم الكمية من المخزن المحوَّل منه (المخزن الأول acc1)
            OperationItems::create([
                'pro_tybe' => $component->type,
                'detail_store' => $component->acc1_id,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'qty_in' => 0,
                'qty_out' => $baseQty, // ✅ Base Quantity
                'item_price' => $uVal > 0 ? ($price / $uVal) : $price, // ✅ Base Price
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'detail_value' => $subValue,
                'is_stock' => 1,
                'notes' => $invoiceItem['notes'] ?? '',
                'item_cost' => $itemCost,
                'fat_unit_id' => $unitId
            ]);

            // 2. إضافة الكمية للمخزن المحوَّل إليه (المخزن الثاني acc2)
            OperationItems::create([
                'pro_tybe' => $component->type,
                'detail_store' => $component->acc2_id,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'qty_in' => $baseQty, // ✅ Base Quantity
                'qty_out' => 0,
                'item_price' => $uVal > 0 ? ($price / $uVal) : $price, // ✅ Base Price
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'detail_value' => $subValue,
                'is_stock' => 1,
                'fat_quantity' => $quantity,
                'item_cost' => $itemCost,
                'fat_unit_id' => $unitId
            ]);
        } elseif ($component->type == 24) {
            OperationItems::create([
                'pro_tybe' => $component->type,
                'detail_store' => 0,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'qty_in' => $baseQty, // ✅ Base Quantity
                'qty_out' => 0,
                'item_price' => $uVal > 0 ? ($price / $uVal) : $price, // ✅ Base Price
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'detail_value' => $subValue,
                'is_stock' => 0,
                'notes' => $invoiceItem['notes'] ?? '',
                'item_cost' => $itemCost,
                'fat_unit_id' => $unitId
            ]);
        } else {
            $qtyIn = in_array($component->type, [11, 12, 13, 20]) ? $baseQty : 0; // ✅ Base Quantity
            $qtyOut = in_array($component->type, [10, 19]) ? $baseQty : 0; // ✅ Base Quantity
            $detailStore = in_array($component->type, [10, 11, 12, 13, 19, 20]) ? $component->acc2_id : 0;

            $opItem = OperationItems::create([
                'pro_tybe' => $component->type,
                'detail_store' => $detailStore,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'item_price' => $uVal > 0 ? ($price / $uVal) : $price, // ✅ Base Price
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'detail_value' => ($price * $quantity)
                    - $discount
                    + ($invoiceItem['additional'] ?? 0)
                    + ($invoiceItem['item_vat_value'] ?? 0)
                    - ($invoiceItem['item_withholding_tax_value'] ?? 0),
                'is_stock' => 1,
                'notes' => $invoiceItem['notes'] ?? '',
                'item_cost' => $itemCost,
                'fat_unit_id' => $unitId
            ]);
        }
    }

    /**
     * Updates Journal Entries without deleting the Journal Header.
     */
    private function syncJournalEntries($operation, $component)
    {
        $journalHead = JournalHead::where('op_id', $operation->id)->first();

        if ($journalHead) {
            $journalId = $journalHead->journal_id;

            $journalHead->update([
                'total' => $component->total_after_additional,
                'date' => $component->pro_date,
                'details' => $component->notes,
                'branch_id' => $component->branch_id,
                'user' => Auth::id()
            ]);

            JournalDetail::where('journal_id', $journalId)->delete();
            $this->generateJournalDetails($journalId, $operation, $component);
        } else {
            $this->createJournalEntries($component, $operation);
        }
    }

    private function generateJournalDetails($journalId, $operation, $component)
    {
        $debit = $credit = null;

        switch ($component->type) {
            case 10:
                $debit = $component->acc1_id;
                $credit = 47;
                break;
            case 11:
                $debit = $component->acc2_id;
                $credit = $component->acc1_id;
                break; // Corrected logic: Purchase: Store is Debit, Supplier is Credit. Wait. Default logic has switch case 11 as debit=acc2(store), credit=acc1(supplier). BUT in createJournalEntries switch 11 is debit=acc2, credit=acc1. Let's verified.
            case 12:
                $debit = 48;
                $credit = $component->acc1_id;
                break;
            case 13:
                $debit = $component->acc1_id;
                $credit = $component->acc2_id;
                break;
            case 18:
                $debit = $component->acc1_id;
                $credit = $component->acc2_id;
                break;
            case 19:
                $debit = $component->acc1_id;
                $credit = $component->acc2_id;
                break;
            case 20:
                $debit = $component->acc2_id;
                $credit = $component->acc1_id;
                break;
            case 21:
                $debit = $component->acc2_id;
                $credit = $component->acc1_id;
                break;
            case 24:
                $debit = $component->acc1_id;
                $credit = $component->acc2_id;
                break;
        }

        if ($debit) {
            $debitAmount = $component->total_after_additional;
            if (in_array($component->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    $debitAmount = $component->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    $debitAmount = $component->subtotal + $component->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    $debitAmount = $component->total_after_additional - $component->additional_value;
                } else {
                    $debitAmount = $component->subtotal;
                }
            }
            if ($component->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    $debitAmount = $component->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    $debitAmount = $component->subtotal + $component->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    $debitAmount = $component->subtotal - $component->discount_value;
                } else {
                    $debitAmount = $component->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $debit,
                'debit' => $debitAmount,
                'credit' => 0,
                'type' => 1,
                'info' => $component->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);
        }

        if ($credit) {
            $creditAmount = $component->total_after_additional;
            if (in_array($component->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    $creditAmount = $component->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    $creditAmount = $component->subtotal + $component->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    $creditAmount = $component->total_after_additional - $component->additional_value;
                } else {
                    $creditAmount = $component->subtotal;
                }
            }
            if ($component->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    $creditAmount = $component->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    $creditAmount = $component->subtotal + $component->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    $creditAmount = $component->subtotal - $component->discount_value;
                } else {
                    $creditAmount = $component->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $credit,
                'debit' => 0,
                'credit' => $creditAmount,
                'type' => 1,
                'info' => $component->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);
        }

        if (in_array($component->type, [10, 12, 19])) {
            $this->syncCostOfGoodsJournal($component, $operation);
        }

        if ($component->type == 10 && $component->discount_value > 0) {
            $salesDiscountMethod = setting('sales_discount_method', '1');
            if ($salesDiscountMethod == '1') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49,
                    'debit' => $component->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 47,
                    'debit' => 0,
                    'credit' => $component->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            } else {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49,
                    'debit' => $component->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id,
                    'debit' => 0,
                    'credit' => $component->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }

        if (in_array($component->type, [11, 20]) && $component->discount_value > 0) {
            $purchaseDiscountMethod = setting('purchase_discount_method', '2');
            if ($purchaseDiscountMethod != '1') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id,
                    'debit' => $component->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مكتسب - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 54,
                    'debit' => 0,
                    'credit' => $component->discount_value,
                    'type' => 1,
                    'info' => 'خصم مكتسب - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }

        if (in_array($component->type, [11, 20]) && $component->additional_value > 0) {
            $purchaseAdditionalMethod = setting('purchase_additional_method', '1');
            if ($purchaseAdditionalMethod == '2') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69,
                    'debit' => $component->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id,
                    'debit' => 0,
                    'credit' => $component->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }

        if ($component->type == 10 && $component->additional_value > 0) {
            $salesAdditionalMethod = setting('sales_additional_method', '1');
            if ($salesAdditionalMethod == '2') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->acc1_id,
                    'debit' => $component->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69,
                    'debit' => 0,
                    'credit' => $component->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - ' . $component->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $component->branch_id,
                ]);
            }
        }

        if (isVatEnabled() && $component->vat_value > 0) {
            if ($component->type == 10) {
                $vatSalesAccountCode = setting('vat_sales_account_code', '21040101');
                $vatSalesAccountId = $this->getAccountIdByCode($vatSalesAccountCode);
                if ($vatSalesAccountId) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $component->acc1_id,
                        'debit' => $component->vat_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $vatSalesAccountId,
                        'debit' => 0,
                        'credit' => $component->vat_value,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                }
            } elseif (in_array($component->type, [11, 20])) {
                $vatPurchaseAccountCode = setting('vat_purchase_account_code', '21040102');
                $vatPurchaseAccountId = $this->getAccountIdByCode($vatPurchaseAccountCode);
                if ($vatPurchaseAccountId) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $vatPurchaseAccountId,
                        'debit' => $component->vat_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $component->acc1_id,
                        'debit' => 0,
                        'credit' => $component->vat_value,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                }
            }
        }

        if (isWithholdingTaxEnabled() && $component->withholding_tax_value > 0) {
            $withholdingTaxAccountCode = setting('withholding_tax_account_code', '21040103');
            $withholdingTaxAccountId = $this->getAccountIdByCode($withholdingTaxAccountCode);

            if ($withholdingTaxAccountId) {
                if ($component->type == 10) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $withholdingTaxAccountId,
                        'debit' => $component->withholding_tax_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'خصم من المنبع - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $component->acc1_id,
                        'debit' => 0,
                        'credit' => $component->withholding_tax_value,
                        'type' => 1,
                        'info' => 'خصم من المنبع - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                } elseif (in_array($component->type, [11, 20])) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $component->acc1_id,
                        'debit' => $component->withholding_tax_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'خصم من المنبع - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $withholdingTaxAccountId,
                        'debit' => 0,
                        'credit' => $component->withholding_tax_value,
                        'type' => 1,
                        'info' => 'خصم من المنبع - ' . $component->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $component->branch_id,
                    ]);
                }
            }
        }
    }

    private function syncCostOfGoodsJournal($component, $operation)
    {
        $costJournal = JournalHead::where('op_id', $operation->id)
            ->where('journal_id', '>', 0) // Basic check
            ->whereRaw("details LIKE '%قيد تكلفة البضاعة%'")
            ->first();

        if ($costJournal) {
            $costAllSales = $component->total_after_additional - $operation->profit - $component->additional_value;
            if ($costAllSales <= 0) {
                JournalDetail::where('journal_id', $costJournal->journal_id)->delete();
                $costJournal->delete();
                return;
            }

            $costJournal->update([
                'total' => $costAllSales,
                'date' => $component->pro_date,
                'user' => Auth::id()
            ]);

            JournalDetail::where('journal_id', $costJournal->journal_id)->delete();

            JournalDetail::create([
                'journal_id' => $costJournal->journal_id,
                'account_id' => 16,
                'debit' => $costAllSales,
                'credit' => 0,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);
            JournalDetail::create([
                'journal_id' => $costJournal->journal_id,
                'account_id' => $component->acc2_id,
                'debit' => 0,
                'credit' => $costAllSales,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $component->branch_id,
            ]);
        } else {
            $this->createCostOfGoodsJournal($component, $operation);
        }
    }
}
