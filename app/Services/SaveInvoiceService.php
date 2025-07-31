<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{OperHead, OperationItems, Item, JournalHead, JournalDetail};

class SaveInvoiceService
{
    public function saveInvoice($component)
    {

        // dd($component->all());
        if (empty($component->invoiceItems)) {
            $component->dispatch('no-items', title: 'خطا!', text: 'لا يمكن حفظ الفاتورة بدون أصناف.', icon: 'error');
            return;
        }

        $component->validate([
            'acc1_id' => 'required|exists:acc_head,id',
            'acc2_id' => 'required|exists:acc_head,id',
            'pro_date' => 'required|date',
            'invoiceItems.*.item_id' => 'required|exists:items,id',
            'invoiceItems.*.unit_id' => 'required|exists:units,id',
            'invoiceItems.*.quantity' => 'required|numeric|min:0.001',
            'invoiceItems.*.price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'additional_percentage' => 'nullable|numeric|min:0|max:100',
            'received_from_client' => 'nullable|numeric|min:0',
        ], [
            'invoiceItems.*.quantity.min' => 'الكمية يجب أن تكون أكبر من الصفر',
            'invoiceItems.*.price.min' => 'السعر يجب أن يكون قيمة موجبة',
        ]);

        foreach ($component->invoiceItems as $index => $item) {
            // حساب الكميه المتوفره للصنف
            $availableQty = OperationItems::where('item_id', $item['item_id'])
                ->where('detail_store', $component->acc2_id)
                ->selectRaw('SUM(qty_in - qty_out) as total')
                ->value('total') ?? 0;
            if (in_array($component->type, [10, 12, 18, 19])) { // عمليات صرف
                if ($availableQty < $item['quantity']) {
                    $itemName = Item::find($item['item_id'])->name;
                    $component->dispatch('no-quantity', title: 'خطا!', text: 'الكمية غير متوفرة للصنف.' . $itemName, icon: 'error');
                    return;
                }
            }
        }
        DB::beginTransaction();
        try {
            $isJournal = in_array($component->type, [10, 11, 12, 13, 18, 19, 20, 21, 23]) ? 1 : 0;
            $isManager = $isJournal ? 0 : 1;

            $isReceipt = in_array($component->type, [10, 22, 13]); // سند قبض
            $isPayment = in_array($component->type, [11, 12]); // سند دفع

            $operation = OperHead::create([
                'pro_id'         => $component->pro_id,
                'pro_type'       => $component->type,
                'acc1'           => $component->acc1_id,
                'acc2'           => $component->acc2_id,
                'emp_id'         => $component->emp_id,
                'is_manager'     => $isManager,
                'is_journal'     => $isJournal,
                'is_stock'       => 1,
                'pro_date'       => $component->pro_date,
                'op2'            => 0,
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
            ]);

            $totalProfit = 0;
            // $salesCost = 0;

            foreach ($component->invoiceItems as $invoiceItem) {
                $itemId    = $invoiceItem['item_id'];
                $quantity  = $invoiceItem['quantity'];
                $unitId    = $invoiceItem['unit_id'];
                $price     = $invoiceItem['price'];
                $subValue  = $invoiceItem['sub_value'] ?? $price * $quantity;
                $discount  = $invoiceItem['discount'] ?? 0;

                $itemCost  = Item::where('id', $itemId)->value('average_cost');

                $qty_in = $qty_out = 0;
                if (in_array($component->type, [11, 12, 20])) $qty_in = $quantity;
                if (in_array($component->type, [10, 13, 18, 19])) $qty_out = $quantity;

                if (in_array($component->type, [11, 12, 20])) {
                    $oldQty = OperationItems::where('item_id', $itemId)
                        ->where('is_stock', 1)
                        ->selectRaw('SUM(qty_in - qty_out) as total')
                        ->value('total') ?? 0;
                    $oldCost = $itemCost;

                    $newQty = $oldQty + $quantity;


                    if ($oldQty == 0 && $oldCost == 0) {
                        $newCost = $price; // استخدام سعر الوحدة إذا لم يكن هناك رصيد سابق
                    } else {
                        $newCost = $newQty > 0 ? (($oldQty * $oldCost) + $subValue) / $newQty : $oldCost;
                    }

                    Item::where('id', $itemId)->update(['average_cost' => $newCost]);

                    // $salesCost += round($invoiceItem['quantity'] * $q, 2);
                }

                // حساب الربح للمبيعات والمردودات
                if (in_array($component->type, [10, 13, 19])) {
                    // حساب الخصم الخاص بالصنف بناء على نسبة خصم الفاتورة
                    $discountItem = $component->subtotal != 0
                        ? ($component->discount_value * $subValue / $component->subtotal)
                        : 0;

                    // إجمالي تكلفة الصنف
                    $itemCostTotal = $itemCost * $quantity;

                    // الربح = صافي المبلغ بعد خصم الجزء النسبي - التكلفة
                    $profit = ($subValue - $discountItem) - $itemCostTotal;
                    $totalProfit += $profit;
                } else {
                    $profit = 0;
                }

                OperationItems::create([
                    'pro_tybe'      => $component->type,
                    'detail_store'  => $component->acc2_id,
                    'pro_id'        => $operation->id,
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
                ]);
            }

            $operation->update(['profit' => $totalProfit]);

            if ($isJournal) {
                $journalId = JournalHead::max('journal_id') + 1;
                $debit = $credit = null;
                switch ($component->type) {
                    case 10:
                        $debit = $component->acc1_id;
                        $credit = 47; // حساب المبيعات
                        break;
                    case 11:
                        $debit = $component->acc2_id; // حساب  المخزن
                        $credit = $component->acc1_id;
                        break;
                    case 12:
                        $debit = 48; //حساب مردود المبيعات
                        $credit = $component->acc1_id;
                        break;
                    case 13:
                        $debit = $component->acc1_id;
                        $credit = $component->acc2_id;  // مردود المخزن
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
                        $debit = $component->acc1_id;
                        $credit = $component->acc2_id;
                        break;
                }

                JournalHead::create([
                    'journal_id' => $journalId,
                    'total'      => $component->total_after_additional,
                    'op2'        => $operation->id,
                    'op_id'      => $operation->id,
                    'pro_type'   => $component->type,
                    'date'       => $component->pro_date,
                    'details'    => $component->notes,
                    'user'       => Auth::id(),
                ]);

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
                    ]);
                }

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
                    ]);
                }

                if ($component->additional_value > 0) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => 69, // الضريبة
                        'debit'      => 0,
                        'credit'     => $component->additional_value,
                        'type'       => 1,
                        'info'       => $component->notes,
                        'op_id'      => $operation->id,
                        'isdeleted'  => 0,
                    ]);
                }

                if (in_array($component->type, [10, 12, 19])) {

                    $costJournalId = JournalHead::max('journal_id') + 1;

                    JournalHead::create([
                        'journal_id' => $costJournalId,
                        'total'      => $component->total_after_additional,
                        'op2'        => $operation->id,
                        'op_id'      => $operation->id,
                        'pro_type'   => $component->type,
                        'date'       => $component->pro_date,
                        'details'    => $component->notes,
                        'user'       => Auth::id(),
                    ]);

                    $costAllSales = $component->total_after_additional - $totalProfit  - $component->additional_value;

                    JournalDetail::create([
                        'journal_id' => $costJournalId,
                        'account_id' => 16, // حساب تكلفة البضاعة
                        'debit'      => $costAllSales,
                        'credit'     => 0,
                        'type'       => 1,
                        'info'       => 'قيد تكلفة البضاعة',
                        'op_id'      => $operation->id,
                        'isdeleted'  => 0,
                    ]);

                    JournalDetail::create([
                        'journal_id' => $costJournalId,
                        'account_id' => $component->acc2_id, // حساب تكلفة البضاعة
                        'debit'      => 0,
                        'credit'     => $costAllSales,
                        'type'       => 1,
                        'info'       => 'قيد تكلفة البضاعة',
                        'op_id'      => $operation->id,
                        'isdeleted'  => 0,
                    ]);
                }
            }

            if ($component->received_from_client > 0) {
                // إنشاء سند قبض أو دفع
                if ($isReceipt || $isPayment) {
                    $voucherValue = $component->received_from_client ?? $component->total_after_additional;
                    // Ensure cash_box_id is a valid integer, otherwise set to null or a default value (e.g., 0)
                    $cashBoxId = is_numeric($component->cash_box_id) && $component->cash_box_id > 0 ? (int)$component->cash_box_id : null;
                    if ($isReceipt) {
                        $proType = 1;
                    } elseif ($isPayment) {
                        $proType = 2;
                    }
                    $voucher = OperHead::create([
                        'pro_id'     => $component->pro_id,
                        'pro_type'   => $proType,
                        'acc1'       => $component->acc1_id,
                        'acc2'       => $cashBoxId,
                        'pro_value'  => $voucherValue,
                        'pro_date'   => $component->pro_date,
                        'info'       => 'سند آلي مرتبط بعملية رقم ' . $component->pro_id,
                        'op2'        => $operation->id,
                        'is_journal' => 1,
                        'is_stock'   => 0,
                    ]);

                    $voucherJournalId = JournalHead::max('journal_id') + 1;
                    JournalHead::create([
                        'journal_id' => $voucherJournalId,
                        'total'      => $voucherValue,
                        'op_id'      => $voucher->id,
                        'op2'        => $operation->id,
                        'pro_type'   => $component->type,
                        'date'       => $component->pro_date,
                        'details'    => 'قيد سند ' . ($isReceipt ? 'قبض' : 'دفع') . ' آلي',
                        'user'       => Auth::id(),
                    ]);

                    JournalDetail::create([
                        'journal_id' => $voucherJournalId,
                        'account_id' => $isReceipt ? $component->cash_box_id : $component->acc1_id,
                        'debit'      => $voucherValue,
                        'credit'     => 0,
                        'type'       => 1,
                        'info'       => 'سند ' . ($isReceipt ? 'قبض' : 'دفع'),
                        'op_id'      => $voucher->id,
                        'isdeleted'  => 0,
                    ]);

                    JournalDetail::create([
                        'journal_id' => $voucherJournalId,
                        'account_id' => $isReceipt ? $component->acc1_id : $component->cash_box_id,
                        'debit'      => 0,
                        'credit'     => $voucherValue,
                        'type'       => 1,
                        'info'       => 'سند ' . ($isReceipt ? 'قبض' : 'دفع'),
                        'op_id'      => $voucher->id,
                        'isdeleted'  => 0,
                    ]);


                }
            }
            DB::commit();
            $component->dispatch('swal', title: 'تم الحفظ!', text: 'تم حفظ الفاتوره بنجاح.', icon: 'success');
            return $operation->id;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('خطأ أثناء حفظ الفاتورة: ');
            return back()->withInput();
        }
    }
}
