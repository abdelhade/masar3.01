<?php

namespace Modules\POS\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\POS\app\Models\CashierTransaction;
use RealRashid\SweetAlert\Facades\Alert;

class POSController extends Controller
{
    /**
     * عرض واجهة POS الرئيسية
     */
    public function index()
    {
        // التحقق من صلاحية الوصول لنظام POS
        if (! auth()->check() || ! auth()->user()->can('view POS System')) {
            abort(403, 'ليس لديك صلاحية لاستخدام نظام نقاط البيع.');
        }

        // جلب المعاملات الأخيرة لهذا المستخدم (اختياري)
        $recentTransactions = OperHead::with(['acc1Head', 'acc2Head', 'employee'])
            ->where('pro_type', 10) // فواتير مبيعات فقط
            ->where('user', auth()->id())
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('pos::index', compact('recentTransactions'));
    }

    /**
     * إنشاء معاملة POS جديدة
     */
    public function create()
    {
        // التحقق من صلاحية إنشاء معاملات POS
        if (! auth()->check() || ! auth()->user()->can('create POS Transaction')) {
            abort(403, 'ليس لديك صلاحية لإنشاء معاملات نقاط البيع.');
        }

        // جلب البيانات المطلوبة
        $nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $clientsAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1103%')
            ->select('id', 'aname')
            ->get();

        $stores = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1104%')
            ->select('id', 'aname')
            ->get();

        $employees = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->select('id', 'aname')
            ->get();

        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', 1)
            ->select('id', 'aname')
            ->get();

        // جلب التصنيفات
        $categories = \DB::table('note_details')
            ->join('notes', 'note_details.note_id', '=', 'notes.id')
            ->select('note_details.id', 'note_details.name', 'notes.name as parent_name')
            ->where('note_details.note_id', '=', 2)
            ->get();

        // جلب الأصناف
        $items = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->where('is_active', 1)
            ->take(50)
            ->get();

        // تحضير بيانات الأصناف للـ JavaScript (لتجنب AJAX calls)
        $itemsData = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'units' => $item->units->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'name' => $unit->name,
                        'value' => $unit->pivot->u_val ?? 1,
                    ];
                })->toArray(),
                'prices' => $item->prices->map(function ($price) {
                    return [
                        'id' => $price->id,
                        'name' => $price->name,
                        'value' => $price->pivot->price ?? 0,
                    ];
                })->toArray(),
            ];
        })->keyBy('id');

        return view('pos::create', compact(
            'nextProId',
            'clientsAccounts',
            'stores',
            'employees',
            'cashAccounts',
            'categories',
            'items',
            'itemsData'
        ));
    }

    /**
     * البحث عن الأصناف (AJAX)
     */
    public function searchItems(Request $request)
    {
        $searchTerm = $request->input('term', '');

        if (strlen($searchTerm) < 2) {
            return response()->json(['items' => []]);
        }

        $items = Item::where('is_active', 1)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('code', 'like', "%{$searchTerm}%");
            })
            ->with(['units', 'prices'])
            ->take(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                ];
            });

        return response()->json(['items' => $items]);
    }

    /**
     * البحث عن الأصناف بالباركود (AJAX)
     */
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->input('barcode', '');

        if (empty($barcode)) {
            return response()->json(['items' => []]);
        }

        // البحث الدقيق بالباركود أولاً
        $item = Item::where('is_active', 1)
            ->where('code', $barcode)
            ->with(['units', 'prices'])
            ->first();

        if ($item) {
            return response()->json([
                'items' => [[
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                ]],
                'exact_match' => true,
            ]);
        }

        // إذا لم يوجد تطابق دقيق، البحث الجزئي
        $items = Item::where('is_active', 1)
            ->where('code', 'like', "%{$barcode}%")
            ->with(['units', 'prices'])
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                ];
            });

        return response()->json([
            'items' => $items,
            'exact_match' => false,
        ]);
    }

    /**
     * جلب تفاصيل الصنف (AJAX)
     */
    public function getItemDetails($id)
    {
        $item = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->where('id', $id)
            ->where('is_active', 1)
            ->first();

        if (! $item) {
            return response()->json(['error' => 'الصنف غير موجود'], 404);
        }

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code,
            'units' => $item->units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'value' => $unit->pivot->u_val ?? 1,
                ];
            }),
            'prices' => $item->prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'name' => $price->name,
                    'value' => $price->pivot->price ?? 0,
                ];
            }),
        ]);
    }

    /**
     * جلب أصناف التصنيف (AJAX)
     */
    public function getCategoryItems($categoryId)
    {
        $categoryName = \DB::table('note_details')
            ->where('id', $categoryId)
            ->value('name');

        if (! $categoryName) {
            return response()->json(['items' => []]);
        }

        $items = \DB::table('item_notes')
            ->join('items', 'item_notes.item_id', '=', 'items.id')
            ->where('item_notes.note_detail_name', $categoryName)
            ->where('items.is_active', 1)
            ->select('items.id', 'items.name', 'items.code')
            ->orderBy('items.name')
            ->get();

        return response()->json(['items' => $items]);
    }

    /**
     * حفظ الفاتورة (AJAX)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'customer_id' => 'nullable|exists:acc_head,id',
            'store_id' => 'nullable|exists:acc_head,id',
            'cash_account_id' => 'nullable|exists:acc_head,id',
            'employee_id' => 'nullable|exists:acc_head,id',
            'payment_method' => 'nullable|string',
            'cash_amount' => 'nullable|numeric|min:0',
            'card_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'table_id' => 'nullable|integer',
            'local_id' => 'nullable|uuid', // UUID من IndexedDB
        ]);

        try {
            DB::beginTransaction();

            // حساب المبالغ
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }
            $discount = 0;
            $additional = 0;
            $total = $subtotal - $discount + $additional;
            $paidAmount = ($validated['cash_amount'] ?? 0) + ($validated['card_amount'] ?? 0);

            // جلب رقم الفاتورة التالي
            $nextProId = OperHead::max('pro_id') + 1 ?? 1;

            // تحديد الحسابات
            $customerId = $validated['customer_id'] ?? null;
            $cashAccountId = $validated['cash_account_id'] ?? null;
            $storeId = $validated['store_id'] ?? null;
            $employeeId = $validated['employee_id'] ?? null;
            $branchId = Auth::user()->branch_id ?? 1;

            // إنشاء رأس المعاملة (OperHead)
            $operHead = OperHead::create([
                'pro_id' => $nextProId,
                'pro_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'pro_type' => 102, // فاتورة كاشير
                'acc1' => $customerId, // العميل
                'acc2' => $cashAccountId ?? $storeId, // الصندوق أو المخزن
                'store_id' => $storeId,
                'emp_id' => $employeeId,
                'fat_total' => $subtotal,
                'fat_disc' => $discount,
                'fat_disc_per' => 0,
                'fat_plus' => $additional,
                'fat_plus_per' => 0,
                'fat_net' => $total,
                'pro_value' => $total,
                'paid_from_client' => $paidAmount,
                'info' => $validated['notes'] ?? 'فاتورة كاشير',
                'details' => $validated['notes'] ?? 'فاتورة كاشير',
                'isdeleted' => 0,
                'is_stock' => 1, // معاملة مخزنية
                'is_finance' => 1, // معاملة مالية
                'is_journal' => 1, // تحتاج قيد محاسبي
                'journal_type' => 2,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // إنشاء تفاصيل الأصناف (OperationItems)
            foreach ($validated['items'] as $item) {
                $itemModel = Item::find($item['id']);
                $unitId = $item['unit_id'] ?? $itemModel->units()->first()?->id ?? null;
                $quantity = $item['quantity'];
                $price = $item['price'];
                $totalValue = $quantity * $price;

                DB::table('operation_items')->insert([
                    'pro_id' => $operHead->id,
                    'item_id' => $item['id'],
                    'unit_id' => $unitId,
                    'qty_in' => 0,
                    'qty_out' => $quantity, // كمية خارجة (مبيعات)
                    'item_price' => $price,
                    'cost_price' => 0, // سيتم حسابه لاحقاً
                    'current_stock_value' => 0,
                    'item_discount' => 0,
                    'additional' => 0,
                    'detail_value' => $totalValue,
                    'profit' => 0,
                    'notes' => null,
                    'is_stock' => 1,
                    'isdeleted' => 0,
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // إنشاء القيد المحاسبي (JournalHead)
            $journalId = JournalHead::max('journal_id') + 1;
            $journalHead = JournalHead::create([
                'journal_id' => $journalId,
                'total' => $total,
                'op_id' => $operHead->id,
                'pro_type' => 102, // فاتورة كاشير
                'date' => now()->format('Y-m-d'),
                'details' => 'قيد فاتورة كاشير رقم ' . $nextProId,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // إنشاء تفاصيل القيد المحاسبي (JournalDetails)
            if ($customerId) {
                // مدين - العميل
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $customerId,
                    'debit' => $total,
                    'credit' => 0,
                    'type' => 0,
                    'info' => 'مدين - عميل',
                    'op_id' => $operHead->id,
                    'isdeleted' => 0,
                    'branch_id' => $branchId,
                ]);
            }

            // دائن - الصندوق أو المخزن
            $creditAccount = $cashAccountId ?? $storeId;
            if ($creditAccount) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $creditAccount,
                    'debit' => 0,
                    'credit' => $total,
                    'type' => 1,
                    'info' => 'دائن - ' . ($cashAccountId ? 'صندوق' : 'مخزن'),
                    'op_id' => $operHead->id,
                    'isdeleted' => 0,
                    'branch_id' => $branchId,
                ]);
            }

            // حفظ في جدول cashier_transactions (للربط والمزامنة)
            $cashierTransaction = CashierTransaction::create([
                'local_id' => $validated['local_id'] ?? null,
                'server_id' => $operHead->id, // ربط بـ operhead
                'pro_type_id' => 102, // فاتورة كاشير
                'pro_id' => $nextProId,
                'pro_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'customer_id' => $customerId,
                'store_id' => $storeId,
                'cash_account_id' => $cashAccountId,
                'employee_id' => $employeeId,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_percentage' => 0,
                'additional' => $additional,
                'additional_percentage' => 0,
                'total' => $total,
                'payment_method' => $validated['payment_method'] ?? null,
                'cash_amount' => $validated['cash_amount'] ?? 0,
                'card_amount' => $validated['card_amount'] ?? 0,
                'paid_amount' => $paidAmount,
                'notes' => $validated['notes'] ?? null,
                'table_id' => $validated['table_id'] ?? null,
                'items' => $validated['items'],
                'sync_status' => 'synced', // تم الحفظ مباشرة على السيرفر
                'synced_at' => now(),
                'user_id' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم الحفظ بنجاح',
                'transaction_id' => $operHead->id,
                'invoice_number' => $nextProId,
                'server_id' => $cashierTransaction->id, // server_id = id من cashier_transactions
                'operhead_id' => $operHead->id, // id من operhead
                'local_id' => $cashierTransaction->local_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('POS Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * مزامنة المعاملات المعلقة (للـ offline sync)
     */
    public function syncTransactions(Request $request)
    {
        $transactions = $request->input('transactions', []);

        if (empty($transactions)) {
            return response()->json(['success' => false, 'message' => 'لا توجد معاملات للمزامنة'], 400);
        }

        $synced = [];
        $failed = [];

        foreach ($transactions as $transaction) {
            try {
                DB::beginTransaction();

                // التحقق من وجود المعاملة مسبقاً (بناءً على local_id)
                $existing = null;
                if (isset($transaction['local_id'])) {
                    $existing = CashierTransaction::where('local_id', $transaction['local_id'])->first();
                }

                if ($existing && $existing->sync_status === 'synced') {
                    // المعاملة موجودة ومزامنة بالفعل
                    $synced[] = [
                        'local_id' => $existing->local_id,
                        'server_id' => $existing->id,
                    ];
                    DB::commit();
                    continue;
                }

                // حساب المبالغ
                $subtotal = 0;
                $items = $transaction['items'] ?? [];
                foreach ($items as $item) {
                    $subtotal += ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                }
                $discount = $transaction['discount'] ?? 0;
                $additional = $transaction['additional'] ?? 0;
                $total = $subtotal - $discount + $additional;
                $paidAmount = ($transaction['cash_amount'] ?? 0) + ($transaction['card_amount'] ?? 0);

                // جلب رقم الفاتورة التالي
                $nextProId = OperHead::max('pro_id') + 1 ?? 1;

                if ($existing) {
                    // تحديث المعاملة الموجودة
                    $existing->update([
                        'pro_id' => $nextProId,
                        'pro_date' => $transaction['pro_date'] ?? now()->format('Y-m-d'),
                        'accural_date' => $transaction['pro_date'] ?? now()->format('Y-m-d'),
                        'customer_id' => $transaction['customer_id'] ?? null,
                        'store_id' => $transaction['store_id'] ?? null,
                        'cash_account_id' => $transaction['cash_account_id'] ?? null,
                        'employee_id' => $transaction['employee_id'] ?? null,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'additional' => $additional,
                        'total' => $total,
                        'payment_method' => $transaction['payment_method'] ?? null,
                        'cash_amount' => $transaction['cash_amount'] ?? 0,
                        'card_amount' => $transaction['card_amount'] ?? 0,
                        'paid_amount' => $paidAmount,
                        'notes' => $transaction['notes'] ?? null,
                        'table_id' => $transaction['table'] ?? null,
                        'items' => $items,
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_error' => null,
                    ]);
                    $savedTransaction = $existing;
                } else {
                    // إنشاء معاملة جديدة
                    $savedTransaction = CashierTransaction::create([
                        'local_id' => $transaction['local_id'] ?? null,
                        'pro_type_id' => 102, // فاتورة كاشير
                        'pro_id' => $nextProId,
                        'pro_date' => $transaction['pro_date'] ?? now()->format('Y-m-d'),
                        'accural_date' => $transaction['pro_date'] ?? now()->format('Y-m-d'),
                        'customer_id' => $transaction['customer_id'] ?? null,
                        'store_id' => $transaction['store_id'] ?? null,
                        'cash_account_id' => $transaction['cash_account_id'] ?? null,
                        'employee_id' => $transaction['employee_id'] ?? null,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'discount_percentage' => 0,
                        'additional' => $additional,
                        'additional_percentage' => 0,
                        'total' => $total,
                        'payment_method' => $transaction['payment_method'] ?? null,
                        'cash_amount' => $transaction['cash_amount'] ?? 0,
                        'card_amount' => $transaction['card_amount'] ?? 0,
                        'paid_amount' => $paidAmount,
                        'notes' => $transaction['notes'] ?? null,
                        'table_id' => $transaction['table'] ?? null,
                        'items' => $items,
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'user_id' => Auth::id(),
                        'branch_id' => Auth::user()->branch_id ?? 1,
                    ]);
                }

                DB::commit();

                $synced[] = [
                    'local_id' => $savedTransaction->local_id,
                    'server_id' => $savedTransaction->id, // server_id = id من cashier_transactions
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $failed[] = [
                    'local_id' => $transaction['local_id'] ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
            'failed' => $failed,
            'message' => 'تمت المزامنة بنجاح',
        ]);
    }

    /**
     * عرض معاملة POS محددة
     */
    public function show($id)
    {
        $transaction = OperHead::with(['operationItems.item', 'acc1Head', 'acc2Head', 'employee'])
            ->where('pro_type', 10) // فواتير مبيعات فقط
            ->findOrFail($id);

        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('view POS Transaction')) {
            abort(403, 'ليس لديك صلاحية لعرض معاملات نقاط البيع.');
        }

        return view('pos::show', compact('transaction'));
    }

    /**
     * طباعة فاتورة POS
     */
    public function print($operation_id)
    {
        $operation = OperHead::with('operationItems.item')->findOrFail($operation_id);

        // التحقق من أن هذه معاملة POS (فاتورة مبيعات)
        if ($operation->pro_type !== 10) {
            abort(404, 'المعاملة المطلوبة غير موجودة.');
        }

        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('print POS Transaction')) {
            abort(403, 'ليس لديك صلاحية لطباعة فواتير نقاط البيع.');
        }

        $acc1List = AccHead::where('id', $operation->acc1)->get();
        $acc2List = AccHead::where('id', $operation->acc2)->get();
        $employees = Employee::where('id', $operation->emp_id)->get();
        $items = Item::whereIn('id', $operation->operationItems->pluck('item_id'))->get();

        return view('pos::print', [
            'pro_id' => $operation->pro_id,
            'pro_date' => $operation->pro_date,
            'accural_date' => $operation->accural_date,
            'serial_number' => $operation->pro_serial,
            'acc1_id' => $operation->acc1,
            'acc2_id' => $operation->acc2,
            'emp_id' => $operation->emp_id,
            'type' => $operation->pro_type,
            'acc1List' => $acc1List,
            'acc2List' => $acc2List,
            'employees' => $employees,
            'items' => $items,
            'invoiceItems' => $operation->operationItems->map(function ($item) {
                $unit = \App\Models\Unit::find($item->unit_id);

                return [
                    'item_id' => $item->item_id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->qty_out, // في POS نستخدم qty_out للمبيعات
                    'price' => $item->item_price,
                    'discount' => $item->item_discount,
                    'sub_value' => $item->detail_value,
                    'available_units' => collect([$unit]),
                ];
            })->toArray(),
            'subtotal' => $operation->fat_total,
            'discount_percentage' => $operation->fat_disc_per,
            'discount_value' => $operation->fat_disc,
            'additional_percentage' => $operation->fat_plus_per,
            'additional_value' => $operation->fat_plus,
            'total_after_additional' => $operation->fat_net,
            'received_from_client' => $operation->paid_from_client,
            'notes' => $operation->info,
        ]);
    }

    /**
     * حذف معاملة POS
     */
    public function destroy($id)
    {
        $operation = OperHead::findOrFail($id);

        // التحقق من أن هذه معاملة POS (فاتورة مبيعات)
        if ($operation->pro_type !== 10) {
            abort(404, 'المعاملة المطلوبة غير موجودة.');
        }

        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('delete POS Transaction')) {
            abort(403, 'ليس لديك صلاحية لحذف معاملات نقاط البيع.');
        }

        try {
            // حذف جميع العناصر المرتبطة من operation_items
            $operation->operationItems()->delete();

            // حذف قيود اليومية المرتبطة بـ op_id
            JournalDetail::where('op_id', $operation->id)->delete();
            JournalHead::where('op_id', $operation->id)->orWhere('op2', $operation->id)->delete();

            // حذف أي سند آلي مرتبط بـ op2
            $autoVoucher = OperHead::where('op2', $operation->id)->where('is_journal', 1)->where('is_stock', 0)->first();
            if ($autoVoucher) {
                // حذف قيوده اليومية
                JournalDetail::where('op_id', $autoVoucher->id)->delete();
                JournalHead::where('op_id', $autoVoucher->id)->orWhere('op2', $autoVoucher->id)->delete();
                // حذف السند نفسه
                $autoVoucher->delete();
            }

            // حذف المعاملة نفسها
            $operation->delete();

            Alert::toast('تم حذف المعاملة وسنداتها بنجاح.', 'success');

            return redirect()->route('pos.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء حذف المعاملة: '.$e->getMessage(), 'error');

            return redirect()->back();
        }
    }

    /**
     * تقارير POS
     */
    public function reports()
    {
        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('view POS Reports')) {
            abort(403, 'ليس لديك صلاحية لعرض تقارير نقاط البيع.');
        }

        // إحصائيات اليوم
        $todayStats = [
            'total_sales' => OperHead::where('pro_type', 10)
                ->whereDate('created_at', today())
                ->sum('fat_net'),
            'transactions_count' => OperHead::where('pro_type', 10)
                ->whereDate('created_at', today())
                ->count(),
            'items_sold' => OperHead::where('pro_type', 10)
                ->whereDate('created_at', today())
                ->withSum('operationItems', 'qty_out')
                ->get()
                ->sum('operation_items_sum_qty_out') ?? 0,
        ];

        return view('pos::reports', compact('todayStats'));
    }
}
