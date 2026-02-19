<?php

namespace Modules\Invoices\Http\Controllers;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\User;
use Modules\HR\Models\Employee;
use App\Models\OperHead;
use App\Models\JournalHead;
use Illuminate\Http\Request;
use App\Models\JournalDetail;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Accounts\Models\AccHead;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Invoices\Services\RecalculationServiceHelper;
use Modules\Invoices\Services\SaveInvoiceService;
use Modules\Invoices\Http\Requests\SaveInvoiceRequest;

class InvoiceController extends Controller
{
    public $titles = [
        10 => 'Sales Invoice',
        11 => 'Purchase Invoice',
        12 => 'Sales Return',
        13 => 'Purchase Return',
        14 => 'Sales Order',
        15 => 'Purchase Order',
        16 => 'Quotation to Customer',
        17 => 'Quotation from Supplier',
        18 => 'Damaged Goods Invoice',
        19 => 'Dispatch Order',
        20 => 'Addition Order',
        21 => 'Store-to-Store Transfer',
        22 => 'Booking Order',
        24 => 'Service Invoice',
        25 => 'Requisition',
        26 => 'Pricing Agreement',
    ];

    public function __construct(
        private SaveInvoiceService $saveInvoiceService
    ) {}

    public function index(Request $request)
    {
        $invoiceType = $request->input('type');

        if (! $invoiceType || ! array_key_exists($invoiceType, $this->titles)) {
            return redirect()->route('admin.dashboard')->with('error', 'Invalid invoice type.');
        }

        $permissionName = 'view ' . $this->titles[$invoiceType];
        $user = Auth::user();
        if (!($user instanceof User) || !$user->can($permissionName)) {
            abort(403, 'You do not have permission to view ' . $this->titles[$invoiceType]);
        }

        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $invoices = OperHead::with(['acc1Headuser', 'store', 'employee', 'acc1Head', 'acc2Head', 'type'])
            ->where('pro_type', $invoiceType)
            ->whereDate('crtime', '>=', $startDate)
            ->whereDate('crtime', '<=', $endDate)
            ->get();

        $invoiceTitle = $this->titles[$invoiceType];

        $sections = [
            'Sales Management' => [10, 12, 14, 16, 22, 26],
            'Purchases Management' => [11, 13, 15, 17, 24, 25],
            'Inventory Management' => [18, 19, 20, 21],
        ];

        $currentSection = '';
        foreach ($sections as $sectionName => $types) {
            if (in_array($invoiceType, $types)) {
                $currentSection = $sectionName;
                break;
            }
        }
        $titles = $this->titles;

        return view('invoices::invoices.index', compact(
            'invoices',
            'startDate',
            'endDate',
            'invoiceType',
            'invoiceTitle',
            'currentSection',
            'titles'
        ));
    }

    /**
     * Generate the route for creating a new invoice.
     */
    public function getCreateRoute($type)
    {
        return url('/invoices/create?type=' . $type . '&q=' . md5($type));
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show create invoice form
     * Redirects to new InvoiceFormController
     */
    public function create(Request $request)
    {
        $type = (int) $request->get('type');
        if (! isset($this->titles[$type])) {
            abort(404, 'Unknown invoice type.');
        }

        // $permissionName = 'create ' . $this->titles[$type];
        // $user = Auth::user();
        // if (!($user instanceof User) || !$user->can($permissionName)) {
        //     abort(403, 'You do not have permission to create ' . $this->titles[$type]);
        // }

        $expectedHash = md5($type);
        $providedHash = $request->get('q');

        if ($providedHash !== $expectedHash) {
            abort(403, 'Untrusted request.');
        }

        // Redirect to new InvoiceFormController
        return redirect()->route('invoices.form.create', [
            'type' => $type,
            'hash' => $expectedHash,
            'branch_id' => $request->get('branch_id', auth()->user()->branch_id ?? null)
        ]);
    }

    public function store(SaveInvoiceRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $operationId = $this->saveInvoiceService->saveInvoice($validatedData);

            if ($operationId) {
                DB::commit();

                $message = __('Invoices saved successfully.');

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'operation_id' => $operationId,
                        'print_url' => route('invoice.print', ['operation_id' => $operationId]),
                        'redirect' => back()->with('success', $message)->getTargetUrl(),
                    ]);
                }

                return redirect()->back()->with('success', $message);
            }

            throw new \Exception('Failed to save invoice result.');
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(string $id)
    {
        $invoice = OperHead::with([
            'operationItems.item',
            'acc1Head',
            'acc2Head',
            'employee',
            'type',
            'user',
            'journalHead.journalDetails.accountHead',
        ])->findOrFail($id);

        if (! $invoice || ($invoice->isdeleted ?? false)) {
            abort(404, 'Invoice not found or has been deleted.');
        }

        $type = $invoice->pro_type;
        if (! isset($this->titles[$type])) {
            abort(404, 'Unknown operation type.');
        }

        $permissionName = 'view ' . $this->titles[$type];
        $user = Auth::user();
        if (!($user instanceof User) || !$user->can($permissionName)) {
            abort(403, 'You do not have permission to view ' . $this->titles[$type]);
        }

        return view('invoices::invoices.show', compact('invoice', 'type'));
    }

    /**
     * Show edit invoice form
     * Redirects to new InvoiceFormController
     */
    public function edit(OperHead $invoice)
    {
        if (! $invoice || ($invoice->isdeleted ?? false)) {
            abort(404, 'Invoice not found or has been deleted.');
        }

        $type = $invoice->pro_type;
        if (! isset($this->titles[$type])) {
            abort(404, 'Unknown operation type.');
        }

        $permissionName = 'edit ' . $this->titles[$type];
        $user = Auth::user();
        if (!($user instanceof User) || !$user->can($permissionName)) {
            abort(403, 'You do not have permission to edit ' . $this->titles[$type]);
        }

        if ($invoice->is_posted ?? false) {
            Alert::toast('Cannot edit a posted invoice.', 'warning');
            return redirect()->route('invoices.index');
        }

        // Redirect to new InvoiceFormController
        return redirect()->route('invoices.form.edit', ['invoiceId' => $invoice->id]);
    }

    public function update(SaveInvoiceRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $validatedData['operationId'] = $id;

            $operationId = $this->saveInvoiceService->saveInvoice($validatedData, true);

            if ($operationId) {
                DB::commit();

                $message = __('Invoices updated successfully.');

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'redirect' => route('invoices.show', $operationId),
                    ]);
                }

                return redirect()->route('invoices.show', $operationId)->with('success', $message);
            }

            throw new \Exception('Failed to update invoice result.');
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        $operation = OperHead::with('operationItems')->findOrFail($id);
        $type = $operation->pro_type;

        if (! isset($this->titles[$type])) {
            abort(404, 'Unknown operation type.');
        }

        $permissionName = 'delete ' . $this->titles[$type];
        $user = Auth::user();
        if (!($user instanceof User) || !$user->can($permissionName)) {
            abort(403, 'You do not have permission to delete ' . $this->titles[$type]);
        }

        // التحقق من أن الفاتورة ليست مرحلة (اختياري حسب منطق عملك)
        /*
        if ($operation->is_posted) {
             Alert::toast('Cannot delete a posted invoice.', 'error');
             return redirect()->back();
        }
        */

        try {
            // متغير لتخزين البيانات التي سنحتاجها بعد الـ commit
            $recalcData = [];
            $operationId = $operation->id; // حفظ ID قبل الحذف
            $operationType = $operation->pro_type; // حفظ النوع قبل الحذف

            DB::transaction(function () use ($operation, &$recalcData) {
                // 1. تجهيز البيانات قبل الحذف
                $tenantId = $operation->tenant;
                $invoiceDate = $operation->pro_date;

                // جلب الأصناف
                $affectedItemIds = $operation->operationItems()
                    ->where('is_stock', 1)
                    ->pluck('item_id')
                    ->unique()
                    ->values()
                    ->toArray();

                // حفظ البيانات لاستخدامها بعد الـ commit
                $recalcData = [
                    'tenantId' => $tenantId,
                    'invoiceDate' => $invoiceDate,
                    'itemIds' => $affectedItemIds,
                ];

                // 2. عمليات الحذف (كما هي تماماً) ...
                JournalDetail::where('op_id', $operation->id)->delete();
                JournalHead::where('op_id', $operation->id)->orWhere('op2', $operation->id)->delete();

                $autoVoucher = OperHead::where('op2', $operation->id)
                    ->where('is_journal', 1)
                    ->where('is_stock', 0)
                    ->first();

                if ($autoVoucher) {
                    JournalDetail::where('op_id', $autoVoucher->id)->delete();
                    JournalHead::where(function ($q) use ($autoVoucher) {
                        $q->where('op_id', $autoVoucher->id)
                            ->orWhere('op2', $autoVoucher->id);
                    })->delete();
                    $autoVoucher->delete();
                }

                $operation->operationItems()->delete();
                $operation->delete();
            }); // انتهى الـ Transaction وتم عمل Commit

            // 3. إعادة حساب average_cost والأرباح والقيود بعد الحذف
            if (!empty($recalcData['itemIds']) && !empty($recalcData['invoiceDate'])) {
                try {
                    // استخدام Helper لاختيار تلقائي للطريقة المناسبة (Queue/Stored Procedure/PHP)
                    // إعادة حساب average_cost (فقط إذا كانت الفاتورة تؤثر على average_cost)
                    if (in_array($operationType, [11, 12, 20, 59])) {
                        // في حالة الحذف، نحسب من جميع الفواتير غير المحذوفة (لا من fromDate فقط)
                        RecalculationServiceHelper::recalculateAverageCost(
                            $recalcData['itemIds'],
                            $recalcData['invoiceDate'],
                            false, // forceQueue
                            true   // isDelete - مهم جداً!
                        );
                    }

                    // إعادة حساب الأرباح والقيود للفواتير المتأثرة
                    RecalculationServiceHelper::recalculateProfitsAndJournals(
                        $recalcData['itemIds'],
                        $recalcData['invoiceDate']
                    );
                } catch (\Exception $e) {
                    // لا نوقف العملية، فقط نسجل الخطأ
                }
            }

            Alert::toast(__('Operation deleted and costs recalculated successfully.'), 'success');

            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Invoice Delete Error: ' . $e->getMessage());
            Alert::toast('An error occurred: ' . $e->getMessage(), 'error');

            return redirect()->back();
        }
    }

    /**
     * Print the specified resource.
     */
    public function print(Request $request, $operation_id)
    {
        $operation = OperHead::with(['operationItems.item.units', 'operationItems.unit'])->findOrFail($operation_id);
        $type = $operation->pro_type;

        if (! isset($this->titles[$type])) {
            abort(404, 'Unknown operation type.');
        }

        $permissionName = 'print ' . $this->titles[$type];
        $user = Auth::user();
        if (!($user instanceof User) || !$user->can($permissionName)) {
            abort(403, 'You do not have permission to print this type.');
        }

        // Get account details
        $acc1 = AccHead::find($operation->acc1);
        $acc2 = AccHead::find($operation->acc2);
        $employee = Employee::find($operation->emp_id);
        $delivery = Employee::find($operation->delivery_id);

        $acc1Role = in_array($operation->pro_type, [10, 12, 14, 16, 22, 26]) ? 'العميل' : 
                    (in_array($operation->pro_type, [11, 13, 15, 17]) ? 'المورد' : 
                    (in_array($operation->pro_type, [18, 19, 20, 21]) ? 'المخزن' : 'غير محدد'));

        // Prepare invoice items with all details
        $invoiceItems = $operation->operationItems->map(function ($item) {
            $itemModel = Item::with('units')->find($item->item_id);
            $unit = \App\Models\Unit::find($item->unit_id);
            
            // Get barcode for this item and unit
            $barcode = DB::table('barcodes')
                ->where('item_id', $item->item_id)
                ->where('unit_id', $item->unit_id)
                ->first();

            $quantity = $item->qty_in ?: $item->qty_out;
            $price = $item->item_price;
            $discount = $item->item_discount;
            
            // Calculate sub_value: (quantity * price) - ((quantity * price * discount) / 100)
            $subtotal = $quantity * $price;
            $discountAmount = ($subtotal * $discount) / 100;
            $sub_value = $subtotal - $discountAmount;

            return [
                'item_id' => $item->item_id,
                'item_name' => $itemModel->name ?? 'غير محدد',
                'item_code' => $itemModel->code ?? '',
                'barcode' => $barcode->barcode ?? '',
                'unit_id' => $item->unit_id,
                'unit_name' => $unit->name ?? 'غير محدد',
                'quantity' => $quantity,
                'price' => $price,
                'discount' => $discount,
                'sub_value' => $sub_value,
                'batch_number' => $item->batch_number ?? '',
                'expiry_date' => $item->expiry_date ?? '',
            ];
        });

        // Calculate totals
        $subtotal = $invoiceItems->sum('sub_value');
        
        // Invoice level discount
        $invoiceDiscountPercentage = $operation->discount_percentage ?? 0;
        $invoiceDiscountValue = $operation->discount_value ?? 0;
        if ($invoiceDiscountPercentage > 0) {
            $invoiceDiscountValue = ($subtotal * $invoiceDiscountPercentage) / 100;
        }
        
        $afterDiscount = $subtotal - $invoiceDiscountValue;
        
        // Additional
        $additionalPercentage = $operation->additional_percentage ?? 0;
        $additionalValue = $operation->additional_value ?? 0;
        if ($additionalPercentage > 0) {
            $additionalValue = ($afterDiscount * $additionalPercentage) / 100;
        }
        
        $afterAdditional = $afterDiscount + $additionalValue;
        
        // VAT
        $vatPercentage = $operation->vat_percentage ?? 0;
        $vatValue = ($afterAdditional * $vatPercentage) / 100;
        
        // Withholding Tax
        $withholdingTaxPercentage = $operation->withholding_tax_percentage ?? 0;
        $withholdingTaxValue = ($afterAdditional * $withholdingTaxPercentage) / 100;
        
        // Total
        $totalAfterAdditional = $afterAdditional + $vatValue - $withholdingTaxValue;
        
        // Paid and remaining
        $paidFromClient = $operation->paid_from_client ?? 0;
        $remaining = $totalAfterAdditional - $paidFromClient;

        return view('invoices::invoices.print-invoice-2', [
            'operation' => $operation,
            'pro_id' => $operation->pro_id,
            'pro_date' => $operation->pro_date,
            'accural_date' => $operation->accural_date,
            'serial_number' => $operation->pro_serial,
            'type' => $operation->pro_type,
            'titles' => $this->titles,
            'acc1Role' => $acc1Role,
            'acc1' => $acc1,
            'acc2' => $acc2,
            'employee' => $employee,
            'delivery' => $delivery,
            'invoiceItems' => $invoiceItems,
            // Totals
            'subtotal' => $subtotal,
            'discount_percentage' => $invoiceDiscountPercentage,
            'discount_value' => $invoiceDiscountValue,
            'additional_percentage' => $additionalPercentage,
            'additional_value' => $additionalValue,
            'vat_percentage' => $vatPercentage,
            'vat_value' => $vatValue,
            'withholding_tax_percentage' => $withholdingTaxPercentage,
            'withholding_tax_value' => $withholdingTaxValue,
            'total' => $totalAfterAdditional,
            'paid_from_client' => $paidFromClient,
            'remaining' => $remaining,
            'notes' => $operation->notes ?? '',
        ]);
    }

    public function view($operationId)
    {
        $operation = OperHead::findOrFail($operationId);
        $type = $operation->pro_type;

        return view('invoices::invoices.view-invoice', compact('operationId', 'type'));
    }

    public function salesStatistics()
    {
        $stats = [
            'total_sales' => OperHead::where('pro_type', 10)->where('isdeleted', 0)->sum('pro_value'),
            'total_returns' => OperHead::where('pro_type', 12)->where('isdeleted', 0)->sum('pro_value'),
            'total_orders' => OperHead::where('pro_type', 14)->where('isdeleted', 0)->count(),
            'total_quotations' => OperHead::where('pro_type', 16)->where('isdeleted', 0)->count(),
            'total_profit' => OperHead::where('pro_type', 10)->where('isdeleted', 0)->sum('profit'),
            'today_sales' => OperHead::where('pro_type', 10)->whereDate('pro_date', today())->sum('pro_value'),
            'sales_by_day' => OperHead::where('pro_type', 10)
                ->where('isdeleted', 0)
                ->where('pro_date', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(pro_date)'))
                ->selectRaw('DATE(pro_date) as date, SUM(pro_value) as total')
                ->pluck('total', 'date'),
            'returns_by_day' => OperHead::where('pro_type', 12)
                ->where('isdeleted', 0)
                ->where('pro_date', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(pro_date)'))
                ->selectRaw('DATE(pro_date) as date, SUM(pro_value) as total')
                ->pluck('total', 'date'),
            // إحصائيات إضافية
            'highest_sale' => OperHead::where('pro_type', 10)->where('isdeleted', 0)->max('pro_value') ?? 0,
            'active_customers' => OperHead::where('pro_type', 10)
                ->where('isdeleted', 0)
                ->where('pro_date', '>=', now()->subDays(30))
                ->distinct('acc1')
                ->count(),
        ];

        return view('invoices::invoices.statistics.sales-statistics', compact('stats'));
    }

    public function purchasesStatistics()
    {
        $stats = [
            'total_purchases' => OperHead::where('pro_type', 11)->where('isdeleted', 0)->sum('pro_value'),
            'total_returns' => OperHead::where('pro_type', 13)->where('isdeleted', 0)->sum('pro_value'),
            'total_orders' => OperHead::where('pro_type', 15)->where('isdeleted', 0)->count(),
            'total_quotations' => OperHead::where('pro_type', 17)->where('isdeleted', 0)->count(),
            'today_purchases' => OperHead::where('pro_type', 11)->whereDate('pro_date', today())->sum('pro_value'),
            'pending_payments' => OperHead::where('pro_type', 11)->where('isdeleted', 0)->sum('pro_value') -
                OperHead::where('pro_type', 11)->where('isdeleted', 0)->sum('paid_from_client'),
            'purchases_by_day' => OperHead::where('pro_type', 11)
                ->where('isdeleted', 0)
                ->where('pro_date', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(pro_date)'))
                ->selectRaw('DATE(pro_date) as date, SUM(pro_value) as total')
                ->pluck('total', 'date'),
            'returns_by_day' => OperHead::where('pro_type', 13)
                ->where('isdeleted', 0)
                ->where('pro_date', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(pro_date)'))
                ->selectRaw('DATE(pro_date) as date, SUM(pro_value) as total')
                ->pluck('total', 'date'),
            // إحصائيات إضافية
            'highest_purchase' => OperHead::where('pro_type', 11)->where('isdeleted', 0)->max('pro_value') ?? 0,
            'active_suppliers' => OperHead::where('pro_type', 11)
                ->where('isdeleted', 0)
                ->where('pro_date', '>=', now()->subDays(30))
                ->distinct('acc1')
                ->count(),
        ];

        return view('invoices::invoices.statistics.purchases-statistics', compact('stats'));
    }

    public function inventoryStatistics()
    {
        $stats = [
            'total_waste' => OperHead::where('pro_type', 18)->where('isdeleted', 0)->sum('pro_value'),
            'total_issues' => OperHead::where('pro_type', 19)->where('isdeleted', 0)->sum('pro_value'),
            'total_additions' => OperHead::where('pro_type', 20)->where('isdeleted', 0)->sum('pro_value'),
            'total_transfers' => OperHead::where('pro_type', 21)->where('isdeleted', 0)->count(),
            'total_items' => Item::count(),
            'low_stock_items' => OperationItems::selectRaw('item_id, SUM(qty_in - qty_out) as total')
                ->groupBy('item_id')
                ->having('total', '<', 10)
                ->count(),
            'inventory_by_type' => [
                'waste' => OperHead::where('pro_type', 18)->where('isdeleted', 0)->sum('pro_value'),
                'issues' => OperHead::where('pro_type', 19)->where('isdeleted', 0)->sum('pro_value'),
                'additions' => OperHead::where('pro_type', 20)->where('isdeleted', 0)->sum('pro_value'),
                'transfers' => OperHead::where('pro_type', 21)->where('isdeleted', 0)->sum('pro_value'),
            ],
            // إحصائيات إضافية
            'total_inventory_value' => OperationItems::selectRaw('SUM((qty_in - qty_out) * cost_price) as total')
                ->where('is_stock', 1)
                ->value('total') ?? 0,
            'top_selling_item' => OperationItems::whereIn('pro_tybe', [10, 13, 19])
                ->selectRaw('item_id, SUM(qty_out) as total_sold')
                ->groupBy('item_id')
                ->orderByDesc('total_sold')
                ->first(),
        ];

        if ($stats['top_selling_item']) {
            $stats['top_selling_item_name'] = Item::find($stats['top_selling_item']->item_id)->name ?? 'غير معروف';
            $stats['top_selling_item_qty'] = $stats['top_selling_item']->total_sold;
        } else {
            $stats['top_selling_item_name'] = 'لا يوجد';
            $stats['top_selling_item_qty'] = 0;
        }

        return view('invoices::invoices.statistics.inventory-statistics', compact('stats'));
    }
}
