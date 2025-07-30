<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{OperHead, OperationItems, JournalHead, JournalDetail};

class ManufacturingInvoiceService
{
    public function saveManufacturingInvoice($component)
    {
        // $data = $component->all();
        // $rules = [
        //     'pro_id' => 'required|numeric',
        //     'rawAccount' => 'required|numeric',
        //     'productAccount' => 'required|numeric',
        //     'employee' => 'required|numeric',
        //     'invoiceDate' => 'required|date',
        //     'OperatingAccount' => 'required|numeric',
        //     'totalManufacturingCost' => 'required|numeric|min:0',
        //     'totalRawMaterialsCost' => 'required|numeric|min:0',
        //     'totalAdditionalExpenses' => 'nullable|numeric|min:0',
        //     'selectedRawMaterials' => 'required|array|min:1',
        //     'selectedRawMaterials.*.item_id' => 'required|numeric',
        //     'selectedRawMaterials.*.quantity' => 'required|numeric|min:0.01',
        //     'selectedRawMaterials.*.unit_cost' => 'required|numeric|min:0',
        //     'selectedRawMaterials.*.total_cost' => 'required|numeric|min:0',
        //     'selectedProducts' => 'required|array|min:1',
        //     'selectedProducts.*.product_id' => 'required|numeric',
        //     'selectedProducts.*.quantity' => 'required|numeric|min:0.01',
        //     'selectedProducts.*.unit_cost' => 'required|numeric|min:0',
        //     'selectedProducts.*.total_cost' => 'required|numeric|min:0',
        //     'additionalExpenses' => 'nullable|array',
        //     'additionalExpenses.*.amount' => 'required|numeric|min:0.01',
        //     'additionalExpenses.*.account_id' => 'required|numeric',
        // ];

        // $validator = validator($data, $rules);

        // if ($validator->fails()) {
        //     throw new ValidationException($validator);
        // }
        // dd($component->all());
        // 1. Create OperHead record
        // if ($component->totalPercentage !== 100.0) {
        //     $component->dispatch('error-swal', title: 'خطأ!', text: 'يجب أن يكون مجموع النسب 100%.', icon: 'error');
        //     return;
        // }

        // التحقق من وجود مواد خام
        if (count($component->selectedRawMaterials) === 0) {
            $component->dispatch('error-swal', title: 'خطأ!', text: 'يجب اختيار مواد خام.', icon: 'error');
            return;
        }
        try {
            DB::beginTransaction();

            $operation = OperHead::create([
                'pro_id' => $component->pro_id,
                'pro_type' => 59,
                'acc1' => $component->rawAccount,
                'acc2' => $component->productAccount,
                'emp_id' => $component->employee,
                'store_id' =>  $component->productAccount,
                'is_stock' => 1,
                'is_finance' => 0,
                'is_manager' => 0,
                'is_journal' => 1,
                'pro_date' => $component->invoiceDate,
                'pro_value' => $component->totalManufacturingCost,
                'fat_net' => $component->totalManufacturingCost,
                'info' => $component->description,
                'user' => Auth::id(),
            ]);

            foreach ($component->selectedRawMaterials as $raw) {
                OperationItems::create([
                    'pro_tybe' => 59,
                    'detail_store' => $component->rawAccount,
                    'pro_id' => $operation->id,
                    'item_id' => $raw['item_id'],
                    'unit_id' => $raw['unit_id'] ?? null,
                    'qty_in' => 0,
                    'qty_out' => $raw['quantity'],
                    'item_price' => $raw['unit_cost'],
                    'cost_price' => $raw['unit_cost'],
                    'detail_value' => $raw['total_cost'],
                    'is_stock' => 1,
                ]);
            }

            foreach ($component->selectedProducts as $product) {
                OperationItems::create([
                    'pro_tybe' => 59,
                    'detail_store' => $component->productAccount,
                    'pro_id' => $operation->id,
                    'item_id' => $product['product_id'],
                    'unit_id' => $product['unit_id'] ?? null,
                    'qty_in' => $product['quantity'],
                    'qty_out' => 0,
                    'item_price' => $product['unit_cost'],
                    'cost_price' => $product['unit_cost'],
                    'detail_value' => $product['total_cost'],
                    'is_stock' => 1,
                ]);
            }

            $journalId = (JournalHead::max('journal_id') ?? 0) + 1;
            $totalRaw = $component->totalRawMaterialsCost;
            // $totalRowProducts = collect($component->selectedProducts)->sum('total_cost');
            $totalExpenses = $component->totalAdditionalExpenses;
            // $totalManufacturing = $component->totalManufacturingCost;

            JournalHead::create([
                'journal_id' => $journalId,
                'total' => $totalRaw,
                'date' => $component->invoiceDate,
                'op_id' => $operation->id,
                'pro_type' => 59,
                'details' => 'صرف مواد خام للتصنيع',
                'user' => Auth::id(),
            ]);
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $component->OperatingAccount,
                'debit' => $totalRaw,
                'credit' => 0,
                'type' => 1,
                'info' => 'صرف مواد خام للتصنيع',
                'op_id' => $operation->id,
            ]);
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $component->rawAccount,
                'debit' => 0,
                'credit' => $totalRaw,
                'type' => 1,
                'info' => 'صرف مواد خام للتصنيع',
                'op_id' => $operation->id,
            ]);

            if ($totalExpenses > 0 && isset($component->additionalExpenses)) {
                $journalId++;

                JournalHead::create([
                    'journal_id' => $journalId,
                    'total' => $totalExpenses,
                    'date' => $component->invoiceDate,
                    'op_id' => $operation->id,
                    'pro_type' => 59,
                    'details' => 'مصاريف إضافية للتصنيع',
                    'user' => Auth::id(),
                ]);
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $component->OperatingAccount,
                    'debit' => $totalExpenses,
                    'credit' => 0,
                    'type' => 1,
                    'info' => 'مصاريف إضافية للتصنيع',
                    'op_id' => $operation->id,
                ]);

                foreach ($component->additionalExpenses as $expense) {
                    JournalDetail::create([
                        'journal_id' => $journalId,
                        'account_id' => $expense['account_id'],
                        'debit' => 0,
                        'credit' => $expense['amount'],
                        'type' => 1,
                        'info' => 'مصاريف إضافية للتصنيع',
                        'op_id' => $operation->id,
                    ]);
                }
            }

            $journalId++;
            JournalHead::create([
                'journal_id' => $journalId,
                'total' => $totalRaw,
                'date' => $component->invoiceDate,
                'op_id' => $operation->id,
                'pro_type' => 59,
                'details' => 'إنتاج منتجات تامة',
                'user' => Auth::id(),
            ]);
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $component->productAccount,
                'debit' => $totalRaw,
                'credit' => 0,
                'type' => 1,
                'info' => 'إنتاج منتجات تامة',
                'op_id' => $operation->id,
            ]);
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $component->OperatingAccount,
                'debit' => 0,
                'credit' => $totalRaw,
                'type' => 1,
                'info' => 'إنتاج منتجات تامة',
                'op_id' => $operation->id,
            ]);

            DB::commit();

            $component->dispatch('success-swal', title: 'تم الحفظ!', text: 'تم حفظ فاتورة التصنيع بنجاح.', icon: 'success');
            return $operation->id;
        } catch (\Exception $e) {
            DB::rollBack();
            $component->dispatch('error-swal', title: 'خطأ !', text: 'حدث خطا اثناء الحفظ.', icon: 'error');
            return back()->withInput();
        }
    }
}
