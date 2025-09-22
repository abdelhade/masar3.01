<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\{OperHead, JournalHead, AccHead, Employee, Item, JournalDetail};

class InvoiceController extends Controller
{
    public $titles = [
        10 => 'فاتوره مبيعات',
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
    ];

    // public function index(Request $request)
    // {
    //     $type = (int) $request->get('type');

    //     $permissions = [
    //         10 => 'عرض فاتورة مبيعات',
    //         11 => 'عرض فاتورة مشتريات',
    //         12 => 'عرض مردود مبيعات',
    //         13 => 'عرض مردود مشتريات',
    //         14 => 'عرض أمر بيع',
    //         15 => 'عرض أمر شراء',
    //         16 => 'عرض عرض سعر لعميل',
    //         17 => 'عرض عرض سعر من مورد',
    //         18 => 'عرض فاتورة تالف',
    //         19 => 'عرض أمر صرف',
    //         20 => 'عرض أمر إضافة',
    //         21 => 'عرض تحويل من مخزن لمخزن',
    //         22 => 'عرض أمر حجز',
    //     ];

    //     if (!isset($permissions[$type])) {
    //         abort(404, 'نوع العملية غير معروف');
    //     }

    //     if (!auth()->user()->can($permissions[$type])) {
    //         abort(403, 'ليس لديك صلاحية لعرض هذا النوع.');
    //     }

    //     $invoices = OperHead::with(['acc1Headuser', 'store', 'employee', 'acc1Head', 'acc2Head', 'type'])
    //         ->where('pro_type', $type)
    //         ->get();

    //     return view('invoices.index', compact('invoices', 'type'));
    // }

    public function index(Request $request)
    {
        // Get the invoice type from the request
        $invoiceType = $request->input('type');

        // If no type is provided, redirect to dashboard or show error
        if (!$invoiceType || !array_key_exists($invoiceType, $this->titles)) {
            return redirect()->route('admin.dashboard')->with('error', 'نوع الفاتورة غير صحيح');
        }

        // Default to today's date if no date range is provided
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        // Build the query for specific invoice type only
        $invoices = OperHead::with(['acc1Headuser', 'store', 'employee', 'acc1Head', 'acc2Head', 'type'])
            ->where('pro_type', $invoiceType)
            ->whereDate('crtime', '>=', $startDate)
            ->whereDate('crtime', '<=', $endDate)
            ->get();

        // Get the title for the current invoice type
        $invoiceTitle = $this->titles[$invoiceType];

        // Define sections for breadcrumb and navigation
        $sections = [
            'ادارة المبيعات' => [10, 12, 14, 16, 22],
            'ادارة المشتريات' => [11, 13, 15, 17],
            'ادارة المخزون' => [18, 19, 20, 21],
        ];

        // Find which section this invoice type belongs to
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

    // Helper method to get create route for specific invoice type
    public function getCreateRoute($type)
    {
        return url('/invoices/create?type=' . $type . '&q=' . md5($type));
    }


    public function create(Request $request)
    {
        // dd($request->all());
        $type = (int) $request->get('type');

        // $permissions = [
        //     10 => 'إضافة فاتورة مبيعات',
        //     11 => 'إضافة فاتورة مشتريات',
        //     12 => 'إضافة مردود مبيعات',
        //     13 => 'إضافة مردود مشتريات',
        //     14 => 'إضافة أمر بيع',
        //     15 => 'إضافة أمر شراء',
        //     16 => 'إضافة عرض سعر لعميل',
        //     17 => 'إضافة عرض سعر من مورد',
        //     18 => 'إضافة فاتورة تالف',
        //     19 => 'إضافة أمر صرف',
        //     20 => 'إضافة أمر إضافة',
        //     21 => 'إضافة تحويل من مخزن لمخزن',
        //     22 => 'إضافة أمر حجز',
        // ];

        // if (!isset($permissions[$type])) {
        //     abort(404, 'نوع العملية غير معروف');
        // }

        // if (!Auth()->user()->can($permissions[$type])) {
        //     abort(403, 'ليس لديك صلاحية لإضافة هذا النوع.');
        // }

        // التحقق من الـ hash
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
        // Ensure the invoice exists and is not soft deleted
        if (!$invoice || ($invoice->isdeleted ?? false)) {
            abort(404, 'الفاتورة غير موجودة أو محذوفة');
        }

        $type = $invoice->pro_type;

        $permissions = [
            10 => 'تعديل فاتورة مبيعات',
            11 => 'تعديل فاتورة مشتريات',
            12 => 'تعديل مردود مبيعات',
            13 => 'تعديل مردود مشتريات',
            14 => 'تعديل أمر بيع',
            15 => 'تعديل أمر شراء',
            16 => 'تعديل عرض سعر لعميل',
            17 => 'تعديل عرض سعر من مورد',
            18 => 'تعديل فاتورة تالف',
            19 => 'تعديل أمر صرف',
            20 => 'تعديل أمر إضافة',
            21 => 'تعديل تحويل من مخزن لمخزن',
            22 => 'تعديل أمر حجز',
        ];

        if (!isset($permissions[$type])) {
            abort(404, 'نوع العملية غير معروف');
        }

        if (!auth()->user()->can($permissions[$type])) {
            abort(403, 'ليس لديك صلاحية لتعديل هذا النوع.');
        }

        // Check if the invoice is in a state that allows editing
        if ($invoice->is_posted ?? false) {
            Alert::toast('لا يمكن تعديل الفاتورة بعد ترحيلها', 'warning');
            return redirect()->route('invoices.index');
        }

        // Check if the invoice has been deleted
        if ($invoice->isdeleted ?? false) {
            abort(404, 'الفاتورة محذوفة أو غير موجودة');
        }

        // Load necessary relationships for the view
        $invoice->load(['operationItems.item.units', 'operationItems.item.prices', 'acc1Head', 'acc2Head', 'employee']);

        // Log the edit attempt for audit purposes
        Log::info('Invoice edit accessed', [
            'invoice_id' => $invoice->id,
            'invoice_type' => $type,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'accessed_at' => now(),
        ]);

        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, string $id)
    {
        // This method is intentionally left empty as updates are handled by Livewire
        // The EditInvoiceForm component handles all update logic through the updateForm() method
        abort(404, 'Updates are handled through the Livewire component');
    }

    public function destroy(string $id)
    {
        $operation = OperHead::findOrFail($id);

        $type = $operation->pro_type;

        $permissions = [
            10 => 'حذف فاتورة مبيعات',
            11 => 'حذف فاتورة مشتريات',
            12 => 'حذف مردود مبيعات',
            13 => 'حذف مردود مشتريات',
            14 => 'حذف أمر بيع',
            15 => 'حذف أمر شراء',
            16 => 'حذف عرض سعر لعميل',
            17 => 'حذف عرض سعر من مورد',
            18 => 'حذف فاتورة تالف',
            19 => 'حذف أمر صرف',
            20 => 'حذف أمر إضافة',
            21 => 'حذف تحويل من مخزن لمخزن',
            22 => 'حذف أمر حجز',
        ];

        if (!isset($permissions[$type])) {
            abort(404, 'نوع العملية غير معروف');
        }

        if (!auth()->user()->can($permissions[$type])) {
            abort(403, 'ليس لديك صلاحية لحذف هذا النوع.');
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

            // حذف العملية نفسها
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

        if (!auth()->user()->can($permissions[$type])) {
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
            22 => 'أمر حجز',
        ];

        $acc1Role = in_array($operation->pro_type, [10, 12, 14, 16, 22]) ? 'مدين' : (in_array($operation->pro_type, [11, 13, 15, 17]) ? 'دائن' : (in_array($operation->pro_type, [18, 19, 20, 21]) ? 'مدين' : 'غير محدد'));

        return view('invoices.print-invoice', [
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
        return view('invoices.view-invoice', compact('operationId'));
    }
}
