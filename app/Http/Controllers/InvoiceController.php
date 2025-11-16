<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\OperationItems;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\Accounts\Models\AccHead;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\{OperHead, JournalHead, Employee, Item, JournalDetail};

class InvoiceController extends Controller
{
    private $titles = [
        10 => 'فاتورة مبيعات',
        11 => 'فاتورة مشتريات',
        12 => 'مردود مبيعات',
        13 => 'مردود مشتريات',
        14 => 'امر بيع',
        15 => 'امر شراء',
        16 => 'عرض سعر لعميل',
        17 => 'عرض سعر من مورد',
        18 => 'فاتورة توالف',
        19 => 'امر صرف',
        20 => 'امر اضافة',
        21 => 'تحويل من مخزن لمخزن',
        22 => 'امر حجز',
        24 => 'فاتورة خدمة',
        25 => 'طلب احتياج',
        26 => 'اتفاقية تسعير',
    ];


    public function index(Request $request)
    {
        $invoiceType = $request->input('type');

        if (!$invoiceType || !array_key_exists($invoiceType, $this->titles)) {
            return redirect()->route('admin.dashboard')->with('error', 'نوع الفاتورة غير صحيح');
        }

        $permissionName = 'view ' . $this->titles[$invoiceType];
        if (!auth()->user()->can($permissionName)) {
            abort(403, 'ليس لديك صلاحية لعرض ' . $this->titles[$invoiceType]);
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
            'ادارة المبيعات' => [10, 12, 14, 16, 22, 26],
            'ادارة المشتريات' => [11, 13, 15, 17, 24, 25],
            'ادارة المخزون' => [18, 19, 20, 21],
        ];

        $currentSection = '';
        foreach ($sections as $sectionName => $types) {
            if (in_array($invoiceType, $types)) {
                $currentSection = $sectionName;
                break;
            }
        }

        return view('invoices.index', compact(
            'invoices',
            'startDate',
            'endDate',
            'invoiceType',
            'invoiceTitle',
            'currentSection'
        ));
    }


    public function getCreateRoute($type)
    {
        return url('/invoices/create?type=' . $type . '&q=' . md5($type));
    }

    public function create(Request $request)
    {
        $type = (int) $request->get('type');

        if (!isset($this->titles[$type])) {
            abort(404, 'نوع الفاتورة غير معروف');
        }

        $permissionName = 'create ' . $this->titles[$type];
        if (!auth()->user()->can($permissionName)) {
            abort(403, 'ليس لديك صلاحية لإنشاء ' . $this->titles[$type]);
        }

        $expectedHash = md5($type);
        $providedHash = $request->get('q');

        if ($providedHash !== $expectedHash) {
            abort(403, 'الطلب غير موثوق.');
        }

        return view('invoices.create', [
            'type' => $type,
            'hash' => $expectedHash,
        ]);
    }


    public function store(Request $request) {}

    public function show(string $id) {}



    public function edit(OperHead $invoice)
    {
        if (!$invoice || ($invoice->isdeleted ?? false)) {
            abort(404, 'الفاتورة غير موجودة أو محذوفة');
        }

        $type = $invoice->pro_type;

        if (!isset($this->titles[$type])) {
            abort(404, 'نوع العملية غير معروف');
        }

        $permissionName = 'edit ' . $this->titles[$type];
        if (!Auth::user()->can($permissionName)) {
            abort(403, 'ليس لديك صلاحية لتعديل ' . $this->titles[$type]);
        }

        if ($invoice->is_posted ?? false) {
            Alert::toast('لا يمكن تعديل الفاتورة بعد ترحيلها', 'warning');
            return redirect()->route('invoices.index');
        }

        $invoice->load(['operationItems.item.units', 'operationItems.item.prices', 'acc1Head', 'acc2Head', 'employee']);

        Log::info('Invoice edit accessed', [
            'invoice_id' => $invoice->id,
            'invoice_type' => $type,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'accessed_at' => now(),
        ]);

        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, string $id)
    {
        abort(404, 'Updates are handled through the Livewire component');
    }

    public function destroy(string $id)
    {
        $operation = OperHead::findOrFail($id);
        $type = $operation->pro_type;

        if (!isset($this->titles[$type])) {
            abort(404, 'نوع العملية غير معروف');
        }

        $permissionName = 'delete ' . $this->titles[$type];
        if (!Auth::user()->can($permissionName)) {
            abort(403, 'ليس لديك صلاحية لحذف ' . $this->titles[$type]);
        }

        try {
            $operation->operationItems()->delete();
            JournalDetail::where('op_id', $operation->id)->delete();
            JournalHead::where('op_id', $operation->id)->orWhere('op2', $operation->id)->delete();

            $autoVoucher = OperHead::where('op2', $operation->id)->where('is_journal', 1)->where('is_stock', 0)->first();
            if ($autoVoucher) {
                JournalDetail::where('op_id', $autoVoucher->id)->delete();
                JournalHead::where('op_id', $autoVoucher->id)->orWhere('op2', $autoVoucher->id)->delete();
                $autoVoucher->delete();
            }

            $operation->delete();
            Alert::toast('تم حذف العملية وسنداتها بنجاح.', 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء حذف العملية: ' . $e->getMessage(), 'error');
            return redirect()->back();
        }
    }


    public function print(Request $request, $operation_id)
    {
        $operation = OperHead::with('operationItems')->findOrFail($operation_id);

        $type = $operation->pro_type;

        $permissions = [
            10 => 'طباعة فاتورة مبيعات',
            11 => 'طباعة فاتورة مشتريات',
            12 => 'طباعة مردود مبيعات',
            13 => 'طباعة مردود مشتريات',
            14 => 'طباعة أمر بيع',
            15 => 'طباعة أمر شراء',
            16 => 'طباعة عرض سعر لعميل',
            17 => 'طباعة عرض سعر من مورد',
            18 => 'طباعة فاتورة تالف',
            19 => 'طباعة أمر صرف',
            20 => 'طباعة أمر إضافة',
            21 => 'طباعة تحويل من مخزن لمخزن',
            22 => 'طباعة أمر حجز',
        ];

        if (!isset($permissions[$type])) {
            abort(404, 'نوع العملية غير معروف');
        }

        if (!Auth::user()->can($permissions[$type])) {
            abort(403, 'ليس لديك صلاحية لطباعة هذا النوع.');
        }

        $acc1List = AccHead::where('id', $operation->acc1)->get();
        $acc2List = AccHead::where('id', $operation->acc2)->get();
        $employees = Employee::where('id', $operation->emp_id)->get();
        $items = Item::whereIn('id', $operation->operationItems->pluck('item_id'))->get();

        $titles = [
            10 => 'فاتورة مبيعات',
            11 => 'فاتورة مشتريات',
            12 => 'مردود مبيعات',
            13 => 'مردود مشتريات',
            14 => 'أمر بيع',
            15 => 'أمر شراء',
            16 => 'عرض سعر لعميل',
            17 => 'عرض سعر من مورد',
            18 => 'فاتورة توالف',
            19 => 'أمر صرف',
            20 => 'أمر إضافة',
            21 => 'تحويل من مخزن لمخزن',
            22 => 'امر حجز',
            26 => 'اتفاقية تسعير',
        ];

        $acc1Role = in_array($operation->pro_type, [10, 12, 14, 16, 22, 26]) ? 'مدين' : (in_array($operation->pro_type, [11, 13, 15, 17]) ? 'دائن' : (in_array($operation->pro_type, [18, 19, 20, 21]) ? 'مدين' : 'غير محدد'));

        return view('invoices.print-invoice-2', [
            'pro_id' => $operation->pro_id,
            'pro_date' => $operation->pro_date,
            'accural_date' => $operation->accural_date,
            'serial_number' => $operation->pro_serial,
            'acc1_id' => $operation->acc1,
            'acc2_id' => $operation->acc2,
            'emp_id' => $operation->emp_id,
            'type' => $operation->pro_type,
            'titles' => $titles,
            'acc1Role' => $acc1Role,
            'acc1List' => $acc1List,
            'acc2List' => $acc2List,
            'employees' => $employees,
            'items' => $items,
            'invoiceItems' => $operation->operationItems->map(function ($item) {
                $unit = \App\Models\Unit::find($item->unit_id);
                return [
                    'item_id' => $item->item_id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->qty_in ?: $item->qty_out,
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
            'received_from_client' => $operation->pro_value,
            'notes' => $operation->info,
        ]);
    }

    public function view($operationId)
    {
        $operation = OperHead::findOrFail($operationId);
        $type = $operation->pro_type;

        return view('invoices.view-invoice', compact('operationId', 'type'));
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

        return view('invoices.statistics.sales-statistics', compact('stats'));
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

        return view('invoices.statistics.purchases-statistics', compact('stats'));
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

        return view('invoices.statistics.inventory-statistics', compact('stats'));
    }
}
