<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{OperHead, OperationItems, JournalHead, JournalDetail};

class ManufacturingInvoiceService
{
    public function saveManufacturingInvoice($component, $isTemplate = false)
    {
        // dd($component->all());
        // 1. إعداد البيانات للتحقق من صحتها
        $data = [
            'pro_id' => $component->pro_id,
            'rawAccount' => $component->rawAccount,
            'productAccount' => $component->productAccount,
            'employee' => $component->employee,
            'invoiceDate' => $component->invoiceDate,
            'OperatingAccount' => $component->OperatingAccount,
            'totalManufacturingCost' => $component->totalManufacturingCost,
            'totalRawMaterialsCost' => $component->totalRawMaterialsCost,
            'totalAdditionalExpenses' => $component->totalAdditionalExpenses,
            'selectedRawMaterials' => $component->selectedRawMaterials,
            'selectedProducts' => $component->selectedProducts,
            'additionalExpenses' => $component->additionalExpenses ?? [],
            'description' => $component->description,
        ];

        // 2. قواعد التحقق من صحة البيانات
        $rules = [
            'pro_id' => 'required|numeric|min:1',
            'rawAccount' => 'required|numeric|exists:acc_head,id',
            'productAccount' => 'required|numeric|exists:acc_head,id',
            'employee' => 'required|numeric|exists:acc_head,id',
            'invoiceDate' => 'required|date|before_or_equal:today',
            'OperatingAccount' => 'required|numeric|exists:acc_head,id',
            'totalManufacturingCost' => 'required|numeric|min:0.01',
            'totalRawMaterialsCost' => 'required|numeric|min:0.01',
            'totalAdditionalExpenses' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',

            // التحقق من المواد الخام
            'selectedRawMaterials' => 'required|array|min:1',
            'selectedRawMaterials.*.item_id' => 'required|numeric|exists:items,id',
            'selectedRawMaterials.*.quantity' => 'required|numeric|min:0.01|max:999999',
            'selectedRawMaterials.*.unit_cost' => 'required|numeric|min:0|max:999999',
            'selectedRawMaterials.*.total_cost' => 'required|numeric|min:0.01|max:999999999',

            // التحقق من المنتجات
            'selectedProducts' => 'required|array|min:1',
            'selectedProducts.*.product_id' => 'required|numeric|exists:items,id',
            'selectedProducts.*.quantity' => 'required|numeric|min:0.01|max:999999',
            'selectedProducts.*.unit_cost' => 'required|numeric|min:0|max:999999',
            'selectedProducts.*.total_cost' => 'required|numeric|min:0|max:999999999',
            'selectedProducts.*.cost_percentage' => 'required|numeric|min:0|max:100',

            // التحقق من المصاريف الإضافية
            'additionalExpenses' => 'nullable|array',
            'additionalExpenses.*.description' => 'required_with:additionalExpenses|string|min:3|max:255',
            'additionalExpenses.*.amount' => 'required_with:additionalExpenses|numeric|min:0.01|max:999999',
            'additionalExpenses.*.account_id' => 'required_with:additionalExpenses|numeric|exists:acc_head,id',
        ];

        // 3. رسائل الخطأ المخصصة
        $messages = [
            'required' => 'هذا الحقل مطلوب',
            'numeric' => 'يجب إدخال قيمة رقمية صحيحة',
            'min' => 'القيمة أقل من الحد المسموح',
            'max' => 'القيمة أكبر من الحد المسموح',
            'exists' => 'القيمة المختارة غير صحيحة',
            'date' => 'يجب إدخال تاريخ صحيح',
            'before_or_equal' => 'التاريخ لا يمكن أن يكون في المستقبل',
            'array' => 'البيانات يجب أن تكون في صورة قائمة',
            'string' => 'يجب إدخال نص',

            // رسائل مخصصة للحقول المحددة
            'pro_id.required' => 'رقم الفاتورة مطلوب',
            'rawAccount.required' => 'حساب المواد الخام مطلوب',
            'productAccount.required' => 'حساب المنتجات مطلوب',
            'employee.required' => 'الموظف مطلوب',
            'invoiceDate.required' => 'تاريخ الفاتورة مطلوب',
            'OperatingAccount.required' => 'حساب التشغيل مطلوب',
            'totalManufacturingCost.min' => 'إجمالي تكلفة التصنيع يجب أن يكون أكبر من صفر',
            'totalRawMaterialsCost.min' => 'إجمالي تكلفة المواد الخام يجب أن يكون أكبر من صفر',

            'selectedRawMaterials.required' => 'يجب اختيار مواد خام على الأقل',
            'selectedRawMaterials.min' => 'يجب اختيار مادة خام واحدة على الأقل',
            'selectedRawMaterials.*.item_id.required' => 'يجب اختيار المادة الخام',
            'selectedRawMaterials.*.item_id.exists' => 'المادة الخام المختارة غير موجودة',
            'selectedRawMaterials.*.quantity.required' => 'كمية المادة الخام مطلوبة',
            'selectedRawMaterials.*.quantity.min' => 'كمية المادة الخام يجب أن تكون أكبر من صفر',
            'selectedRawMaterials.*.unit_cost.required' => 'سعر الوحدة للمادة الخام مطلوب',
            'selectedRawMaterials.*.total_cost.required' => 'إجمالي تكلفة المادة الخام مطلوب',

            'selectedProducts.required' => 'يجب اختيار منتجات على الأقل',
            'selectedProducts.min' => 'يجب اختيار منتج واحد على الأقل',
            'selectedProducts.*.product_id.required' => 'يجب اختيار المنتج',
            'selectedProducts.*.product_id.exists' => 'المنتج المختار غير موجود',
            'selectedProducts.*.quantity.required' => 'كمية المنتج مطلوبة',
            'selectedProducts.*.quantity.min' => 'كمية المنتج يجب أن تكون أكبر من صفر',
            'selectedProducts.*.unit_cost.required' => 'سعر الوحدة للمنتج مطلوب',
            'selectedProducts.*.total_cost.required' => 'إجمالي تكلفة المنتج مطلوب',
            'selectedProducts.*.cost_percentage.required' => 'نسبة التكلفة مطلوبة',
            'selectedProducts.*.cost_percentage.max' => 'نسبة التكلفة لا يمكن أن تزيد عن 100%',

            'additionalExpenses.*.description.required_with' => 'وصف المصروف مطلوب',
            'additionalExpenses.*.description.min' => 'وصف المصروف يجب أن يكون 3 أحرف على الأقل',
            'additionalExpenses.*.amount.required_with' => 'مبلغ المصروف مطلوب',
            'additionalExpenses.*.amount.min' => 'مبلغ المصروف يجب أن يكون أكبر من صفر',
            'additionalExpenses.*.account_id.required_with' => 'حساب المصروف مطلوب',
            'additionalExpenses.*.account_id.exists' => 'حساب المصروف غير صحيح',
        ];

        // 4. تشغيل التحقق من صحة البيانات
        $validator = validator($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();

            $component->dispatch('error-swal', [
                'title' => 'خطأ في البيانات!',
                'text' => $firstError,
                'icon' => 'error'
            ]);

            return false;
        }

        // 5. تحققات إضافية مخصصة

        // التحقق من أن مجموع نسب التكلفة = 100%
        $totalPercentage = collect($component->selectedProducts)
            ->sum(fn($product) => (float)$product['cost_percentage']);

        if (abs($totalPercentage - 100) > 0.01) {
            $component->dispatch('error-swal', [
                'title' => 'خطأ في النسب!',
                'text' => 'مجموع نسب توزيع التكلفة يجب أن يساوي 100%. المجموع الحالي: ' . number_format($totalPercentage, 2) . '%',
                'icon' => 'error'
            ]);
            return false;
        }

        // التحقق من عدم تكرار المواد الخام
        $rawMaterialIds = collect($component->selectedRawMaterials)->pluck('item_id');
        if ($rawMaterialIds->count() !== $rawMaterialIds->unique()->count()) {
            $component->dispatch('error-swal', [
                'title' => 'خطأ في المواد الخام!',
                'text' => 'لا يمكن تكرار نفس المادة الخام أكثر من مرة',
                'icon' => 'error'
            ]);
            return false;
        }

        // التحقق من عدم تكرار المنتجات
        $productIds = collect($component->selectedProducts)->pluck('product_id');
        if ($productIds->count() !== $productIds->unique()->count()) {
            $component->dispatch('error-swal', [
                'title' => 'خطأ في المنتجات!',
                'text' => 'لا يمكن تكرار نفس المنتج أكثر من مرة',
                'icon' => 'error'
            ]);
            return false;
        }

        // التحقق من أن إجمالي التكلفة صحيح
        $calculatedTotal = $component->totalRawMaterialsCost + $component->totalAdditionalExpenses;
        if (abs($calculatedTotal - $component->totalManufacturingCost) > 0.01) {
            $component->dispatch('error-swal', [
                'title' => 'خطأ في الحسابات!',
                'text' => 'إجمالي تكلفة التصنيع غير صحيح. يجب أن يساوي مجموع تكلفة المواد الخام والمصاريف الإضافية',
                'icon' => 'error'
            ]);
            return false;
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
                'is_stock' => $isTemplate ? 0 : 1, // 0 للنموذج، 1 للفاتورة العادية
                'is_finance' => 0,
                'is_manager' => 0,
                'expected_time' => $component->actualTime ?? null,
                'is_journal' => $isTemplate ? 0 : 1, // 0 للنموذج، 1 للفاتورة العادية
                'pro_date' => $component->invoiceDate,
                'pro_value' => $component->totalManufacturingCost,
                'fat_net' => $component->totalManufacturingCost,
                'info' => $component->description,
                'user' => Auth::id(),

                'is_template' => $isTemplate ? 1 : 0, // إضافة هذا الحقل إذا كان موجوداً في الجدول
            ]);

            foreach ($component->selectedRawMaterials as $raw) {
                OperationItems::create([
                    'pro_tybe' => 59,
                    'detail_store' => $component->rawAccount,
                    'pro_id' => $operation->id,
                    'item_id' => $raw['item_id'],
                    'unit_id' => $raw['unit_id'] ?? null,
                    'qty_in' => 0,
                    'qty_out' => $isTemplate ? 0 : $raw['quantity'], // 0 للنموذج
                    'item_price' => $raw['unit_cost'],
                    'cost_price' => $raw['unit_cost'],
                    'detail_value' => $raw['total_cost'],
                    'is_stock' => $isTemplate ? 0 : 1, // 0 للنموذج
                ]);
            }

            foreach ($component->selectedProducts as $product) {

                $item = Item::find($product['product_id']);
                if ($item) {
                    // حساب المتوسط الجديد
                    $oldQuantity = $item->inventory->quantity ?? 0;
                    $oldAverage = $item->average_cost ?? 0;
                    $newQuantity = $product['quantity'];
                    $newCost = $product['unit_cost'];

                    // الصيغة: (الكمية القديمة * المتوسط القديم) + (الكمية الجديدة * التكلفة الجديدة) / (الكمية القديمة + الجديدة)
                    $totalCost = ($oldQuantity * $oldAverage) + ($newQuantity * $newCost);
                    $totalQuantity = $oldQuantity + $newQuantity;

                    $newAverage = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;

                    // تحديث سعر الشراء المتوسط
                    $item->update([
                        'average_cost' => $newAverage
                    ]);
                }
                OperationItems::create([
                    'pro_tybe' => 59,
                    'detail_store' => $component->productAccount,
                    'pro_id' => $operation->id,
                    'item_id' => $product['product_id'],
                    'unit_id' => $product['unit_id'] ?? null,
                    'qty_in' => $isTemplate ? 0 : $product['quantity'], // 0 للنموذج
                    'qty_out' => 0,
                    'item_price' => $product['unit_cost'],
                    'cost_price' => $product['unit_cost'],
                    'detail_value' => $product['total_cost'],
                    'is_stock' => $isTemplate ? 0 : 1, // 0 للنموذج
                ]);
            }
            if (!$isTemplate) {
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
            }
            DB::commit();

            $component->dispatch('success-swal', title: 'تم الحفظ!', text: 'تم حفظ فاتورة التصنيع بنجاح.', icon: 'success');
            return $operation->id;
        } catch (\Exception $e) {
            DB::rollBack();
            $component->dispatch('error-swal', title: 'خطأ !', text: 'حدث خطا اثناء الحفظ.', icon: 'error');
            return back()->withInput();
        }
    }

    public function updateManufacturingInvoice($component, $invoiceId)
    {
        // try {
        //     DB::beginTransaction();

        // 1. العثور على الفاتورة القديمة
        $operation = OperHead::find($invoiceId);
        if (!$operation) {
            $component->dispatch('error-swal', [
                'title' => 'خطأ!',
                'text' => 'الفاتورة غير موجودة.',
                'icon' => 'error'
            ]);
            return false;
        }

        // 2. حذف البيانات القديمة
        OperationItems::where('pro_id', $operation->id)->delete();
        Expense::where('op_id', $operation->id)->delete();
        JournalHead::where('op_id', $operation->id)->delete();

        // 3. تحديث بيانات الفاتورة الرئيسية
        $operation->update([
            'pro_id' => $component->pro_id,
            'pro_type' => 59,
            'acc1' => $component->rawAccount,
            'acc2' => $component->productAccount,
            'emp_id' => $component->employee,
            'store_id' => $component->productAccount,
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

        // 4. إنشاء بيانات المواد الخام الجديدة
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

        // 5. إنشاء بيانات المنتجات الجديدة
        foreach ($component->selectedProducts as $product) {
            $item = Item::find($product['product_id']);
            if ($item) {
                $oldQuantity = $item->inventory->quantity ?? 0;
                $oldAverage = $item->average_cost ?? 0;
                $newQuantity = $product['quantity'];
                $newCost = $product['unit_cost'];

                $totalCost = ($oldQuantity * $oldAverage) + ($newQuantity * $newCost);
                $totalQuantity = $oldQuantity + $newQuantity;

                $newAverage = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;

                $item->update([
                    'average_cost' => $newAverage
                ]);
            }

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

        // if ($component->totalAdditionalExpenses > 0 && !empty($component->additionalExpenses)) {
        foreach ($component->additionalExpenses as $expense) {
            Expense::create([
                'title' => $component->description ?: 'فاتورة تصنيع',
                'pro_type' => 59,
                'op_id' => $operation->id,
                'amount' => $expense['amount'],
                'account_id' => $expense['account_id'],
                'description' => 'مصروف إضافي: ' . ($expense['description'] ?? 'غير محدد') . ' - فاتورة: ' . $component->pro_id,
            ]);
        }
        // }

        // 6. إنشاء قيود المحاسبة
        $journalId = (JournalHead::max('journal_id') ?? 0) + 1;
        $totalRaw = $component->totalRawMaterialsCost;
        $totalExpenses = $component->totalAdditionalExpenses;

        // قيد صرف المواد الخام
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

        // قيد المصروفات الإضافية
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

        // قيد إنتاج المنتجات
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

        $component->isEditing = false;
        $component->originalInvoiceId = null;
        $component->dispatch('success-swal', title: 'تم التعديل!', text: 'تم تعديل فاتورة التصنيع بنجاح.', icon: 'success');
        return $operation->id;
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     $component->dispatch('error-swal', title: 'خطأ!', text: 'حدث خطأ أثناء تعديل الفاتورة: ' . $e->getMessage(), icon: 'error');
        //     return false;
        // }
    }
}
