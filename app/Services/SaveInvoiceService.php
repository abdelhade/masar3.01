<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{OperHead, OperationItems, Item, JournalHead, JournalDetail, User};
use Modules\Notifications\Notifications\OrderNotification;

class SaveInvoiceService
{
    public function saveInvoice($component, $isEdit = false)
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
        ], [
            'invoiceItems.*.quantity.min' => 'الكمية يجب أن تكون أكبر من الصفر',
            'invoiceItems.*.price.min' => 'السعر يجب أن يكون قيمة موجبة',
        ]);

        // التحقق من الكميات المتاحة فقط للمبيعات والصرف
        foreach ($component->invoiceItems as $index => $item) {
            if (in_array($component->type, [10, 12, 18, 19, 21])) {
                $availableQty = OperationItems::where('item_id', $item['item_id'])
                    ->where('detail_store', $component->type == 21 ? $component->acc1_id : $component->acc2_id)
                    ->selectRaw('SUM(qty_in - qty_out) as total')
                    ->value('total') ?? 0;

                // في حالة التعديل، نضيف الكمية المحفوظة مسبقاً للصنف
                if ($isEdit && $component->operationId) {
                    $previousQty = OperationItems::where('pro_id', $component->operationId)
                        ->where('item_id', $item['item_id'])
                        ->sum('qty_out') ?? 0;
                    $availableQty += $previousQty;
                }

                if ($availableQty < $item['quantity']) {
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
            // dd($component->all());

            // for testing notifications

            // $user = User::find(Auth::id());
            // $user->notify(new OrderNotification([
            //     'id' => 55,
            //     'title' => 'طلب جديد',
            //     'message' => 'تم إنشاء طلب جديد',
            //     'icon' => 'fas fa-shopping-cart',
            //     'created_at' => now()->toDateTimeString(),
            // ]));

            $isJournal = in_array($component->type, [10, 11, 12, 13, 18, 19, 20, 21, 23, 24]) ? 1 : 0;
            $isManager = $isJournal ? 0 : 1;
            $isReceipt = in_array($component->type, [10, 22, 13]);
            $isPayment = in_array($component->type, [11, 12]);

            $operationData = [
                'pro_type'       => $component->type,
                'acc1'           => $component->acc1_id,
                'acc2'           => $component->acc2_id,
                'emp_id'         => $component->emp_id,
                'emp2_id' => $component->delivery_id,
                'is_manager'     => $isManager,
                'is_journal'     => $isJournal,
                'is_stock'       => 1,
                'pro_date'       => $component->pro_date,
                // op2 may be provided by the create form when converting an existing operation
                'op2'            => $component->op2 ?? request()->get('op2') ?? 0,
                'pro_value'      => $component->total_after_additional,
                'fat_net'        => $component->total_after_additional,
                'price_list'     => $component->selectedPriceType,
                'accural_date'   => $component->accural_date,
                'pro_serial'     => $component->serial_number,
                'fat_disc_per'   => $component->discount_percentage,
                'fat_disc'       => $component->discount_value,
                'fat_plus_per'   => $component->additional_percentage,
                'fat_plus'       => $component->additional_value,
                'fat_total'      => $component->subtotal,
                'info'           => $component->notes,
                'status'         => $component->status ?? 0,
                'acc_fund'       => $component->cash_box_id ?: 0,
                'paid_from_client' => $component->received_from_client,
                'user'           => Auth::id(),
                'branch_id' => $component->branch_id
            ];

            // تحديث الفاتورة الحالية أو إنشاء جديدة
            if ($isEdit && $component->operationId) {
                $operation = OperHead::findOrFail($component->operationId);
                $this->deleteRelatedRecords($operation->id);
                $operationData['pro_id'] = $operation->pro_id;
                $operation->update($operationData);
            } else {
                $operationData['pro_id'] = $component->pro_id;
                $operation = OperHead::create($operationData);

                if (!empty($operationData['op2'])) {
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
                            'is_locked' => 1 // قفل المستند الأصلي
                        ]);

                        // ✅ تحديث الـ root (أمر الاحتياج الأصلي)
                        $rootId = $parent->origin_id ?: $parent->id;
                        $root = OperHead::find($rootId);
                        if ($root && $root->id != $parent->id) {
                            $root->update([
                                'workflow_state' => $this->getWorkflowStateByType($operation->pro_type),
                                'is_locked' => 1 // قفل المستند الأصلي
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
            }

            // إضافة عناصر الفاتورة
            $totalProfit = 0;
            foreach ($component->invoiceItems as $invoiceItem) {
                $itemId    = $invoiceItem['item_id'];
                $quantity  = $invoiceItem['quantity'];
                $unitId    = $invoiceItem['unit_id'];
                $price     = $invoiceItem['price'];
                $subValue  = $invoiceItem['sub_value'] ?? $price * $quantity;
                $discount  = $invoiceItem['discount'] ?? 0;
                $itemCost  = Item::where('id', $itemId)->value('average_cost');

                if ($component->type == 21) {
                    // 1. خصم الكمية من المخزن المحوَّل منه (المخزن الأول acc1)
                    OperationItems::create([
                        'pro_tybe'      => $component->type,
                        'detail_store'  => $component->acc1_id, // <-- المخزن الأول (المُرسِل)
                        'pro_id'        => $operation->id, // خل نستخدم id or pro_id
                        'item_id'       => $itemId,
                        'unit_id'       => $unitId,
                        'qty_in'        => 0,
                        'qty_out'       => $quantity, // <-- خصم الكمية
                        'item_price'    => $price,
                        'cost_price'    => $itemCost,
                        'item_discount' => $discount,
                        'detail_value'  => $subValue,
                        'notes'         => $invoiceItem['notes'] ?? 'تحويل إلى مخزن ' . $component->acc2_id,
                        'is_stock'      => 1,
                        'branch_id' => $component->branch_id

                        // 'profit'        => $profit,
                    ]);

                    // 2. إضافة الكمية إلى المخزن المحوَّل إليه (المخزن الثاني acc2)
                    OperationItems::create([
                        'pro_tybe'      => $component->type,
                        'detail_store'  => $component->acc2_id, // <-- المخزن الثاني (المُستقبِل)
                        'pro_id'        => $operation->id, // خل نستخدم id or pro_id
                        'item_id'       => $itemId,
                        'unit_id'       => $unitId,
                        'qty_in'        => $quantity, // <-- إضافة الكمية
                        'qty_out'       => 0,
                        'item_price'    => $price,
                        'cost_price'    => $itemCost,
                        'item_discount' => $discount,
                        'detail_value'  => $subValue,
                        'notes'         => $invoiceItem['notes'] ?? 'تحويل من مخزن ' . $component->acc1_id,
                        'is_stock'      => 1,
                        'branch_id' => $component->branch_id
                        // 'profit'        => $profit,
                    ]);
                }

                $qty_in = $qty_out = 0;
                if (in_array($component->type, [11, 12, 20])) $qty_in = $quantity;
                if (in_array($component->type, [10, 13, 18, 19])) $qty_out = $quantity;

                // تحديث متوسط التكلفة للمشتريات
                if (in_array($component->type, [11, 12, 20])) {
                    $this->updateAverageCost($itemId, $quantity, $subValue, $itemCost);
                }

                // حساب الربح للمبيعات
                if (in_array($component->type, [10, 13, 19])) {
                    $discountItem = $component->subtotal != 0
                        ? ($component->discount_value * $subValue / $component->subtotal)
                        : 0;

                    $itemCostTotal = $itemCost * $quantity;
                    $profit = ($subValue - $discountItem) - $itemCostTotal;
                    $totalProfit += $profit;
                } else {
                    $profit = 0;
                }

                // إنشاء عنصر الفاتورة لاى شئ غير التحويلات حاليا النوع 21
                if ($component->type != 21) {
                    // معالجة خاصة لطلب الاحتياج - يجب أن نضع الكمية في qty_out (اعتباره صرف احتياج)
                    if ($component->type == 25) {
                        $qty_in =  $quantity;
                        $qty_out = 0;
                    }
                    OperationItems::create([
                        'pro_tybe'      => $component->type,
                        'detail_store'  => $component->acc2_id,
                        'pro_id'        => $operation->id, // خل نستخدم id or pro_id
                        'item_id'       => $itemId,
                        'unit_id'       => $unitId,
                        'qty_in'        => $qty_in,
                        'qty_out'       => $qty_out,
                        'item_price'    => $price,
                        'cost_price'    => $itemCost,
                        'item_discount' => $discount,
                        'detail_value'  => $subValue,
                        'notes'         => $invoiceItem['notes'] ?? null,
                        'is_stock'      => 1,
                        'profit'        => $profit,
                        'branch_id' => $component->branch_id
                    ]);
                }
            }

            // تحديث إجمالي الربح
            $operation->update(['profit' => $totalProfit]);

            // إنشاء القيود المحاسبية
            if ($isJournal) {
                $this->createJournalEntries($component, $operation);
            }

            // إنشاء سند القبض/الدفع إذا وُجد
            if ($component->received_from_client > 0) {
                $this->createVoucher($component, $operation, $isReceipt, $isPayment);
            }

            DB::commit();

            $message = $isEdit ? 'تم تحديث الفاتورة بنجاح.' : 'تم حفظ الفاتورة بنجاح.';
            $component->dispatch(
                'swal',
                title: 'تم الحفظ!',
                text: $message,
                icon: 'success'
            );

            return $operation->id;
        } catch (\Exception) {
            DB::rollBack();
            logger()->error('خطأ أثناء حفظ الفاتورة: ');
            $component->dispatch(
                'error',
                title: 'خطأ!',
                text: 'فشل في حفظ الفاتورة: ',
                icon: 'error'
            );
            return false;
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

    private function updateAverageCost($itemId, $quantity, $subValue, $currentCost)
    {
        $oldQty = OperationItems::where('item_id', $itemId)
            ->where('is_stock', 1)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        $newQty = $oldQty + $quantity;

        if ($oldQty == 0 && $currentCost == 0) {
            $newCost = $subValue / $quantity;
        } else {
            $newCost = $newQty > 0 ? (($oldQty * $currentCost) + $subValue) / $newQty : $currentCost;
        }

        Item::where('id', $itemId)->update(['average_cost' => $newCost]);
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
            'total'      => $component->total_after_additional,
            'op2'        => $operation->id,
            'op_id'      => $operation->id,
            'pro_type'   => $component->type,
            'date'       => $component->pro_date,
            'details'    => $component->notes,
            'user'       => Auth::id(),
            'branch_id' => $component->branch_id
        ]);

        // الطرف المدين
        if ($debit) {
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $debit,
                'debit'      => $component->total_after_additional,
                'credit'     => 0,
                'type'       => 1,
                'info'       => $component->notes,
                'op_id'      => $operation->id,
                'isdeleted'  => 0,
                'branch_id' => $component->branch_id
            ]);
        }

        // الطرف الدائن
        if ($credit) {
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $credit,
                'debit'      => 0,
                'credit'     => $component->total_after_additional - $component->additional_value,
                'type'       => 1,
                'info'       => $component->notes,
                'op_id'      => $operation->id,
                'isdeleted'  => 0,
                'branch_id' => $component->branch_id
            ]);
        }

        // قيد الإضافات إن وُجدت
        if ($component->additional_value > 0) {
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => 69, // حساب الإضافات
                'debit'      => 0,
                'credit'     => $component->additional_value,
                'type'       => 1,
                'info'       => 'إضافات - ' . $component->notes,
                'op_id'      => $operation->id,
                'isdeleted'  => 0,
                'branch_id' => $component->branch_id
            ]);
        }

        // قيد تكلفة البضاعة المباعة للمبيعات
        if (in_array($component->type, [10, 12, 19])) {
            $this->createCostOfGoodsJournal($component, $operation);
        }
    }

    /**
     * Record an operation transition between two operhead records for audit and workflow tracking.
     */
    private function recordTransition(?int $fromId, ?int $toId, ?int $fromState, ?int $toState, ?int $userId, string $action, ?int $branchId = null): void
    {
        if (!$fromId || !$toId) {
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
                'total'      => $costAllSales,
                'op2'        => $operation->id,
                'op_id'      => $operation->id,
                'pro_type'   => $component->type,
                'date'       => $component->pro_date,
                'details'    => 'قيد تكلفة البضاعة - ' . $component->notes,
                'user'       => Auth::id(),
                'branch_id' => $component->branch_id
            ]);

            JournalDetail::create([
                'journal_id' => $costJournalId,
                'account_id' => 16, // حساب تكلفة البضاعة المباعة
                'debit'      => $costAllSales,
                'credit'     => 0,
                'type'       => 1,
                'info'       => 'قيد تكلفة البضاعة',
                'op_id'      => $operation->id,
                'isdeleted'  => 0,
                'branch_id' => $component->branch_id
            ]);

            JournalDetail::create([
                'journal_id' => $costJournalId,
                'account_id' => $component->acc2_id, // حساب المخزن
                'debit'      => 0,
                'credit'     => $costAllSales,
                'type'       => 1,
                'info'       => 'قيد تكلفة البضاعة',
                'op_id'      => $operation->id,
                'isdeleted'  => 0,
                'branch_id' => $component->branch_id
            ]);
        }
    }

    private function createVoucher($component, $operation, $isReceipt, $isPayment)
    {
        $voucherValue = $component->received_from_client ?? $component->total_after_additional;
        $cashBoxId = is_numeric($component->cash_box_id) && $component->cash_box_id > 0
            ? (int)$component->cash_box_id
            : null;

        if (!$cashBoxId) {
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
            'pro_id'     => $operation->pro_id,
            'pro_type'   => $proType,
            'acc1'       => $component->acc1_id,
            'acc2'       => $cashBoxId,
            'pro_value'  => $voucherValue,
            'pro_date'   => $component->pro_date,
            'info'       => 'سند ' . $voucherType . ' آلي مرتبط بعملية رقم ' . $operation->id,
            'op2'        => $operation->id,
            'is_journal' => 1,
            'is_stock'   => 0,
            'user'       => Auth::id(),
            'branch_id' => $component->branch_id
        ]);

        // إنشاء قيد السند
        $voucherJournalId = JournalHead::max('journal_id') + 1;

        JournalHead::create([
            'journal_id' => $voucherJournalId,
            'total'      => $voucherValue,
            'op_id'      => $voucher->id,
            'op2'        => $operation->id,
            'pro_type'   => $proType,
            'date'       => $component->pro_date,
            'details'    => 'قيد سند ' . $voucherType . ' آلي',
            'user'       => Auth::id(),
            'branch_id' => $component->branch_id
        ]);

        JournalDetail::create([
            'journal_id' => $voucherJournalId,
            'account_id' => $debitAccount,
            'debit'      => $voucherValue,
            'credit'     => 0,
            'type'       => 1,
            'info'       => 'سند ' . $voucherType,
            'op_id'      => $voucher->id,
            'isdeleted'  => 0,
            'branch_id' => $component->branch_id
        ]);

        JournalDetail::create([
            'journal_id' => $voucherJournalId,
            'account_id' => $creditAccount,
            'debit'      => 0,
            'credit'     => $voucherValue,
            'type'       => 1,
            'info'       => 'سند ' . $voucherType,
            'op_id'      => $voucher->id,
            'isdeleted'  => 0,
            'branch_id' => $component->branch_id
        ]);
    }
}
