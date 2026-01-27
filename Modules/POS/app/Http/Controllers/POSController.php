<?php

namespace Modules\POS\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use App\Models\Setting;
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
            ->where('pro_type', 102) // فواتير كاشير
            ->where('user', auth()->id())
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // حساب إحصائيات اليوم
        $todayStats = [
            'total_sales' => OperHead::where('pro_type', 102)
                ->whereDate('created_at', today())
                ->sum('fat_net') ?? 0,
            'transactions_count' => OperHead::where('pro_type', 102)
                ->whereDate('created_at', today())
                ->count(),
            'items_sold' => OperHead::where('pro_type', 102)
                ->whereDate('created_at', today())
                ->withSum('operationItems', 'qty_out')
                ->get()
                ->sum('operation_items_sum_qty_out') ?? 0,
        ];

        return view('pos::index', compact(
            'recentTransactions',
            'todayStats'
        ));
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

        $bankAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1102%')
            ->select('id', 'aname')
            ->get();

        // جلب حسابات المصروفات (للمصروفات النثرية)
        $expenseAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where(function ($q) {
                $q->where('code', 'like', '5101%') // مصروفات تشغيلية
                    ->orWhere('code', 'like', '5102%') // مصروفات إدارية
                    ->orWhere('code', 'like', '5103%') // مصروفات تسويقية
                    ->orWhere('code', 'like', '5104%'); // مصروفات أخرى
            })
            ->select('id', 'aname')
            ->orderBy('code')
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

        // جلب الباركودات للأصناف
        $itemIds = $items->pluck('id');
        $barcodes = Barcode::whereIn('item_id', $itemIds)
            ->where('isdeleted', 0)
            ->select('item_id', 'unit_id', 'barcode')
            ->get()
            ->groupBy('item_id');

        // تحضير بيانات الأصناف للـ JavaScript (لتجنب AJAX calls)
        $itemsData = $items->map(function ($item) use ($barcodes) {
            $itemBarcodes = $barcodes->get($item->id, collect());

            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'is_weight_scale' => $item->is_weight_scale ?? false,
                'scale_plu_code' => $item->scale_plu_code ?? null,
                'barcodes' => $itemBarcodes->map(function ($barcode) {
                    return [
                        'barcode' => $barcode->barcode,
                        'unit_id' => $barcode->unit_id,
                    ];
                })->toArray(),
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

        // تحضير البيانات الأولية للمنتجات (للعرض الأولي)
        $initialProductsData = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
            ];
        })->values();

        return view('pos::create', compact(
            'nextProId',
            'clientsAccounts',
            'stores',
            'employees',
            'cashAccounts',
            'bankAccounts',
            'expenseAccounts',
            'categories',
            'items',
            'itemsData',
            'initialProductsData'
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
     * البحث عن الأصناف بالباركود (AJAX) - محسّن للسرعة
     */
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->input('barcode', '');

        if (empty($barcode)) {
            return response()->json(['items' => []]);
        }

        // البحث في جدول الباركودات أولاً (البحث الدقيق)
        $barcodeRecord = Barcode::where('barcode', $barcode)
            ->where('isdeleted', 0)
            ->with(['item' => function ($q) {
                $q->where('is_active', 1)
                    ->select('id', 'name', 'code');
            }])
            ->first();

        if ($barcodeRecord && $barcodeRecord->item) {
            return response()->json([
                'items' => [[
                    'id' => $barcodeRecord->item->id,
                    'name' => $barcodeRecord->item->name,
                    'code' => $barcodeRecord->item->code,
                    'unit_id' => $barcodeRecord->unit_id,
                ]],
                'exact_match' => true,
            ]);
        }

        // إذا لم يوجد تطابق دقيق، البحث الجزئي في الباركودات
        $barcodeRecords = Barcode::where('barcode', 'like', "%{$barcode}%")
            ->where('isdeleted', 0)
            ->with(['item' => function ($q) {
                $q->where('is_active', 1)
                    ->select('id', 'name', 'code');
            }])
            ->take(10)
            ->get()
            ->filter(function ($barcodeRecord) {
                return $barcodeRecord->item !== null;
            })
            ->map(function ($barcodeRecord) {
                return [
                    'id' => $barcodeRecord->item->id,
                    'name' => $barcodeRecord->item->name,
                    'code' => $barcodeRecord->item->code,
                    'unit_id' => $barcodeRecord->unit_id,
                ];
            });

        return response()->json([
            'items' => $barcodeRecords,
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
            'is_weight_scale' => $item->is_weight_scale ?? false,
            'scale_plu_code' => $item->scale_plu_code ?? null,
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
     * عرض شاشة فحص السعر بالباركود
     */
    public function priceCheck()
    {
        // التحقق من صلاحية الوصول
        if (! auth()->check() || ! auth()->user()->can('view POS System')) {
            abort(403, 'ليس لديك صلاحية لاستخدام شاشة فحص السعر.');
        }

        return view('pos::price-check');
    }

    /**
     * جلب بيانات السعر بالباركود (AJAX)
     */
    public function getPriceByBarcode($barcode)
    {
        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'الباركود مطلوب',
            ], 400);
        }

        // البحث في جدول الباركودات
        $barcodeRecord = Barcode::where('barcode', $barcode)
            ->where('isdeleted', 0)
            ->with(['item' => function ($q) {
                $q->where('is_active', 1);
            }])
            ->first();

        if (! $barcodeRecord || ! $barcodeRecord->item) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على صنف بهذا الباركود',
            ], 404);
        }

        $item = $barcodeRecord->item;

        // جلب الوحدات والأسعار
        $item->load(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices']);

        // تحضير بيانات الوحدات
        $units = $item->units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'value' => $unit->pivot->u_val ?? 1,
            ];
        });

        // تحضير بيانات الأسعار لكل وحدة
        $pricesByUnit = [];
        foreach ($item->units as $unit) {
            $unitPrices = [];
            foreach ($item->prices as $price) {
                // البحث عن السعر لهذه الوحدة في جدول item_prices
                $itemPrice = \DB::table('item_prices')
                    ->where('item_id', $item->id)
                    ->where('price_id', $price->id)
                    ->where('unit_id', $unit->id)
                    ->first();

                if ($itemPrice) {
                    $unitPrices[] = [
                        'id' => $price->id,
                        'name' => $price->name,
                        'price' => (float) $itemPrice->price,
                        'discount' => (float) ($itemPrice->discount ?? 0),
                        'tax_rate' => (float) ($itemPrice->tax_rate ?? 0),
                    ];
                }
            }
            $pricesByUnit[] = [
                'unit_id' => $unit->id,
                'unit_name' => $unit->name,
                'prices' => $unitPrices,
            ];
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'barcode' => $barcode,
                'unit_id' => $barcodeRecord->unit_id,
                'units' => $units,
                'prices_by_unit' => $pricesByUnit,
            ],
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
            'bank_account_id' => 'nullable|exists:acc_head,id',
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
            $bankAccountId = $validated['bank_account_id'] ?? null;
            $storeId = $validated['store_id'] ?? null;
            $employeeId = $validated['employee_id'] ?? null;
            $branchId = Auth::user()->branch_id ?? 1;

            // تحديد حساب الدفع (صندوق للدفع النقدي، بنك للدفع بالبطاقة)
            $paymentAccountId = null;
            $paymentMethod = $validated['payment_method'] ?? 'cash';
            if ($paymentMethod === 'cash' || $paymentMethod === 'mixed') {
                $paymentAccountId = $cashAccountId ?? $storeId;
            } elseif ($paymentMethod === 'card') {
                $paymentAccountId = $bankAccountId ?? $storeId;
            } else {
                $paymentAccountId = $cashAccountId ?? $bankAccountId ?? $storeId;
            }

            // إنشاء رأس المعاملة (OperHead)
            $operHead = OperHead::create([
                'pro_id' => $nextProId,
                'pro_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'pro_type' => 102, // فاتورة كاشير
                'acc1' => $customerId, // العميل
                'acc2' => $paymentAccountId, // الصندوق أو البنك أو المخزن
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

            // ========== القيد الأول: قيد المبيعات ==========
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $salesJournalId = $lastJournalId + 1;
            $salesJournalHead = JournalHead::create([
                'journal_id' => $salesJournalId,
                'total' => $total,
                'op_id' => $operHead->id,
                'pro_type' => 102, // فاتورة كاشير
                'date' => now()->format('Y-m-d'),
                'details' => 'قيد مبيعات - فاتورة كاشير رقم '.$nextProId,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // مدين - العميل (للمبيعات)
            if ($customerId) {
                JournalDetail::create([
                    'journal_id' => $salesJournalId,
                    'account_id' => $customerId,
                    'debit' => $total,
                    'credit' => 0,
                    'type' => 0,
                    'info' => 'مدين - عميل (مبيعات)',
                    'op_id' => $operHead->id,
                    'isdeleted' => 0,
                    'branch' => $branchId,
                ]);
            }

            // دائن - حساب المبيعات (47)
            JournalDetail::create([
                'journal_id' => $salesJournalId,
                'account_id' => 47, // حساب المبيعات
                'debit' => 0,
                'credit' => $total,
                'type' => 1,
                'info' => 'دائن - مبيعات',
                'op_id' => $operHead->id,
                'isdeleted' => 0,
                'branch' => $branchId,
            ]);

            // ========== القيد الثاني: قيد الدفع ==========
            $cashAmount = $validated['cash_amount'] ?? 0;
            $cardAmount = $validated['card_amount'] ?? 0;
            $paymentMethod = $validated['payment_method'] ?? 'cash';

            if ($paidAmount > 0) {
                if ($paymentMethod === 'mixed' && $cashAmount > 0 && $cardAmount > 0) {
                    // الدفع المختلط - قيدين منفصلين
                    
                    // قيد الدفع النقدي
                    $cashJournalId = $salesJournalId + 1;
                    JournalHead::create([
                        'journal_id' => $cashJournalId,
                        'total' => $cashAmount,
                        'op_id' => $operHead->id,
                        'pro_type' => 102,
                        'date' => now()->format('Y-m-d'),
                        'details' => 'قيد دفع نقدي - فاتورة كاشير رقم '.$nextProId,
                        'user' => Auth::id(),
                        'branch_id' => $branchId,
                    ]);

                    // مدين - الصندوق
                    if ($cashAccountId) {
                        JournalDetail::create([
                            'journal_id' => $cashJournalId,
                            'account_id' => $cashAccountId,
                            'debit' => $cashAmount,
                            'credit' => 0,
                            'type' => 0,
                            'info' => 'مدين - صندوق (دفع نقدي)',
                            'op_id' => $operHead->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }

                    // دائن - العميل
                    if ($customerId) {
                        JournalDetail::create([
                            'journal_id' => $cashJournalId,
                            'account_id' => $customerId,
                            'debit' => 0,
                            'credit' => $cashAmount,
                            'type' => 1,
                            'info' => 'دائن - عميل (دفع نقدي)',
                            'op_id' => $operHead->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }

                    // قيد الدفع بالبطاقة
                    $cardJournalId = $cashJournalId + 1;
                    JournalHead::create([
                        'journal_id' => $cardJournalId,
                        'total' => $cardAmount,
                        'op_id' => $operHead->id,
                        'pro_type' => 102,
                        'date' => now()->format('Y-m-d'),
                        'details' => 'قيد دفع بالبطاقة - فاتورة كاشير رقم '.$nextProId,
                        'user' => Auth::id(),
                        'branch_id' => $branchId,
                    ]);

                    // مدين - البنك
                    if ($bankAccountId) {
                        JournalDetail::create([
                            'journal_id' => $cardJournalId,
                            'account_id' => $bankAccountId,
                            'debit' => $cardAmount,
                            'credit' => 0,
                            'type' => 0,
                            'info' => 'مدين - بنك (دفع بالبطاقة)',
                            'op_id' => $operHead->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }

                    // دائن - العميل
                    if ($customerId) {
                        JournalDetail::create([
                            'journal_id' => $cardJournalId,
                            'account_id' => $customerId,
                            'debit' => 0,
                            'credit' => $cardAmount,
                            'type' => 1,
                            'info' => 'دائن - عميل (دفع بالبطاقة)',
                            'op_id' => $operHead->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }
                } else {
                    // دفع واحد (نقدي أو بطاقة)
                    $paymentAccountId = null;
                    $paymentAmount = 0;
                    
                    if ($paymentMethod === 'cash' || ($paymentMethod === 'mixed' && $cashAmount > 0)) {
                        $paymentAccountId = $cashAccountId ?? $storeId;
                        $paymentAmount = $cashAmount > 0 ? $cashAmount : $paidAmount;
                    } elseif ($paymentMethod === 'card' || ($paymentMethod === 'mixed' && $cardAmount > 0)) {
                        $paymentAccountId = $bankAccountId ?? $storeId;
                        $paymentAmount = $cardAmount > 0 ? $cardAmount : $paidAmount;
                    }

                    if ($paymentAccountId && $paymentAmount > 0) {
                        $paymentJournalId = $salesJournalId + 1;
                        JournalHead::create([
                            'journal_id' => $paymentJournalId,
                            'total' => $paymentAmount,
                            'op_id' => $operHead->id,
                            'pro_type' => 102,
                            'date' => now()->format('Y-m-d'),
                            'details' => 'قيد دفع - فاتورة كاشير رقم '.$nextProId,
                            'user' => Auth::id(),
                            'branch_id' => $branchId,
                        ]);

                        // مدين - الصندوق أو البنك
                        JournalDetail::create([
                            'journal_id' => $paymentJournalId,
                            'account_id' => $paymentAccountId,
                            'debit' => $paymentAmount,
                            'credit' => 0,
                            'type' => 0,
                            'info' => 'مدين - '.($cashAccountId ? 'صندوق' : 'بنك'),
                            'op_id' => $operHead->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);

                        // دائن - العميل
                        if ($customerId) {
                            JournalDetail::create([
                                'journal_id' => $paymentJournalId,
                                'account_id' => $customerId,
                                'debit' => 0,
                                'credit' => $paymentAmount,
                                'type' => 1,
                                'info' => 'دائن - عميل (دفع)',
                                'op_id' => $operHead->id,
                                'isdeleted' => 0,
                                'branch' => $branchId,
                            ]);
                        }
                    }
                }
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
            \Log::error('POS Store Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * تعليق الفاتورة (Hold/Suspend Order)
     */
    public function holdOrder(Request $request)
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
            'bank_account_id' => 'nullable|exists:acc_head,id',
            'employee_id' => 'nullable|exists:acc_head,id',
            'payment_method' => 'nullable|string',
            'cash_amount' => 'nullable|numeric|min:0',
            'card_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'table_id' => 'nullable|integer',
            'local_id' => 'nullable|uuid',
        ]);

        try {
            // حساب المبالغ
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }
            $discount = 0;
            $additional = 0;
            $total = $subtotal - $discount + $additional;
            $paidAmount = ($validated['cash_amount'] ?? 0) + ($validated['card_amount'] ?? 0);

            $branchId = Auth::user()->branch_id ?? 1;

            // حفظ الفاتورة المعلقة في cashier_transactions فقط (بدون OperHead)
            $cashierTransaction = CashierTransaction::create([
                'local_id' => $validated['local_id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'pro_type_id' => 102, // فاتورة كاشير
                'pro_id' => null, // سيتم تعيينه عند الإكمال
                'pro_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'customer_id' => $validated['customer_id'] ?? null,
                'store_id' => $validated['store_id'] ?? null,
                'cash_account_id' => $validated['cash_account_id'] ?? null,
                'employee_id' => $validated['employee_id'] ?? null,
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
                'notes' => $validated['notes'] ?? 'فاتورة معلقة',
                'table_id' => $validated['table_id'] ?? null,
                'items' => $validated['items'],
                'status' => 'held', // حالة معلقة
                'held_at' => now(),
                'sync_status' => 'pending', // لم يتم المزامنة بعد
                'user_id' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تعليق الفاتورة بنجاح',
                'held_order_id' => $cashierTransaction->id,
                'local_id' => $cashierTransaction->local_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('POS Hold Order Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعليق الفاتورة: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب قائمة الفواتير المعلقة
     */
    public function getHeldOrders(Request $request)
    {
        try {
            $branchId = Auth::user()->branch_id ?? 1;

            $heldOrders = CashierTransaction::with(['customer:id,aname', 'store:id,aname', 'user:id,name'])
                ->held()
                ->where('branch_id', $branchId)
                ->orderBy('held_at', 'desc')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'local_id' => $order->local_id,
                        'total' => (float) $order->total,
                        'customer_name' => $order->customer->aname ?? 'عميل نقدي',
                        'store_name' => $order->store->aname ?? 'غير محدد',
                        'user_name' => $order->user->name ?? 'غير محدد',
                        'items_count' => count($order->items ?? []),
                        'held_at' => $order->held_at->format('Y-m-d H:i:s'),
                        'held_at_formatted' => $order->held_at->diffForHumans(),
                        'notes' => $order->notes,
                    ];
                });

            return response()->json([
                'success' => true,
                'held_orders' => $heldOrders,
                'count' => $heldOrders->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching held orders: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الفواتير المعلقة',
                'held_orders' => [],
            ], 500);
        }
    }

    /**
     * استدعاء فاتورة معلقة (Recall/Resume Order)
     */
    public function recallOrder($id)
    {
        try {
            $branchId = Auth::user()->branch_id ?? 1;

            $heldOrder = CashierTransaction::held()
                ->where('branch_id', $branchId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $heldOrder->id,
                    'local_id' => $heldOrder->local_id,
                    'customer_id' => $heldOrder->customer_id,
                    'store_id' => $heldOrder->store_id,
                    'cash_account_id' => $heldOrder->cash_account_id,
                    'bank_account_id' => null, // يمكن إضافته لاحقاً
                    'employee_id' => $heldOrder->employee_id,
                    'items' => $heldOrder->items,
                    'subtotal' => (float) $heldOrder->subtotal,
                    'discount' => (float) $heldOrder->discount,
                    'additional' => (float) $heldOrder->additional,
                    'total' => (float) $heldOrder->total,
                    'payment_method' => $heldOrder->payment_method ?? 'cash',
                    'cash_amount' => (float) $heldOrder->cash_amount,
                    'card_amount' => (float) $heldOrder->card_amount,
                    'notes' => $heldOrder->notes,
                    'table_id' => $heldOrder->table_id,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error recalling held order: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استدعاء الفاتورة: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * إكمال فاتورة معلقة (Complete Held Order)
     */
    public function completeHeldOrder(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $branchId = Auth::user()->branch_id ?? 1;

            $heldOrder = CashierTransaction::held()
                ->where('branch_id', $branchId)
                ->findOrFail($id);

            // استخدام نفس منطق store() لإنشاء OperHead والقيود المحاسبية
            $nextProId = OperHead::max('pro_id') + 1 ?? 1;

            $customerId = $heldOrder->customer_id;
            $cashAccountId = $heldOrder->cash_account_id;
            $storeId = $heldOrder->store_id;
            $employeeId = $heldOrder->employee_id;

            $paymentAccountId = $cashAccountId ?? $storeId;
            $paymentMethod = $heldOrder->payment_method ?? 'cash';

            // إنشاء رأس المعاملة (OperHead)
            $operHead = OperHead::create([
                'pro_id' => $nextProId,
                'pro_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'pro_type' => 102, // فاتورة كاشير
                'acc1' => $customerId,
                'acc2' => $paymentAccountId,
                'store_id' => $storeId,
                'emp_id' => $employeeId,
                'fat_total' => $heldOrder->subtotal,
                'fat_disc' => $heldOrder->discount,
                'fat_disc_per' => 0,
                'fat_plus' => $heldOrder->additional,
                'fat_plus_per' => 0,
                'fat_net' => $heldOrder->total,
                'pro_value' => $heldOrder->total,
                'paid_from_client' => $heldOrder->paid_amount,
                'info' => $heldOrder->notes ?? 'فاتورة كاشير (من فاتورة معلقة)',
                'details' => $heldOrder->notes ?? 'فاتورة كاشير (من فاتورة معلقة)',
                'isdeleted' => 0,
                'is_stock' => 1,
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // إنشاء تفاصيل الأصناف
            foreach ($heldOrder->items as $item) {
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
                    'qty_out' => $quantity,
                    'item_price' => $price,
                    'cost_price' => 0,
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

            // إنشاء القيد المحاسبي
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $journalId = $lastJournalId + 1;
            $journalHead = JournalHead::create([
                'journal_id' => $journalId,
                'total' => $heldOrder->total,
                'op_id' => $operHead->id,
                'pro_type' => 102,
                'date' => now()->format('Y-m-d'),
                'details' => 'قيد فاتورة كاشير رقم '.$nextProId.' (من فاتورة معلقة)',
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // إنشاء تفاصيل القيد المحاسبي
            if ($customerId) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $customerId,
                    'debit' => $heldOrder->total,
                    'credit' => 0,
                    'type' => 0,
                    'info' => 'مدين - عميل',
                    'op_id' => $operHead->id,
                    'isdeleted' => 0,
                    'branch' => $branchId,
                ]);
            }

            $creditAccount = $cashAccountId ?? $storeId;
            if ($creditAccount) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $creditAccount,
                    'debit' => 0,
                    'credit' => $heldOrder->total,
                    'type' => 1,
                    'info' => 'دائن - '.($cashAccountId ? 'صندوق' : 'مخزن'),
                    'op_id' => $operHead->id,
                    'isdeleted' => 0,
                    'branch' => $branchId,
                ]);
            }

            // تحديث الفاتورة المعلقة
            $heldOrder->update([
                'server_id' => $operHead->id,
                'pro_id' => $nextProId,
                'status' => 'completed',
                'completed_at' => now(),
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إكمال الفاتورة بنجاح',
                'transaction_id' => $operHead->id,
                'invoice_number' => $nextProId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('POS Complete Held Order Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إكمال الفاتورة: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف فاتورة معلقة
     */
    public function deleteHeldOrder($id)
    {
        try {
            $branchId = Auth::user()->branch_id ?? 1;

            $heldOrder = CashierTransaction::held()
                ->where('branch_id', $branchId)
                ->findOrFail($id);

            $heldOrder->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الفاتورة المعلقة بنجاح',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting held order: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الفاتورة: '.$e->getMessage(),
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
        $transaction = OperHead::with(['operationItems.item', 'operationItems.unit', 'acc1Head', 'acc2Head', 'employee', 'user'])
            ->where('pro_type', 102) // فواتير كاشير
            ->where('isdeleted', 0)
            ->findOrFail($id);

        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('view POS Transaction')) {
            abort(403, 'ليس لديك صلاحية لعرض معاملات نقاط البيع.');
        }

        return view('pos::show', compact('transaction'));
    }

    /**
     * تحرير معاملة POS
     */
    public function edit($id)
    {
        $transaction = OperHead::with(['operationItems.item', 'operationItems.unit'])
            ->where('pro_type', 102) // فواتير كاشير
            ->where('isdeleted', 0)
            ->findOrFail($id);

        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('edit POS Transaction')) {
            abort(403, 'ليس لديك صلاحية لتحرير معاملات نقاط البيع.');
        }

        // جلب البيانات المطلوبة
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

        // جلب الأصناف المستخدمة في المعاملة
        $items = Item::with(['units' => fn ($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->whereIn('id', $transaction->operationItems->pluck('item_id'))
            ->get();

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

        return view('pos::edit', compact(
            'transaction',
            'clientsAccounts',
            'stores',
            'employees',
            'cashAccounts',
            'items',
            'itemsData'
        ));
    }

    /**
     * تحديث معاملة POS
     */
    public function update(Request $request, $id)
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
        ]);

        try {
            DB::beginTransaction();

            $transaction = OperHead::where('pro_type', 102)
                ->where('isdeleted', 0)
                ->findOrFail($id);

            // حساب المبالغ
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }
            $discount = 0;
            $additional = 0;
            $total = $subtotal - $discount + $additional;
            $paidAmount = ($validated['cash_amount'] ?? 0) + ($validated['card_amount'] ?? 0);

            // تحديث رأس المعاملة
            $transaction->update([
                'acc1' => $validated['customer_id'] ?? $transaction->acc1,
                'acc2' => $validated['cash_account_id'] ?? $validated['store_id'] ?? $transaction->acc2,
                'store_id' => $validated['store_id'] ?? $transaction->store_id,
                'emp_id' => $validated['employee_id'] ?? $transaction->emp_id,
                'fat_total' => $subtotal,
                'fat_disc' => $discount,
                'fat_disc_per' => 0,
                'fat_plus' => $additional,
                'fat_plus_per' => 0,
                'fat_net' => $total,
                'pro_value' => $total,
                'paid_from_client' => $paidAmount,
                'info' => $validated['notes'] ?? $transaction->info,
                'details' => $validated['notes'] ?? $transaction->details,
            ]);

            // حذف الأصناف القديمة
            $transaction->operationItems()->delete();

            // إنشاء الأصناف الجديدة
            $branchId = Auth::user()->branch_id ?? 1;
            foreach ($validated['items'] as $item) {
                $itemModel = Item::find($item['id']);
                $unitId = $item['unit_id'] ?? $itemModel->units()->first()?->id ?? null;
                $quantity = $item['quantity'];
                $price = $item['price'];
                $totalValue = $quantity * $price;

                DB::table('operation_items')->insert([
                    'pro_id' => $transaction->id,
                    'item_id' => $item['id'],
                    'unit_id' => $unitId,
                    'qty_in' => 0,
                    'qty_out' => $quantity,
                    'item_price' => $price,
                    'cost_price' => 0,
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

            // تحديث القيد المحاسبي إذا كان موجوداً
            $journalHead = JournalHead::where('op_id', $transaction->id)->first();
            if ($journalHead) {
                $journalHead->update(['total' => $total]);

                // تحديث تفاصيل القيد
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();

                if ($validated['customer_id'] ?? $transaction->acc1) {
                    JournalDetail::create([
                        'journal_id' => $journalHead->journal_id,
                        'account_id' => $validated['customer_id'] ?? $transaction->acc1,
                        'debit' => $total,
                        'credit' => 0,
                        'type' => 0,
                        'info' => 'مدين - عميل',
                        'op_id' => $transaction->id,
                        'isdeleted' => 0,
                        'branch' => $branchId,
                    ]);
                }

                $creditAccount = $validated['cash_account_id'] ?? $validated['store_id'] ?? $transaction->acc2;
                if ($creditAccount) {
                    JournalDetail::create([
                        'journal_id' => $journalHead->journal_id,
                        'account_id' => $creditAccount,
                        'debit' => 0,
                        'credit' => $total,
                        'type' => 1,
                        'info' => 'دائن - '.($validated['cash_account_id'] ? 'صندوق' : 'مخزن'),
                        'op_id' => $transaction->id,
                        'isdeleted' => 0,
                        'branch' => $branchId,
                    ]);
                }
            }

            DB::commit();

            Alert::toast('تم تحديث المعاملة بنجاح', 'success');

            return redirect()->route('pos.show', $transaction->id);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('POS Update Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            Alert::toast('حدث خطأ أثناء التحديث: '.$e->getMessage(), 'error');

            return redirect()->back()->withInput();
        }
    }

    /**
     * طباعة فاتورة POS - 7.8 cm (80mm)
     */
    public function print($operation_id)
    {
        $operation = OperHead::with(['operationItems.item', 'operationItems.unit', 'acc1Head', 'acc2Head', 'user'])->findOrFail($operation_id);

        // التحقق من أن هذه معاملة POS (فاتورة كاشير)
        if ($operation->pro_type !== 102) {
            abort(404, 'المعاملة المطلوبة غير موجودة.');
        }

        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('print POS Transaction')) {
            abort(403, 'ليس لديك صلاحية لطباعة فواتير نقاط البيع.');
        }

        return view('pos::print', [
            'operation' => $operation,
            'pro_id' => $operation->pro_id,
            'pro_date' => $operation->pro_date,
            'accural_date' => $operation->accural_date,
            'serial_number' => $operation->pro_serial,
            'acc1List' => collect([$operation->acc1Head])->filter(),
            'acc2List' => collect([$operation->acc2Head])->filter(),
            'items' => Item::whereIn('id', $operation->operationItems->pluck('item_id'))->get(),
            'invoiceItems' => $operation->operationItems->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->qty_out,
                    'price' => $item->item_price,
                    'discount' => $item->item_discount ?? 0,
                    'sub_value' => $item->detail_value,
                    'item_name' => $item->item->name ?? 'غير محدد',
                    'unit_name' => $item->unit->name ?? 'قطعة',
                ];
            })->toArray(),
            'subtotal' => $operation->fat_total ?? 0,
            'discount_percentage' => $operation->fat_disc_per ?? 0,
            'discount_value' => $operation->fat_disc ?? 0,
            'additional_percentage' => $operation->fat_plus_per ?? 0,
            'additional_value' => $operation->fat_plus ?? 0,
            'total_after_additional' => $operation->fat_net ?? $operation->pro_value ?? 0,
            'received_from_client' => $operation->paid_from_client ?? 0,
            'notes' => $operation->info ?? $operation->details ?? '',
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
     * جلب رصيد العميل (AJAX)
     */
    public function getCustomerBalance($customerId)
    {
        try {
            $customer = AccHead::findOrFail($customerId);

            // حساب الرصيد من journal_details
            $balance = \DB::table('journal_details')
                ->where('account_id', $customerId)
                ->where('isdeleted', 0)
                ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as balance')
                ->value('balance') ?? 0;

            // إضافة الرصيد الابتدائي
            $startBalance = (float) ($customer->start_balance ?? 0);
            $totalBalance = $startBalance + (float) $balance;

            return response()->json([
                'success' => true,
                'balance' => $totalBalance,
                'customer_name' => $customer->aname,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'balance' => 0,
                'message' => 'حدث خطأ أثناء جلب الرصيد',
            ], 500);
        }
    }

    /**
     * جلب آخر 50 عملية POS (AJAX)
     */
    public function getRecentTransactions(Request $request)
    {
        try {
            $limit = $request->input('limit', 50);
            $branchId = Auth::user()->branch_id ?? 1;

            // جلب آخر 50 عملية POS (pro_type = 102 للكاشير)
            $transactions = OperHead::with(['acc1Head:id,aname', 'acc2Head:id,aname', 'user:id,name'])
                ->where('pro_type', 102) // فواتير كاشير
                ->where('isdeleted', 0)
                ->where('branch_id', $branchId)
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'pro_id' => $transaction->pro_id,
                        'pro_date' => $transaction->pro_date,
                        'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                        'customer_name' => $transaction->acc1Head->aname ?? 'غير محدد',
                        'store_name' => $transaction->acc2Head->aname ?? 'غير محدد',
                        'user_name' => $transaction->user->name ?? 'غير محدد',
                        'total' => (float) ($transaction->fat_net ?? $transaction->pro_value ?? 0),
                        'paid_amount' => (float) ($transaction->paid_from_client ?? 0),
                        'items_count' => $transaction->operationItems()->count(),
                    ];
                });

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
                'count' => $transactions->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching recent transactions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب العمليات',
                'transactions' => [],
            ], 500);
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

    /**
     * تسجيل مصروف نثري (Petty Cash - سند دفع لمصروف)
     */
    public function pettyCash(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'cash_account_id' => 'required|exists:acc_head,id',
            'expense_account_id' => 'required|exists:acc_head,id',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $branchId = Auth::user()->branch_id ?? 1;
            $amount = $validated['amount'];
            $cashAccountId = $validated['cash_account_id'];
            $expenseAccountId = $validated['expense_account_id'];
            $description = $validated['description'];
            $notes = $validated['notes'] ?? '';

            // جلب رقم السند التالي (Payment Voucher - pro_type = 2)
            $nextProId = OperHead::where('pro_type', 2)->max('pro_id') + 1 ?? 1;

            // إنشاء سند صرف (Payment Voucher)
            $operHead = OperHead::create([
                'pro_id' => $nextProId,
                'pro_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'pro_type' => 2, // سند صرف
                'acc1' => $expenseAccountId, // حساب المصروف (مدين)
                'acc2' => $cashAccountId, // الصندوق (دائن)
                'pro_value' => $amount,
                'fat_net' => $amount,
                'info' => $description,
                'details' => $notes ? $description.' - '.$notes : $description,
                'isdeleted' => 0,
                'is_finance' => 1, // معاملة مالية
                'is_journal' => 1, // تحتاج قيد محاسبي
                'journal_type' => 2,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // إنشاء القيد المحاسبي (JournalHead)
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $journalId = $lastJournalId + 1;
            $journalHead = JournalHead::create([
                'journal_id' => $journalId,
                'total' => $amount,
                'op_id' => $operHead->id,
                'pro_type' => 2, // سند صرف
                'date' => now()->format('Y-m-d'),
                'details' => 'قيد مصروف نثري - '.$description,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // إنشاء تفاصيل القيد المحاسبي
            // مدين - حساب المصروف
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $expenseAccountId,
                'debit' => $amount,
                'credit' => 0,
                'type' => 0,
                'info' => 'مدين - مصروف نثري: '.$description,
                'op_id' => $operHead->id,
                'isdeleted' => 0,
                'branch' => $branchId,
            ]);

            // دائن - الصندوق
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $cashAccountId,
                'debit' => 0,
                'credit' => $amount,
                'type' => 1,
                'info' => 'دائن - صندوق (مصروف نثري)',
                'op_id' => $operHead->id,
                'isdeleted' => 0,
                'branch' => $branchId,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل المصروف النثري بنجاح',
                'voucher_id' => $operHead->id,
                'voucher_number' => $nextProId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('POS Pay Out Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل المصروف: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب إعدادات الميزان (AJAX)
     */
    public function getScaleSettings()
    {
        $settings = Setting::first();

        if (! $settings) {
            $settings = new Setting;
        }

        return response()->json([
            'success' => true,
            'enable_scale_items' => $settings->enable_scale_items ?? false,
            'scale_code_prefix' => $settings->scale_code_prefix ?? '',
            'scale_code_digits' => $settings->scale_code_digits ?? 5,
            'scale_quantity_digits' => $settings->scale_quantity_digits ?? 5,
            'scale_quantity_divisor' => $settings->scale_quantity_divisor ?? 100,
        ]);
    }

    /**
     * عرض صفحة إعدادات الكاشير
     */
    public function settings()
    {
        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('view POS System')) {
            abort(403, 'ليس لديك صلاحية لعرض إعدادات الكاشير.');
        }

        // جلب إعدادات الكاشير
        $settings = Setting::first();

        if (! $settings) {
            $settings = new Setting;
        }

        // جلب الحسابات المتاحة للإعدادات
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

        return view('pos::settings', compact(
            'settings',
            'clientsAccounts',
            'stores',
            'employees',
            'cashAccounts'
        ));
    }

    /**
     * تحديث إعدادات الكاشير
     */
    public function updateSettings(Request $request)
    {
        // التحقق من الصلاحية
        if (! auth()->check() || ! auth()->user()->can('view POS System')) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية لتعديل إعدادات الكاشير.',
            ], 403);
        }

        $validated = $request->validate([
            'def_pos_client' => 'nullable|exists:acc_head,id',
            'def_pos_store' => 'nullable|exists:acc_head,id',
            'def_pos_employee' => 'nullable|exists:acc_head,id',
            'def_pos_fund' => 'nullable|exists:acc_head,id',
            'enable_scale_items' => 'nullable|boolean',
            'scale_code_prefix' => 'nullable|string|max:10',
            'scale_code_digits' => 'nullable|integer|min:1|max:10',
            'scale_quantity_digits' => 'nullable|integer|min:1|max:10',
            'scale_quantity_divisor' => 'nullable|integer|in:10,100,1000',
        ]);

        try {
            $settings = Setting::first();

            if (! $settings) {
                $settings = new Setting;
            }

            $settings->def_pos_client = $validated['def_pos_client'] ?? $settings->def_pos_client;
            $settings->def_pos_store = $validated['def_pos_store'] ?? $settings->def_pos_store;
            $settings->def_pos_employee = $validated['def_pos_employee'] ?? $settings->def_pos_employee;
            $settings->def_pos_fund = $validated['def_pos_fund'] ?? $settings->def_pos_fund;

            // إعدادات الميزان
            $settings->enable_scale_items = isset($validated['enable_scale_items']) ? (bool) $validated['enable_scale_items'] : ($settings->enable_scale_items ?? false);
            $settings->scale_code_prefix = $validated['scale_code_prefix'] ?? $settings->scale_code_prefix;
            $settings->scale_code_digits = $validated['scale_code_digits'] ?? $settings->scale_code_digits ?? 5;
            $settings->scale_quantity_digits = $validated['scale_quantity_digits'] ?? $settings->scale_quantity_digits ?? 5;
            $settings->scale_quantity_divisor = $validated['scale_quantity_divisor'] ?? $settings->scale_quantity_divisor ?? 100;

            $settings->save();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث إعدادات الكاشير بنجاح',
            ]);
        } catch (\Exception $e) {
            \Log::error('POS Settings Update Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الإعدادات: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب تفاصيل الفاتورة للإرجاع
     */
    public function getInvoice($proId)
    {
        try {
            $invoice = OperHead::with(['operationItems.item', 'operationItems.unit', 'acc1Head'])
                ->where('pro_type', 102)
                ->where('pro_id', $proId)
                ->where('isdeleted', 0)
                ->first();

            if (! $invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'الفاتورة غير موجودة',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'invoice' => [
                    'id' => $invoice->id,
                    'pro_id' => $invoice->pro_id,
                    'pro_date' => $invoice->pro_date,
                    'customer_name' => $invoice->acc1Head->aname ?? 'عميل نقدي',
                    'total' => (float) ($invoice->fat_net ?? $invoice->pro_value ?? 0),
                    'items' => $invoice->operationItems->map(function ($item) {
                        return [
                            'item_id' => $item->item_id,
                            'item_name' => $item->item->name ?? 'غير محدد',
                            'unit_name' => $item->unit->name ?? 'قطعة',
                            'quantity' => (float) $item->qty_out,
                            'price' => (float) $item->item_price,
                            'total' => (float) $item->detail_value,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * إرجاع فاتورة كاشير
     */
    public function returnInvoice(Request $request)
    {
        $validated = $request->validate([
            'original_invoice_id' => 'required|exists:oper_head,id',
            'cash_account_id' => 'nullable|exists:acc_head,id',
            'bank_account_id' => 'nullable|exists:acc_head,id',
            'payment_method' => 'nullable|string|in:cash,card,mixed',
            'cash_amount' => 'nullable|numeric|min:0',
            'card_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $originalInvoice = OperHead::with('operationItems')
                ->where('pro_type', 102) // فاتورة كاشير
                ->where('id', $validated['original_invoice_id'])
                ->where('isdeleted', 0)
                ->firstOrFail();

            $branchId = Auth::user()->branch_id ?? 1;
            $nextProId = OperHead::where('pro_type', 112)->max('pro_id') + 1 ?? 1;

            // جلب معلومات الدفع من الفاتورة الأصلية
            $cashierTransaction = \Modules\POS\app\Models\CashierTransaction::where('server_id', $originalInvoice->id)->first();
            $originalCashAmount = $cashierTransaction->cash_amount ?? 0;
            $originalCardAmount = $cashierTransaction->card_amount ?? 0;
            $originalPaymentMethod = $cashierTransaction->payment_method ?? 'cash';

            // استخدام المبالغ المحددة أو الأصلية
            $returnCashAmount = $validated['cash_amount'] ?? $originalCashAmount;
            $returnCardAmount = $validated['card_amount'] ?? $originalCardAmount;
            $returnPaymentMethod = $validated['payment_method'] ?? $originalPaymentMethod;
            $totalReturnAmount = $returnCashAmount + $returnCardAmount;

            // إنشاء فاتورة إرجاع كاشير
            $returnInvoice = OperHead::create([
                'pro_id' => $nextProId,
                'pro_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'pro_type' => 112, // مرتجع كاشير
                'acc1' => $originalInvoice->acc1,
                'acc2' => $originalInvoice->acc2,
                'store_id' => $originalInvoice->store_id,
                'emp_id' => $originalInvoice->emp_id,
                'fat_total' => $originalInvoice->fat_total,
                'fat_disc' => $originalInvoice->fat_disc,
                'fat_disc_per' => $originalInvoice->fat_disc_per,
                'fat_plus' => $originalInvoice->fat_plus,
                'fat_plus_per' => $originalInvoice->fat_plus_per,
                'fat_net' => $originalInvoice->fat_net,
                'pro_value' => $originalInvoice->pro_value,
                'paid_from_client' => $totalReturnAmount,
                'info' => 'إرجاع فاتورة كاشير رقم '.$originalInvoice->pro_id,
                'details' => 'إرجاع فاتورة كاشير رقم '.$originalInvoice->pro_id,
                'isdeleted' => 0,
                'is_stock' => 1,
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'op2' => $originalInvoice->id, // ربط بالفاتورة الأصلية
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // إنشاء تفاصيل الأصناف (عكس الكميات)
            foreach ($originalInvoice->operationItems as $item) {
                DB::table('operation_items')->insert([
                    'pro_id' => $returnInvoice->id,
                    'item_id' => $item->item_id,
                    'unit_id' => $item->unit_id,
                    'qty_in' => $item->qty_out, // عكس: إرجاع = إضافة للمخزن
                    'qty_out' => 0,
                    'item_price' => $item->item_price,
                    'cost_price' => $item->cost_price ?? 0,
                    'current_stock_value' => 0,
                    'item_discount' => $item->item_discount ?? 0,
                    'additional' => $item->additional ?? 0,
                    'detail_value' => $item->detail_value,
                    'profit' => 0,
                    'notes' => 'إرجاع من فاتورة كاشير '.$originalInvoice->pro_id,
                    'is_stock' => 1,
                    'isdeleted' => 0,
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ========== القيد الأول: قيد مردود المبيعات ==========
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $returnSalesJournalId = $lastJournalId + 1;
            JournalHead::create([
                'journal_id' => $returnSalesJournalId,
                'total' => $returnInvoice->fat_net,
                'op_id' => $returnInvoice->id,
                'pro_type' => 112,
                'date' => now()->format('Y-m-d'),
                'details' => 'قيد مردود مبيعات - مرتجع كاشير رقم '.$nextProId,
                'user' => Auth::id(),
                'branch_id' => $branchId,
            ]);

            // دائن - العميل (مردود مبيعات)
            if ($originalInvoice->acc1) {
                JournalDetail::create([
                    'journal_id' => $returnSalesJournalId,
                    'account_id' => $originalInvoice->acc1,
                    'debit' => 0,
                    'credit' => $returnInvoice->fat_net,
                    'type' => 1,
                    'info' => 'دائن - عميل (مردود مبيعات)',
                    'op_id' => $returnInvoice->id,
                    'isdeleted' => 0,
                    'branch' => $branchId,
                ]);
            }

            // مدين - حساب مردود المبيعات (48)
            JournalDetail::create([
                'journal_id' => $returnSalesJournalId,
                'account_id' => 48, // حساب مردود المبيعات
                'debit' => $returnInvoice->fat_net,
                'credit' => 0,
                'type' => 0,
                'info' => 'مدين - مردود مبيعات',
                'op_id' => $returnInvoice->id,
                'isdeleted' => 0,
                'branch' => $branchId,
            ]);

            // ========== القيد الثاني: قيد استرجاع الدفع ==========
            if ($totalReturnAmount > 0) {
                $cashAccountId = $validated['cash_account_id'] ?? ($cashierTransaction->cash_account_id ?? null);
                $bankAccountId = $validated['bank_account_id'] ?? null;

                if ($returnPaymentMethod === 'mixed' && $returnCashAmount > 0 && $returnCardAmount > 0) {
                    // استرجاع دفع مختلط - قيدين منفصلين
                    
                    // قيد استرجاع الدفع النقدي
                    $returnCashJournalId = $returnSalesJournalId + 1;
                    JournalHead::create([
                        'journal_id' => $returnCashJournalId,
                        'total' => $returnCashAmount,
                        'op_id' => $returnInvoice->id,
                        'pro_type' => 112,
                        'date' => now()->format('Y-m-d'),
                        'details' => 'قيد استرجاع دفع نقدي - مرتجع كاشير رقم '.$nextProId,
                        'user' => Auth::id(),
                        'branch_id' => $branchId,
                    ]);

                    // دائن - الصندوق
                    if ($cashAccountId) {
                        JournalDetail::create([
                            'journal_id' => $returnCashJournalId,
                            'account_id' => $cashAccountId,
                            'debit' => 0,
                            'credit' => $returnCashAmount,
                            'type' => 1,
                            'info' => 'دائن - صندوق (استرجاع دفع نقدي)',
                            'op_id' => $returnInvoice->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }

                    // مدين - العميل
                    if ($originalInvoice->acc1) {
                        JournalDetail::create([
                            'journal_id' => $returnCashJournalId,
                            'account_id' => $originalInvoice->acc1,
                            'debit' => $returnCashAmount,
                            'credit' => 0,
                            'type' => 0,
                            'info' => 'مدين - عميل (استرجاع دفع نقدي)',
                            'op_id' => $returnInvoice->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }

                    // قيد استرجاع الدفع بالبطاقة
                    $returnCardJournalId = $returnCashJournalId + 1;
                    JournalHead::create([
                        'journal_id' => $returnCardJournalId,
                        'total' => $returnCardAmount,
                        'op_id' => $returnInvoice->id,
                        'pro_type' => 112,
                        'date' => now()->format('Y-m-d'),
                        'details' => 'قيد استرجاع دفع بالبطاقة - مرتجع كاشير رقم '.$nextProId,
                        'user' => Auth::id(),
                        'branch_id' => $branchId,
                    ]);

                    // دائن - البنك
                    if ($bankAccountId) {
                        JournalDetail::create([
                            'journal_id' => $returnCardJournalId,
                            'account_id' => $bankAccountId,
                            'debit' => 0,
                            'credit' => $returnCardAmount,
                            'type' => 1,
                            'info' => 'دائن - بنك (استرجاع دفع بالبطاقة)',
                            'op_id' => $returnInvoice->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }

                    // مدين - العميل
                    if ($originalInvoice->acc1) {
                        JournalDetail::create([
                            'journal_id' => $returnCardJournalId,
                            'account_id' => $originalInvoice->acc1,
                            'debit' => $returnCardAmount,
                            'credit' => 0,
                            'type' => 0,
                            'info' => 'مدين - عميل (استرجاع دفع بالبطاقة)',
                            'op_id' => $returnInvoice->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);
                    }
                } else {
                    // استرجاع دفع واحد (نقدي أو بطاقة)
                    $returnPaymentAccountId = null;
                    $returnPaymentAmount = 0;
                    
                    if ($returnPaymentMethod === 'cash' || ($returnPaymentMethod === 'mixed' && $returnCashAmount > 0)) {
                        $returnPaymentAccountId = $cashAccountId ?? $originalInvoice->acc2;
                        $returnPaymentAmount = $returnCashAmount > 0 ? $returnCashAmount : $totalReturnAmount;
                    } elseif ($returnPaymentMethod === 'card' || ($returnPaymentMethod === 'mixed' && $returnCardAmount > 0)) {
                        $returnPaymentAccountId = $bankAccountId ?? $originalInvoice->acc2;
                        $returnPaymentAmount = $returnCardAmount > 0 ? $returnCardAmount : $totalReturnAmount;
                    }

                    if ($returnPaymentAccountId && $returnPaymentAmount > 0) {
                        $returnPaymentJournalId = $returnSalesJournalId + 1;
                        JournalHead::create([
                            'journal_id' => $returnPaymentJournalId,
                            'total' => $returnPaymentAmount,
                            'op_id' => $returnInvoice->id,
                            'pro_type' => 112,
                            'date' => now()->format('Y-m-d'),
                            'details' => 'قيد استرجاع دفع - مرتجع كاشير رقم '.$nextProId,
                            'user' => Auth::id(),
                            'branch_id' => $branchId,
                        ]);

                        // دائن - الصندوق أو البنك
                        JournalDetail::create([
                            'journal_id' => $returnPaymentJournalId,
                            'account_id' => $returnPaymentAccountId,
                            'debit' => 0,
                            'credit' => $returnPaymentAmount,
                            'type' => 1,
                            'info' => 'دائن - '.($cashAccountId ? 'صندوق' : 'بنك'),
                            'op_id' => $returnInvoice->id,
                            'isdeleted' => 0,
                            'branch' => $branchId,
                        ]);

                        // مدين - العميل
                        if ($originalInvoice->acc1) {
                            JournalDetail::create([
                                'journal_id' => $returnPaymentJournalId,
                                'account_id' => $originalInvoice->acc1,
                                'debit' => $returnPaymentAmount,
                                'credit' => 0,
                                'type' => 0,
                                'info' => 'مدين - عميل (استرجاع دفع)',
                                'op_id' => $returnInvoice->id,
                                'isdeleted' => 0,
                                'branch' => $branchId,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إرجاع فاتورة الكاشير بنجاح',
                'return_invoice_id' => $returnInvoice->id,
                'return_invoice_number' => $nextProId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Return Cashier Invoice Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الإرجاع: '.$e->getMessage(),
            ], 500);
        }
    }
}
