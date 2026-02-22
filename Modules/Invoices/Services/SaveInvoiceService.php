<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperationItems;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\Invoices\Services\Invoice\DetailValueCalculator;
use Modules\Invoices\Services\Invoice\DetailValueValidator;

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
     * @param  object|array  $data  Invoice data from Livewire component or Request array
     * @param  bool  $isEdit  Whether this is an edit operation
     * @return int|false Operation ID on success, false on failure
     *
     * @throws \Exception
     */
    public function saveInvoice(object|array $data, bool $isEdit = false): int|false
    {
        // Convert array to object for easier access if needed, or use a helper
        $data = is_array($data) ? (object) $data : $data;

        // Handle field name mapping (if sent as 'items' from Request but expected as 'invoiceItems')
        $items = property_exists($data, 'invoiceItems') ? $data->invoiceItems : ($data->items ?? []);

        if (empty($items)) {
            throw new \Exception('لا يمكن حفظ الفاتورة بدون أصناف.');
        }

        // ✅ إضافة جديدة: التحقق من تواريخ الصلاحية المنتهية (اختياري)
        $checkExpiredItems = setting('prevent_selling_expired_items', '1') == '1';

        if ($checkExpiredItems && in_array($data->type, [10, 12, 14, 16, 19, 22])) {
            foreach ($items as $index => $item) {
                if (! empty($item['expiry_date'])) {
                    $expiryDate = \Carbon\Carbon::parse($item['expiry_date']);

                    if ($expiryDate->isPast()) {
                        $itemName = Item::find($item['item_id'])->name;
                        throw new \Exception("الصنف '{$itemName}' منتهي الصلاحية بتاريخ: {$expiryDate->format('Y-m-d')}");
                    }
                }
            }
        }

        // ✅ التحقق من حد الائتمان للعملاء في فواتير المبيعات فقط (type: 10)
        if ($data->type == 10) {
            $customer = DB::table('acc_head')->where('id', $data->acc1_id)->first();

            if ($customer && isset($customer->debit_limit) && $customer->debit_limit !== null) {
                // حساب الرصيد الحالي للعميل
                $currentBalance = $customer->balance ?? 0;

                // حساب قيمة الفاتورة الجديدة
                $invoiceTotal = $data->total_after_additional ?? 0;

                // حساب المدفوع من العميل
                $receivedFromClient = $data->received_from_client ?? 0;

                // حساب الرصيد بعد الفاتورة
                $balanceAfterInvoice = $currentBalance + ($invoiceTotal - $receivedFromClient);

                // التحقق من تجاوز الحد
                if ($balanceAfterInvoice > $customer->debit_limit) {
                    throw new \Exception(sprintf(
                        'تجاوز العميل حد الائتمان المسموح (الحد: %s، الرصيد بعد الفاتورة: %s)',
                        number_format((float) $customer->debit_limit, 3),
                        number_format((float) $balanceAfterInvoice, 3)
                    ));
                }
            }
        }

        // ✅ Critical: Check if invoice is posted (Security)
        $isEdit = (isset($data->operationId) && $data->operationId) || $isEdit;
        $operationId = $data->operationId ?? null;

        if ($isEdit && $operationId) {
            $existingOperation = OperHead::find($operationId);
            if ($existingOperation && ($existingOperation->is_posted ?? false)) {
                throw new \Exception('لا يمكن تعديل فاتورة مرحلة (posted).');
            }
        }

        // ✅ High: Validate currency_rate > 0
        $currencyRate = $data->currency_rate ?? 1;
        if ($currencyRate <= 0) {
            throw new \Exception('سعر صرف العملة يجب أن يكون أكبر من صفر.');
        }

        foreach ($items as $index => $item) {
            if (in_array($data->type, [10, 12, 18, 19, 21])) {
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
                    ->where('detail_store', $data->type == 21 ? $data->acc1_id : $data->acc2_id)
                    ->selectRaw('SUM(qty_in - qty_out) as total')
                    ->value('total') ?? 0;

                if ($isEdit && $operationId) {
                    $previousQty = OperationItems::where('pro_id', $operationId)
                        ->where('item_id', $item['item_id'])
                        ->sum('qty_out') ?? 0;
                    $availableQty += $previousQty;
                }

                // استبدل شرط التحقق بهذا الكود:
                $allowNegative = (setting('invoice_allow_negative_quantity') ?? '0') == '1' && $data->type == 10;

                // ✅ 4. Compare base quantities
                if (! $allowNegative && $availableQty < $quantityInBaseUnits) {
                    $itemName = Item::find($item['item_id'])->name;
                    throw new \Exception('الكمية غير متوفرة للصنف: '.$itemName.' (المتاح: '.$availableQty.')');
                }
            }
        }

        DB::beginTransaction();
        try {
            // ✅ جميع الحسابات تتم في Alpine.js (client-side)
            // القيم المحسوبة تأتي من Alpine.js: subtotal, discount_value, additional_value, total_after_additional
            // SaveInvoiceService يستقبل القيم الجاهزة من Livewire بدون إعادة حساب

            $isJournal = in_array($data->type, [10, 11, 12, 13, 18, 19, 20, 21, 23, 24]) ? 1 : 0;
            $isManager = $isJournal ? 0 : 1;
            $isReceipt = in_array($data->type, [10, 22, 13]);
            $isPayment = in_array($data->type, [11, 12]);

            $currencyId = $data->currency_id;
            $currencyRate = $data->currency_rate;

            $operationData = [
                'pro_type' => $data->type,
                'acc1' => $data->acc1_id,
                'acc2' => $data->acc2_id,
                'emp_id' => $data->emp_id,
                'emp2_id' => $data->delivery_id,
                'is_manager' => $isManager,
                'is_journal' => $isJournal,
                'is_stock' => 1,
                'pro_date' => $data->pro_date,
                // op2 may be provided by the create form when converting an existing operation
                'op2' => $data->op2 ?? request()->get('op2') ?? 0,
                'pro_value' => $data->total_after_additional * $currencyRate,
                'fat_net' => $data->total_after_additional * $currencyRate,
                'price_list' => $data->selectedPriceType ?? null,
                'accural_date' => $data->accural_date,
                'pro_serial' => $data->serial_number,
                'fat_disc_per' => $data->discount_percentage,
                'fat_disc' => $data->discount_value,
                'fat_plus_per' => $data->additional_percentage,
                'fat_plus' => $data->additional_value,
                'fat_total' => $data->subtotal,
                'info' => $data->notes,
                'status' => ($data->type == 14) ? ($data->status ?? 0) : 0,
                'acc_fund' => $data->cash_box_id ?: 0,
                'paid_from_client' => $data->received_from_client,
                'vat_percentage' => $data->vat_percentage ?? 0,
                'vat_value' => $data->vat_value ?? 0,
                'withholding_tax_percentage' => $data->withholding_tax_percentage ?? 0,
                'withholding_tax_value' => $data->withholding_tax_value ?? 0,
                'user' => Auth::id(),
                'branch_id' => $data->branch_id,
                'currency_id' => $currencyId,
                'currency_rate' => $currencyRate,
                'acc1_before' => $data->currentBalance ?? 0,
                'acc1_after' => $data->balanceAfterInvoice ?? 0,
                'template_id' => $data->template_id ?? $data->selectedTemplateId ?? null,
                'currency_id' => $data->currency_id ?? null, // ✅ Save currency ID
                'currency_rate' => $currencyRate, // ✅ Save currency rate (validated)
            ];

            // تحديث الفاتورة الحالية أو إنشاء جديدة
            if ($isEdit && $operationId) {
                $operation = OperHead::with('operationItems')->findOrFail($operationId);

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
                $this->syncJournalEntries($operation, $data);
            } else {
                $operationData['pro_id'] = $data->pro_id ?? 0;
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
                            'convert_to_'.$operation->pro_type,
                            $data->branch_id
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
                                'root_update_to_'.$operation->pro_type,
                                $data->branch_id
                            );
                        }
                    }
                } else {
                    // ✅ إذا كان مستند جديد بدون parent، نحدث الـ workflow_state مباشرة
                    $operation->workflow_state = $this->getWorkflowStateByType($operation->pro_type);
                    $operation->save();
                }
                // إنشاء القيود للفواتير الجديدة
                $this->createJournalEntries($data, $operation);
            }

            // ✅ Calculate accurate detail_value for all items (Requirements 4.1, 4.2, 4.3)
            // Prepare invoice data for calculator including taxes
            $invoiceData = [
                'fat_disc' => $data->discount_value ?? 0,
                'fat_disc_per' => $data->discount_percentage ?? 0,
                'fat_plus' => $data->additional_value ?? 0,
                'fat_plus_per' => $data->additional_percentage ?? 0,
                'vat_value' => $data->vat_value ?? 0,
                'vat_percentage' => $data->vat_percentage ?? 0,
                'withholding_tax_value' => $data->withholding_tax_value ?? 0,
                'withholding_tax_percentage' => $data->withholding_tax_percentage ?? 0,
            ];

            // Calculate detail_value for all items with distributed invoice discounts/additions/taxes
            $calculatedItems = $this->calculateItemDetailValues($items, $invoiceData);

            // ✅ استخدام syncInvoiceItems بدلاً من الحذف والإضافة
            if ($isEdit && $operationId) {
                $this->syncInvoiceItems($operation, $calculatedItems, $data);
            } else {
                // إضافة عناصر الفاتورة الجديدة
                $this->insertNewItems($operation, $calculatedItems, $data);
            }

            // ✅ Calculate fat_cost and profit based on ACTUAL operation_items (Requirement: Use saved items)
            $operation->refresh(); // Ensure pro_value and relation are updated
            $invoiceTotalCost = 0;
            if (in_array($data->type, [11, 13, 20])) {
                $invoiceTotalCost = $operation->pro_value;
            } else {
                foreach ($operation->operationItems as $opItem) {
                    $invoiceTotalCost += ($opItem->qty_in + $opItem->qty_out) * $opItem->cost_price;
                }
            }

            $profit = $operation->pro_value - $invoiceTotalCost;
            if ($data->type == 12) {
                $profit = -$profit;
            }

            $operation->update([
                'fat_cost' => $invoiceTotalCost,
                'profit' => $profit,
            ]);

            // ✅ Recalculate Manufacturing Chain if needed
            if (in_array($data->type, [11, 12, 20])) {
                $itemIds = array_unique(array_column($calculatedItems, 'item_id'));
                \Modules\Invoices\Services\RecalculationServiceHelper::recalculateManufacturingChain(
                    $itemIds,
                    $operation->pro_date
                );
            }

            // ✅ إنشاء أو تحديث سند القبض/الدفع
            $receivedFromClient = $data->received_from_client ?? 0;
            $cashBoxId = $data->cash_box_id ?? null;
            
            logger()->info('💰 Voucher Creation Check', [
                'receivedFromClient' => $receivedFromClient,
                'cashBoxId' => $cashBoxId,
                'isReceipt' => $isReceipt,
                'isPayment' => $isPayment,
                'operation_type' => $data->type,
                'isEdit' => $isEdit,
                'operationId' => $operationId ?? null,
            ]);
            
            if ($isEdit && $operationId) {
                // في حالة التعديل، نستخدم syncVoucher
                logger()->info('📝 Calling syncVoucher for edit');
                $this->syncVoucher($operation, $data);
            } else {
                // في حالة الإنشاء، نستخدم createVoucher
                if ($receivedFromClient > 0 && $cashBoxId) {
                    logger()->info('✅ Creating new voucher');
                    $this->createVoucher($data, $operation, $isReceipt, $isPayment);
                } else {
                    logger()->warning('⚠️ Voucher not created - conditions not met', [
                        'receivedFromClient' => $receivedFromClient,
                        'cashBoxId' => $cashBoxId,
                    ]);
                }
            }

            // ✅ إعادة حساب average_cost والأرباح للعمليات اللاحقة (Ripple Effect)
            if ($isEdit && isset($oldItemIds) && isset($oldOperationDate)) {
                try {
                    if (in_array($data->type, [11, 12, 20, 59])) {
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
                    // ✅ Critical: Log recalculation errors instead of silently failing
                    logger()->error('Failed to recalculate average cost and profits after edit', [
                        'operation_id' => $operation->id ?? null,
                        'item_ids' => $oldItemIds ?? [],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't return false - let the invoice save, but log the error
                }
            } elseif (! $isEdit && in_array($data->type, [11, 12, 20, 59])) {
                try {
                    // For new items, we use the new items list (which we can derive from calculatedItems)
                    $newItemIds = array_unique(array_column($calculatedItems, 'item_id'));

                    if (! empty($newItemIds)) {
                        \Modules\Invoices\Services\RecalculationServiceHelper::recalculateAverageCost($newItemIds, $data->pro_date);

                        \Modules\Invoices\Services\RecalculationServiceHelper::recalculateProfitsAndJournals(
                            $newItemIds,
                            $data->pro_date,
                            $operation->id,
                            $operation->created_at?->format('Y-m-d H:i:s')
                        );
                    }
                } catch (\Exception $e) {
                    // ✅ Critical: Log recalculation errors instead of silently failing
                    logger()->error('Failed to recalculate average cost and profits after new invoice', [
                        'operation_id' => $operation->id ?? null,
                        'item_ids' => $newItemIds ?? [],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            DB::commit();

            return $operation->id;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('خطأ أثناء حفظ الفاتورة: '.$e->getMessage());
            logger()->error($e->getTraceAsString());

            throw $e;
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

            throw new \RuntimeException('Failed to calculate detail values: '.$e->getMessage(), 0, $e);
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

    private function createJournalEntries($data, $operation)
    {
        // ✅ High: Fix Race Condition in journal_id using lockForUpdate
        $journalId = DB::transaction(function () {
            // Use lockForUpdate to prevent concurrent access
            $maxJournal = JournalHead::lockForUpdate()->orderBy('journal_id', 'desc')->first();
            $maxJournalId = $maxJournal ? $maxJournal->journal_id : 0;

            return $maxJournalId + 1;
        }, 5); // 5 attempts

        $debit = $credit = null;

        // تحديد الحسابات المدينة والدائنة حسب نوع الفاتورة
        switch ($data->type) {
            case 10:
                $debit = $data->acc1_id;
                $credit = 47;
                break; // مبيعات
            case 11:
                $debit = $data->acc2_id;
                $credit = $data->acc1_id;
                break; // مشتريات
            case 12:
                $debit = 48;
                $credit = $data->acc1_id;
                break; // مردود مبيعات
            case 13:
                $debit = $data->acc1_id;
                $credit = $data->acc2_id;
                break; // مردود مشتريات
            case 18:
                $debit = $data->acc1_id;
                $credit = $data->acc2_id;
                break; // توالف
            case 19:
                $debit = $data->acc1_id;
                $credit = $data->acc2_id;
                break; // صرف
            case 20:
                $debit = $data->acc2_id;
                $credit = $data->acc1_id;
                break; // إضافة
            case 21:
                $debit = $data->acc2_id;  // المخزن الذي استلم البضاعة (مدين)
                $credit = $data->acc1_id; // المخزن الذي أرسل البضاعة (دائن)
                break; // تحويل
            case 24:
                $debit = $data->acc1_id;  // المصروفات المختارة (مدين)
                $credit = $data->acc2_id; // المورد (دائن)
                break; // فاتورة خدمه
        }

        // إنشاء رأس القيد
        JournalHead::create([
            'journal_id' => $journalId,
            'total' => $data->total_after_additional,
            'op2' => $operation->id,
            'op_id' => $operation->id,
            'pro_type' => $data->type,
            'date' => $data->pro_date,
            'details' => $data->notes,
            'user' => Auth::id(),
            'branch_id' => $data->branch_id,
        ]);

        // الطرف المدين
        if ($debit) {
            $debitAmount = $data->total_after_additional;

            // للمشتريات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if (in_array($data->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    // كلاهما يُضاف/يُخصم من التكلفة
                    $debitAmount = $data->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي للتكلفة
                    $debitAmount = $data->subtotal + $data->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    // الخصم للتكلفة، الإضافي منفصل
                    $debitAmount = $data->total_after_additional - $data->additional_value;
                } else {
                    // كلاهما منفصل
                    $debitAmount = $data->subtotal;
                }
            }

            // للمبيعات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if ($data->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    // كلاهما في القيد الأساسي
                    $debitAmount = $data->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي في الأساسي
                    $debitAmount = $data->subtotal + $data->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    // الخصم في الأساسي، الإضافي منفصل
                    $debitAmount = $data->subtotal - $data->discount_value;
                } else {
                    // كلاهما منفصل
                    $debitAmount = $data->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $debit,
                'debit' => $debitAmount,
                'credit' => 0,
                'type' => 1,
                'info' => $data->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $data->branch_id,
            ]);
        }

        // الطرف الدائن
        if ($credit) {
            $creditAmount = $data->total_after_additional;

            // للمشتريات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if (in_array($data->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    // كلاهما يُضاف/يُخصم من التكلفة
                    $creditAmount = $data->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي للتكلفة
                    $creditAmount = $data->subtotal + $data->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    // الخصم للتكلفة، الإضافي منفصل
                    $creditAmount = $data->total_after_additional - $data->additional_value;
                } else {
                    // كلاهما منفصل
                    $creditAmount = $data->subtotal;
                }
            }

            // للمبيعات: نحدد المبلغ حسب طريقة معالجة الخصم والإضافي
            if ($data->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    // كلاهما في القيد الأساسي
                    $creditAmount = $data->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    // الخصم منفصل، الإضافي في الأساسي
                    $creditAmount = $data->subtotal + $data->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    // الخصم في الأساسي، الإضافي منفصل
                    $creditAmount = $data->subtotal - $data->discount_value;
                } else {
                    // كلاهما منفصل
                    $creditAmount = $data->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $credit,
                'debit' => 0,
                'credit' => $creditAmount,
                'type' => 1,
                'info' => $data->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $data->branch_id,
            ]);
        }

        // قيد تكلفة البضاعة المباعة للمبيعات
        if (in_array($data->type, [10, 12, 19])) {
            $this->createCostOfGoodsJournal($data, $operation);
        }
        // قيد الخصم المسموح به للمبيعات
        if ($data->type == 10 && $data->discount_value > 0) {
            $salesDiscountMethod = setting('sales_discount_method', '1');

            if ($salesDiscountMethod == '1') {
                // الطريقة الحالية: من ح/ خصم مسموح به إلى ح/ المبيعات
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49, // حساب خصم مسموح به (Discount Allowed)
                    'debit' => $data->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 47, // حساب المبيعات
                    'debit' => 0,
                    'credit' => $data->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            } else {
                // الطريقة الثانية: قيد عكسي - من ح/ خصم مسموح به إلى ح/ العميل
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49, // حساب خصم مسموح به (Discount Allowed)
                    'debit' => $data->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // العميل
                    'debit' => 0,
                    'credit' => $data->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
        }

        // قيد الخصم المكتسب للمشتريات
        if (in_array($data->type, [11, 20]) && $data->discount_value > 0) {
            $purchaseDiscountMethod = setting('purchase_discount_method', '2');

            if ($purchaseDiscountMethod == '1') {
                // الأوبشن 1: الخصم يُخصم من التكلفة - لا يوجد قيد منفصل للخصم
                // لا نعمل أي قيد هنا
            } else {
                // الأوبشن 2: الخصم كإيراد منفصل (قيد عكسي) - الافتراضي
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // المورد (مدين)
                    'debit' => $data->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مكتسب - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 54, // حساب خصم مكتسب (Discount Received)
                    'debit' => 0,
                    'credit' => $data->discount_value,
                    'type' => 1,
                    'info' => 'خصم مكتسب - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
        }

        // قيد الإضافي للمشتريات
        if (in_array($data->type, [11, 20]) && $data->additional_value > 0) {
            $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

            if ($purchaseAdditionalMethod == '2') {
                // الأوبشن 2: كمصروف منفصل (قيد عكسي)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69, // حساب الإضافات (مدين)
                    'debit' => $data->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // المورد (دائن)
                    'debit' => 0,
                    'credit' => $data->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
            // الأوبشن 1: يُضاف للتكلفة (الحالي) - القيد الحالي موجود بالفعل
        }

        // قيد الإضافي للمبيعات
        if ($data->type == 10 && $data->additional_value > 0) {
            $salesAdditionalMethod = setting('sales_additional_method', '1');

            if ($salesAdditionalMethod == '2') {
                // الأوبشن 2: قيد منفصل للإضافي
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // العميل (مدين)
                    'debit' => $data->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69, // حساب الإضافات (دائن)
                    'debit' => 0,
                    'credit' => $data->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
            // الأوبشن 1: يُضاف للإيراد (الحالي) - القيد الحالي موجود بالفعل
        }

        // قيد ضريبة القيمة المضافة (إذا كانت مفعلة)
        if (isVatEnabled() && $data->vat_value > 0) {
            if ($data->type == 10) {
                // مبيعات: الضريبة من العميل
                // الحصول على كود الحساب من الإعدادات
                $vatSalesAccountCode = setting('vat_sales_account_code', '21040101');
                $vatSalesAccountId = $this->getAccountIdByCode($vatSalesAccountCode);

                if (! $vatSalesAccountId) {
                    return; // تخطي القيد إذا لم يتم العثور على الحساب
                }

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // العميل (مدين)
                    'debit' => $data->vat_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $vatSalesAccountId, // حساب ضريبة القيمة المضافة (دائن)
                    'debit' => 0,
                    'credit' => $data->vat_value,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            } elseif (in_array($data->type, [11, 20])) {
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
                    'debit' => $data->vat_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // المورد (دائن)
                    'debit' => 0,
                    'credit' => $data->vat_value,
                    'type' => 1,
                    'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
        }

        // قيد الخصم من المنبع (Withholding Tax) - إذا كانت مفعلة
        if (isWithholdingTaxEnabled() && $data->withholding_tax_value > 0) {
            // الحصول على كود الحساب من الإعدادات
            $withholdingTaxAccountCode = setting('withholding_tax_account_code', '21040103');
            $withholdingTaxAccountId = $this->getAccountIdByCode($withholdingTaxAccountCode);

            if (! $withholdingTaxAccountId) {
                return; // تخطي القيد إذا لم يتم العثور على الحساب
            }

            if ($data->type == 10) {
                // مبيعات: الخصم من المنبع يُخصم من العميل
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $withholdingTaxAccountId, // حساب خصم من المنبع (مدين)
                    'debit' => $data->withholding_tax_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم من المنبع - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // العميل (دائن)
                    'debit' => 0,
                    'credit' => $data->withholding_tax_value,
                    'type' => 1,
                    'info' => 'خصم من المنبع - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            } elseif (in_array($data->type, [11, 20])) {
                // مشتريات: الخصم من المنبع يُخصم من المورد
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id, // المورد (مدين)
                    'debit' => $data->withholding_tax_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم من المنبع - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $withholdingTaxAccountId, // حساب خصم من المنبع (دائن)
                    'debit' => 0,
                    'credit' => $data->withholding_tax_value,
                    'type' => 1,
                    'info' => 'خصم من المنبع - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
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

    private function createCostOfGoodsJournal($data, $operation)
    {
        $costJournalId = JournalHead::max('journal_id') + 1;
        $costAllSales = $data->total_after_additional - $operation->profit - $data->additional_value;

        if ($costAllSales > 0) {
            JournalHead::create([
                'journal_id' => $costJournalId,
                'total' => $costAllSales,
                'op2' => $operation->id,
                'op_id' => $operation->id,
                'pro_type' => $data->type,
                'date' => $data->pro_date,
                'details' => 'قيد تكلفة البضاعة - '.$data->notes,
                'user' => Auth::id(),
                'branch_id' => $data->branch_id,
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
                'branch_id' => $data->branch_id,
            ]);

            JournalDetail::create([
                'journal_id' => $costJournalId,
                'account_id' => $data->acc2_id, // حساب المخزن
                'debit' => 0,
                'credit' => $costAllSales,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $data->branch_id,
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

    private function createVoucher($data, $operation, $isReceipt, $isPayment)
    {
        $voucherValue = $data->received_from_client ?? 0;
        $cashBoxId = is_numeric($data->cash_box_id) && $data->cash_box_id > 0
            ? (int) $data->cash_box_id
            : null;

        // ✅ Logging للتأكد من القيم
        logger()->info('createVoucher called', [
            'voucherValue' => $voucherValue,
            'cashBoxId' => $cashBoxId,
            'isReceipt' => $isReceipt,
            'isPayment' => $isPayment,
            'operation_id' => $operation->id,
        ]);

        if (! $cashBoxId) {
            logger()->warning('createVoucher: No cash box ID provided');
            return; // لا يمكن إنشاء سند بدون صندوق
        }

        if ($voucherValue <= 0) {
            logger()->warning('createVoucher: Voucher value is zero or negative', ['value' => $voucherValue]);
            return; // لا يمكن إنشاء سند بقيمة صفر
        }

        if ($isReceipt) {
            $proType = 1;
            $debitAccount = $cashBoxId;
            $creditAccount = $data->acc1_id;
            $voucherType = 'قبض';
        } elseif ($isPayment) {
            $proType = 2;
            $debitAccount = $data->acc1_id;
            $creditAccount = $cashBoxId;
            $voucherType = 'دفع';
        } else {
            logger()->warning('createVoucher: Neither receipt nor payment');
            return;
        }

        // إنشاء السند
        $voucher = OperHead::create([
            'pro_id' => $operation->pro_id,
            'pro_type' => $proType,
            'acc1' => $data->acc1_id,
            'acc2' => $cashBoxId,
            'pro_value' => $voucherValue,
            'pro_date' => $data->pro_date,
            'info' => 'سند '.$voucherType.' آلي مرتبط بعملية رقم '.$operation->id,
            'op2' => $operation->id,
            'is_journal' => 1,
            'is_stock' => 0,
            'user' => Auth::id(),
            'branch_id' => $data->branch_id,
        ]);

        logger()->info('Voucher created successfully', [
            'voucher_id' => $voucher->id,
            'voucher_type' => $voucherType,
            'value' => $voucherValue,
        ]);

        // إنشاء قيد السند
        $voucherJournalId = JournalHead::max('journal_id') + 1;

        JournalHead::create([
            'journal_id' => $voucherJournalId,
            'total' => $voucherValue,
            'op_id' => $voucher->id,
            'op2' => $operation->id,
            'pro_type' => $proType,
            'date' => $data->pro_date,
            'details' => 'قيد سند '.$voucherType.' آلي',
            'user' => Auth::id(),
            'branch_id' => $data->branch_id,
        ]);

        JournalDetail::create([
            'journal_id' => $voucherJournalId,
            'account_id' => $debitAccount,
            'debit' => $voucherValue,
            'credit' => 0,
            'type' => 1,
            'info' => 'سند '.$voucherType,
            'op_id' => $voucher->id,
            'isdeleted' => 0,
            'branch_id' => $data->branch_id,
        ]);

        JournalDetail::create([
            'journal_id' => $voucherJournalId,
            'account_id' => $creditAccount,
            'debit' => 0,
            'credit' => $voucherValue,
            'type' => 1,
            'info' => 'سند '.$voucherType,
            'op_id' => $voucher->id,
            'isdeleted' => 0,
            'branch_id' => $data->branch_id,
        ]);

        logger()->info('Voucher journal entries created successfully', [
            'journal_id' => $voucherJournalId,
            'debit_account' => $debitAccount,
            'credit_account' => $creditAccount,
        ]);
    }

    /**
     * معالجة سند القبض/الدفع في حالة التعديل
     */
    private function syncVoucher($operation, $data)
    {
        $receivedFromClient = $data->received_from_client ?? 0;
        $cashBoxId = $data->cash_box_id ?? null;
        
        // البحث عن السند المرتبط بالفاتورة
        $existingVoucher = OperHead::where('op2', $operation->id)
            ->whereIn('pro_type', [1, 2]) // سند قبض أو دفع
            ->first();

        // تحديد نوع السند
        $isReceipt = in_array($data->type, [10, 22, 13]);
        $isPayment = in_array($data->type, [11, 12]);

        if ($receivedFromClient > 0 && $cashBoxId) {
            // يجب إنشاء أو تحديث السند
            if ($existingVoucher) {
                // تحديث السند الموجود
                $proType = $isReceipt ? 1 : 2;
                $voucherType = $isReceipt ? 'قبض' : 'دفع';
                
                $existingVoucher->update([
                    'pro_type' => $proType,
                    'acc1' => $data->acc1_id,
                    'acc2' => $cashBoxId,
                    'pro_value' => $receivedFromClient,
                    'pro_date' => $data->pro_date,
                    'info' => 'سند '.$voucherType.' آلي مرتبط بعملية رقم '.$operation->id,
                    'user' => Auth::id(),
                    'branch_id' => $data->branch_id,
                ]);

                // تحديث قيد السند
                $voucherJournalHead = JournalHead::where('op_id', $existingVoucher->id)->first();
                if ($voucherJournalHead) {
                    $voucherJournalHead->update([
                        'total' => $receivedFromClient,
                        'date' => $data->pro_date,
                        'details' => 'قيد سند '.$voucherType.' آلي',
                        'user' => Auth::id(),
                        'branch_id' => $data->branch_id,
                    ]);

                    // حذف وإعادة إنشاء تفاصيل القيد
                    JournalDetail::where('journal_id', $voucherJournalHead->journal_id)->delete();

                    $debitAccount = $isReceipt ? $cashBoxId : $data->acc1_id;
                    $creditAccount = $isReceipt ? $data->acc1_id : $cashBoxId;

                    JournalDetail::create([
                        'journal_id' => $voucherJournalHead->journal_id,
                        'account_id' => $debitAccount,
                        'debit' => $receivedFromClient,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'سند '.$voucherType,
                        'op_id' => $existingVoucher->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);

                    JournalDetail::create([
                        'journal_id' => $voucherJournalHead->journal_id,
                        'account_id' => $creditAccount,
                        'debit' => 0,
                        'credit' => $receivedFromClient,
                        'type' => 1,
                        'info' => 'سند '.$voucherType,
                        'op_id' => $existingVoucher->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                }
            } else {
                // إنشاء سند جديد
                $this->createVoucher($data, $operation, $isReceipt, $isPayment);
            }
        } else {
            // حذف السند إذا كان موجوداً ولا يوجد مبلغ مدفوع
            if ($existingVoucher) {
                $voucherJournalIds = JournalHead::where('op_id', $existingVoucher->id)->pluck('journal_id');
                if ($voucherJournalIds->count() > 0) {
                    JournalDetail::whereIn('journal_id', $voucherJournalIds)->delete();
                    JournalHead::where('op_id', $existingVoucher->id)->delete();
                }
                $existingVoucher->delete();
            }
        }
    }

    /**
     * Delta Sync logic for Invoice Items
     * Updates existing items, Inserts new ones, Deletes removed ones.
     */
    private function syncInvoiceItems($operation, $calculatedItems, $data)
    {
        $data = is_array($data) ? (object) $data : $data;
        $existingItems = OperationItems::where('pro_id', $operation->id)->get()->keyBy('id');
        $processedItemIds = [];

        $currencyId = $data->currency_id;
        $currencyRate = (float) ($data->currency_rate ?? 1);

        foreach ($calculatedItems as $invoiceItem) {
            $itemId = $invoiceItem['item_id'];
            $quantity = $invoiceItem['quantity'];
            $unitId = $invoiceItem['unit_id'];
            $price = $invoiceItem['price'];
            $subValue = $invoiceItem['calculated_detail_value'];
            $discount = $invoiceItem['discount'] ?? 0;

            // ✅ Determine cost_price based on invoice type as requested
            // 11: Purchase, 13: Purchase Return, 20: Addition - In these types, the cost is the price we buy at
            // For others: Cost is the item's average cost
            $uVal = DB::table('item_units')
                ->where('item_id', $itemId)
                ->where('unit_id', $unitId)
                ->value('u_val') ?? 1;

            if (in_array($data->type, [11, 13, 20])) {
                $itemCost = (float) ($uVal > 0 ? ($price / $uVal) : $price); // Base Price
            } else {
                $itemCost = (float) (Item::where('id', $itemId)->value('average_cost') ?? 0);
            }

            $batchNumber = $invoiceItem['batch_number'] ?? null;
            $expiryDate = $invoiceItem['expiry_date'] ?? null;

            // Check if this item is an existing one being updated (has 'operation_item_id')
            $operationItemId = $invoiceItem['operation_item_id'] ?? null;

            // Fetch u_val explicitly
            $uVal = DB::table('item_units')
                ->where('item_id', $itemId)
                ->where('unit_id', $unitId)
                ->value('u_val') ?? 1;

            // ✅ Validation: Ensure uVal is positive
            if ($uVal <= 0) {
                throw new \InvalidArgumentException("Invalid unit value (u_val) for item {$itemId}, unit {$unitId}. Value must be greater than 0.");
            }

            if ($operationItemId && $existingItems->has($operationItemId)) {
                // UPDATE existing item
                $opItem = $existingItems->get($operationItemId);

                // Convert Display Quantity to Base Quantity
                $baseQty = $quantity * $uVal; // Base Qty = Display Qty × Unit Value

                $invoiceItemProfit = $this->calculateItemProfit($invoiceItem, $itemCost, $data);

                $updateData = [
                    'item_id' => $itemId,
                    'unit_id' => $unitId,
                    'unit_value' => $uVal, // ✅ Save unit_value
                    'fat_unit_id' => $unitId, // ✅ Save display unit ID
                    'qty_in' => in_array($data->type, [11, 12, 13, 20]) ? $baseQty : 0, // ✅ Base Quantity
                    'qty_out' => in_array($data->type, [10, 19]) ? $baseQty : 0, // ✅ Base Quantity
                    'fat_quantity' => $quantity, // ✅ Display Quantity
                    'item_price' => $uVal > 0 ? (($price / $uVal) * $currencyRate) : ($price * $currencyRate), // ✅ Base Price * Rate
                    'fat_price' => $price, // ✅ Display Price (لوحدة العرض)
                    'item_discount' => $invoiceItem['discount_value'] ?? $invoiceItem['discount'] ?? 0,
                    'item_discount_pre' => $invoiceItem['discount_percentage'] ?? 0, // ✅ Save discount percentage
                    'detail_value' => $subValue, // ✅ Critical: Use calculated_detail_value directly (from DetailValueCalculator)
                    'notes' => $invoiceItem['notes'] ?? '',
                    'cost_price' => $itemCost,
                    'profit' => $invoiceItemProfit,
                    'currency_id' => $currencyId,
                    'currency_rate' => $currencyRate,
                    'item_cost' => $itemCost,
                    'batch_number' => $batchNumber, // ✅ Save batch number
                    'expiry_date' => $expiryDate, // ✅ Save expiry date
                ];

                if ($data->type == 21) {
                    // Transfer logic fallback (Delete/Create for simplicity in type 21)
                    $opItem->delete();
                    $this->createSingleItem($operation, $invoiceItem, $data, $itemCost, $batchNumber, $expiryDate, $currencyId, $currencyRate);
                } else {
                    $opItem->update($updateData);
                }

                $processedItemIds[] = $operationItemId;
            } else {
                // INSERT new item
                $this->createSingleItem($operation, $invoiceItem, $data, $itemCost, $batchNumber, $expiryDate, $currencyId, $currencyRate);
            }

            // ✅ Critical: Removed updateAverageCost from syncInvoiceItems
            // RecalculationServiceHelper::recalculateAverageCost will handle this correctly
            // after the transaction commits (Ripple Effect)
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
    private function insertNewItems($operation, $calculatedItems, $data)
    {
        $data = is_array($data) ? (object) $data : $data;
        $currencyId = $data->currency_id;
        $currencyRate = (float) ($data->currency_rate ?? 1);

        foreach ($calculatedItems as $invoiceItem) {
            $itemId = $invoiceItem['item_id'];
            $unitId = $invoiceItem['unit_id'];
            $price = $invoiceItem['price'];

            $uVal = DB::table('item_units')
                ->where('item_id', $itemId)
                ->where('unit_id', $unitId)
                ->value('u_val') ?? 1;

            if (in_array($data->type, [11, 13, 20])) {
                $itemCost = (float) ($uVal > 0 ? ($price / $uVal) : $price); // Base Price
            } else {
                $itemCost = (float) (Item::where('id', $itemId)->value('average_cost') ?? 0);
            }

            $batchNumber = $invoiceItem['batch_number'] ?? null;
            $expiryDate = $invoiceItem['expiry_date'] ?? null;

            $invoiceItemProfit = $this->calculateItemProfit($invoiceItem, $itemCost, $data);
            $this->createSingleItem($operation, $invoiceItem, $data, $itemCost, $batchNumber, $expiryDate, $currencyId, $currencyRate);
        }
    }

    private function createSingleItem($operation, $invoiceItem, $data, $itemCost, $batchNumber, $expiryDate, $currencyId, $currencyRate)
    {
        $itemId = $invoiceItem['item_id'];
        $quantity = $invoiceItem['quantity'];
        $unitId = $invoiceItem['unit_id'];
        $price = $invoiceItem['price'];
        $subValue = $invoiceItem['calculated_detail_value'];
        $discount = $invoiceItem['discount_value'] ?? $invoiceItem['discount'] ?? 0; // ✅ Use discount_value
        $discountPercentage = $invoiceItem['discount_percentage'] ?? 0; // ✅ Add discount_percentage

        // Fetch u_val explicitly
        $uVal = DB::table('item_units')
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->value('u_val') ?? 1;

        // ✅ Validation: Ensure uVal is positive
        if ($uVal <= 0) {
            throw new \InvalidArgumentException("Invalid unit value (u_val) for item {$itemId}, unit {$unitId}. Value must be greater than 0.");
        }

        // Convert Display Quantity to Base Quantity
        $baseQty = $quantity * $uVal; // Base Qty = Display Qty × Unit Value

        $invoiceItemProfit = $this->calculateItemProfit($invoiceItem, $itemCost, $data);

        if ($data->type == 21) {
            // 1. خصم الكمية من المخزن المحوَّل منه (المخزن الأول acc1)
            OperationItems::create([
                'pro_tybe' => $data->type,
                'detail_store' => $data->acc1_id,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'qty_in' => 0,
                'qty_out' => $baseQty, // ✅ Base Quantity
                'item_price' => $uVal > 0 ? (($price / $uVal) * $currencyRate) : ($price * $currencyRate), // ✅ Base Price * Rate
                'fat_quantity' => $quantity, // ✅ Display Quantity (توحيد الحفظ)
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'item_discount_pre' => $discountPercentage, // ✅ Save discount percentage
                'detail_value' => $subValue,
                'is_stock' => 1,
                'notes' => $invoiceItem['notes'] ?? '',
                'cost_price' => $itemCost,
                'fat_unit_id' => $unitId,
                'profit' => $invoiceItemProfit,
                'currency_id' => $currencyId,
                'currency_rate' => $currencyRate,
                'item_cost' => $itemCost,
                'batch_number' => $batchNumber, // ✅ Save batch number
                'expiry_date' => $expiryDate, // ✅ Save expiry date
            ]);

            // 2. إضافة الكمية للمخزن المحوَّل إليه (المخزن الثاني acc2)
            OperationItems::create([
                'pro_tybe' => $data->type,
                'detail_store' => $data->acc2_id,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'fat_unit_id' => $unitId, // ✅ Save display unit ID
                'qty_in' => $baseQty, // ✅ Base Quantity
                'qty_out' => 0,
                'item_price' => $uVal > 0 ? (($price / $uVal) * $currencyRate) : ($price * $currencyRate), // ✅ Base Price * Rate
                'fat_quantity' => $quantity, // ✅ Display Quantity
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'item_discount_pre' => $discountPercentage, // ✅ Save discount percentage
                'detail_value' => $subValue,
                'is_stock' => 1,
                'fat_quantity' => $quantity,
                'cost_price' => $itemCost,
                'fat_unit_id' => $unitId,
                'profit' => $invoiceItemProfit,
                'currency_id' => $currencyId,
                'currency_rate' => $currencyRate,

                'notes' => $invoiceItem['notes'] ?? '',
                'item_cost' => $itemCost,
                'batch_number' => $batchNumber, // ✅ Save batch number
                'expiry_date' => $expiryDate, // ✅ Save expiry date
            ]);
        } elseif ($data->type == 24) {
            OperationItems::create([
                'pro_tybe' => $data->type,
                'detail_store' => 0,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'qty_in' => $baseQty, // ✅ Base Quantity
                'qty_out' => 0,
                'item_price' => $uVal > 0 ? (($price / $uVal) * $currencyRate) : ($price * $currencyRate), // ✅ Base Price * Rate
                'fat_quantity' => $quantity, // ✅ Display Quantity
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'item_discount_pre' => $discountPercentage, // ✅ Save discount percentage
                'detail_value' => $subValue,
                'is_stock' => 0,
                'notes' => $invoiceItem['notes'] ?? '',
                'cost_price' => $itemCost,
                'fat_unit_id' => $unitId,
                'profit' => $invoiceItemProfit,
                'currency_id' => $currencyId,
                'currency_rate' => $currencyRate,
                'item_cost' => $itemCost,
                'batch_number' => $batchNumber, // ✅ Save batch number
                'expiry_date' => $expiryDate, // ✅ Save expiry date
            ]);
        } else {
            $qtyIn = in_array($data->type, [11, 12, 13, 20]) ? $baseQty : 0; // ✅ Base Quantity
            $qtyOut = in_array($data->type, [10, 19]) ? $baseQty : 0; // ✅ Base Quantity
            $detailStore = in_array($data->type, [10, 11, 12, 13, 19, 20]) ? $data->acc2_id : 0;

            $opItem = OperationItems::create([
                'pro_tybe' => $data->type,
                'detail_store' => $detailStore,
                'pro_id' => $operation->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'unit_value' => $uVal, // ✅ Save unit_value
                'fat_unit_id' => $unitId, // ✅ Save display unit ID
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'item_price' => $uVal > 0 ? (($price / $uVal) * $currencyRate) : ($price * $currencyRate), // ✅ Base Price * Rate
                'fat_quantity' => $quantity, // ✅ Display Quantity
                'fat_price' => $price, // ✅ Display Price
                'item_discount' => $discount,
                'item_discount_pre' => $discountPercentage, // ✅ Save discount percentage
                'detail_value' => $subValue, // ✅ Critical: Use calculated_detail_value directly (from DetailValueCalculator)
                'is_stock' => 1,
                'notes' => $invoiceItem['notes'] ?? '',
                'cost_price' => $itemCost,
                'fat_unit_id' => $unitId,
                'profit' => $invoiceItemProfit,
                'currency_id' => $currencyId,
                'currency_rate' => $currencyRate,

                'item_cost' => $itemCost,
                'batch_number' => $batchNumber, // ✅ Save batch number
                'expiry_date' => $expiryDate, // ✅ Save expiry date
            ]);
        }
    }

    /**
     * ✅ Calculates profit for a single item within an invoice context
     * Uses Formula: (Net Line Value - Proportional Invoice Discount) - (Item Cost * Base Qty)
     */
    private function calculateItemProfit(array $invoiceItem, float $itemCost, $data): float
    {
        $detailValue = ($invoiceItem['price'] * $invoiceItem['quantity']) - ($invoiceItem['discount'] ?? 0);
        $totalInvoiceValue = $data->subtotal ?? 0;
        $invoiceDiscount = $data->discount_value ?? 0;

        // Calculate proportional discount for this line
        $proportionalDiscount = 0;
        if ($invoiceDiscount > 0 && $totalInvoiceValue > 0) {
            $proportionalDiscount = ($detailValue * $invoiceDiscount) / $totalInvoiceValue;
        }

        // Base Qty for cost calculation
        $itemId = $invoiceItem['item_id'];
        $unitId = $invoiceItem['unit_id'];
        $uVal = DB::table('item_units')->where('item_id', $itemId)->where('unit_id', $unitId)->value('u_val') ?? 1;
        $baseQty = $invoiceItem['quantity'] * $uVal;

        // Net Line Value (after both item and invoice discounts)
        $netLineValue = $detailValue - $proportionalDiscount;
        $totalCostForLine = $itemCost * $baseQty;

        $profit = $netLineValue - $totalCostForLine;

        // Handle Sales Returns (Type 12) - Profit should be negative
        if ($data->type == 12) {
            $profit = -abs($profit);
        }

        return (float) $profit;
    }

    /**
     * Updates Journal Entries without deleting the Journal Header.
     */
    private function syncJournalEntries($operation, $data)
    {
        $journalHead = JournalHead::where('op_id', $operation->id)->first();

        if ($journalHead) {
            $journalId = $journalHead->journal_id;

            $journalHead->update([
                'total' => $data->total_after_additional,
                'date' => $data->pro_date,
                'details' => $data->notes,
                'branch_id' => $data->branch_id,
                'user' => Auth::id(),
            ]);

            JournalDetail::where('journal_id', $journalId)->delete();
            $this->generateJournalDetails($journalId, $operation, $data);
        } else {
            $this->createJournalEntries($data, $operation);
        }

        // ✅ معالجة سند القبض/الدفع في حالة التعديل
        $this->syncVoucher($operation, $data);
    }

    private function generateJournalDetails($journalId, $operation, $data)
    {
        $debit = $credit = null;

        switch ($data->type) {
            case 10:
                $debit = $data->acc1_id;
                $credit = 47;
                break;
            case 11:
                $debit = $data->acc2_id;
                $credit = $data->acc1_id;
                break; // Corrected logic: Purchase: Store is Debit, Supplier is Credit. Wait. Default logic has switch case 11 as debit=acc2(store), credit=acc1(supplier). BUT in createJournalEntries switch 11 is debit=acc2, credit=acc1. Let's verified.
            case 12:
                $debit = 48;
                $credit = $data->acc1_id;
                break;
            case 13:
                $debit = $data->acc1_id;
                $credit = $data->acc2_id;
                break;
            case 18:
                $debit = $data->acc1_id;
                $credit = $data->acc2_id;
                break;
            case 19:
                $debit = $data->acc1_id;
                $credit = $data->acc2_id;
                break;
            case 20:
                $debit = $data->acc2_id;
                $credit = $data->acc1_id;
                break;
            case 21:
                $debit = $data->acc2_id;
                $credit = $data->acc1_id;
                break;
            case 24:
                $debit = $data->acc1_id;
                $credit = $data->acc2_id;
                break;
        }

        if ($debit) {
            $debitAmount = $data->total_after_additional;
            if (in_array($data->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    $debitAmount = $data->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    $debitAmount = $data->subtotal + $data->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    $debitAmount = $data->total_after_additional - $data->additional_value;
                } else {
                    $debitAmount = $data->subtotal;
                }
            }
            if ($data->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    $debitAmount = $data->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    $debitAmount = $data->subtotal + $data->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    $debitAmount = $data->subtotal - $data->discount_value;
                } else {
                    $debitAmount = $data->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $debit,
                'debit' => $debitAmount,
                'credit' => 0,
                'type' => 1,
                'info' => $data->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $data->branch_id,
            ]);
        }

        if ($credit) {
            $creditAmount = $data->total_after_additional;
            if (in_array($data->type, [11, 20])) {
                $purchaseDiscountMethod = setting('purchase_discount_method', '2');
                $purchaseAdditionalMethod = setting('purchase_additional_method', '1');

                if ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '1') {
                    $creditAmount = $data->total_after_additional;
                } elseif ($purchaseDiscountMethod == '2' && $purchaseAdditionalMethod == '1') {
                    $creditAmount = $data->subtotal + $data->additional_value;
                } elseif ($purchaseDiscountMethod == '1' && $purchaseAdditionalMethod == '2') {
                    $creditAmount = $data->total_after_additional - $data->additional_value;
                } else {
                    $creditAmount = $data->subtotal;
                }
            }
            if ($data->type == 10) {
                $salesDiscountMethod = setting('sales_discount_method', '1');
                $salesAdditionalMethod = setting('sales_additional_method', '1');

                if ($salesDiscountMethod == '1' && $salesAdditionalMethod == '1') {
                    $creditAmount = $data->total_after_additional;
                } elseif ($salesDiscountMethod == '2' && $salesAdditionalMethod == '1') {
                    $creditAmount = $data->subtotal + $data->additional_value;
                } elseif ($salesDiscountMethod == '1' && $salesAdditionalMethod == '2') {
                    $creditAmount = $data->subtotal - $data->discount_value;
                } else {
                    $creditAmount = $data->subtotal;
                }
            }

            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $credit,
                'debit' => 0,
                'credit' => $creditAmount,
                'type' => 1,
                'info' => $data->notes,
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $data->branch_id,
            ]);
        }

        if (in_array($data->type, [10, 12, 19])) {
            $this->syncCostOfGoodsJournal($data, $operation);
        }

        if ($data->type == 10 && $data->discount_value > 0) {
            $salesDiscountMethod = setting('sales_discount_method', '1');
            if ($salesDiscountMethod == '1') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49,
                    'debit' => $data->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 47,
                    'debit' => 0,
                    'credit' => $data->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            } else {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49,
                    'debit' => $data->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id,
                    'debit' => 0,
                    'credit' => $data->discount_value,
                    'type' => 1,
                    'info' => 'خصم مسموح به - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
        }

        if (in_array($data->type, [11, 20]) && $data->discount_value > 0) {
            $purchaseDiscountMethod = setting('purchase_discount_method', '2');
            if ($purchaseDiscountMethod != '1') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id,
                    'debit' => $data->discount_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'خصم مكتسب - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 54,
                    'debit' => 0,
                    'credit' => $data->discount_value,
                    'type' => 1,
                    'info' => 'خصم مكتسب - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
        }

        if (in_array($data->type, [11, 20]) && $data->additional_value > 0) {
            $purchaseAdditionalMethod = setting('purchase_additional_method', '1');
            if ($purchaseAdditionalMethod == '2') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69,
                    'debit' => $data->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id,
                    'debit' => 0,
                    'credit' => $data->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
        }

        if ($data->type == 10 && $data->additional_value > 0) {
            $salesAdditionalMethod = setting('sales_additional_method', '1');
            if ($salesAdditionalMethod == '2') {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $data->acc1_id,
                    'debit' => $data->additional_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 69,
                    'debit' => 0,
                    'credit' => $data->additional_value,
                    'type' => 1,
                    'info' => 'إضافات - '.$data->notes,
                    'op_id' => $operation->id,
                    'isdeleted' => 0,
                    'branch_id' => $data->branch_id,
                ]);
            }
        }

        if (isVatEnabled() && $data->vat_value > 0) {
            if ($data->type == 10) {
                $vatSalesAccountCode = setting('vat_sales_account_code', '21040101');
                $vatSalesAccountId = $this->getAccountIdByCode($vatSalesAccountCode);
                if ($vatSalesAccountId) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $data->acc1_id,
                        'debit' => $data->vat_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $vatSalesAccountId,
                        'debit' => 0,
                        'credit' => $data->vat_value,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                }
            } elseif (in_array($data->type, [11, 20])) {
                $vatPurchaseAccountCode = setting('vat_purchase_account_code', '21040102');
                $vatPurchaseAccountId = $this->getAccountIdByCode($vatPurchaseAccountCode);
                if ($vatPurchaseAccountId) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $vatPurchaseAccountId,
                        'debit' => $data->vat_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $data->acc1_id,
                        'debit' => 0,
                        'credit' => $data->vat_value,
                        'type' => 1,
                        'info' => 'ضريبة قيمة مضافة - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                }
            }
        }

        if (isWithholdingTaxEnabled() && $data->withholding_tax_value > 0) {
            $withholdingTaxAccountCode = setting('withholding_tax_account_code', '21040103');
            $withholdingTaxAccountId = $this->getAccountIdByCode($withholdingTaxAccountCode);

            if ($withholdingTaxAccountId) {
                if ($data->type == 10) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $withholdingTaxAccountId,
                        'debit' => $data->withholding_tax_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'خصم من المنبع - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $data->acc1_id,
                        'debit' => 0,
                        'credit' => $data->withholding_tax_value,
                        'type' => 1,
                        'info' => 'خصم من المنبع - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                } elseif (in_array($data->type, [11, 20])) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $data->acc1_id,
                        'debit' => $data->withholding_tax_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => 'خصم من المنبع - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $withholdingTaxAccountId,
                        'debit' => 0,
                        'credit' => $data->withholding_tax_value,
                        'type' => 1,
                        'info' => 'خصم من المنبع - '.$data->notes,
                        'op_id' => $operation->id,
                        'isdeleted' => 0,
                        'branch_id' => $data->branch_id,
                    ]);
                }
            }
        }
    }

    private function syncCostOfGoodsJournal($data, $operation)
    {
        $costJournal = JournalHead::where('op_id', $operation->id)
            ->where('journal_id', '>', 0) // Basic check
            ->whereRaw("details LIKE '%قيد تكلفة البضاعة%'")
            ->first();

        if ($costJournal) {
            $costAllSales = $data->total_after_additional - $operation->profit - $data->additional_value;
            if ($costAllSales <= 0) {
                JournalDetail::where('journal_id', $costJournal->journal_id)->delete();
                $costJournal->delete();

                return;
            }

            $costJournal->update([
                'total' => $costAllSales,
                'date' => $data->pro_date,
                'user' => Auth::id(),
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
                'branch_id' => $data->branch_id,
            ]);
            JournalDetail::create([
                'journal_id' => $costJournal->journal_id,
                'account_id' => $data->acc2_id,
                'debit' => 0,
                'credit' => $costAllSales,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $data->branch_id,
            ]);
        } else {
            $this->createCostOfGoodsJournal($data, $operation);
        }
    }
}
