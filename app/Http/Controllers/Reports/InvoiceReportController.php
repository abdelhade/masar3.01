<?php

namespace App\Http\Controllers\Reports;

use App\Models\OperHead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoiceReportController extends Controller
{
    public function purchaseInvoices(Request $request)
    {
        $invoices = OperHead::whereIn('pro_type', [11, 13])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->paginate(50);
        return view('reports.invoices.purchase-invoice', compact('invoices'));
    }

    public function salesInvoices(Request $request)
    {
        $invoices = OperHead::whereIn('pro_type', [10, 12])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->paginate(50);
        return view('reports.invoices.sales-invoice', compact('invoices'));
    }

    public function salesOrdersReport()
    {
        $invoices = OperHead::whereIn('pro_type', [14])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->paginate(50);
        return view('reports.invoices.sales-orders', compact('invoices'));
    }

    public function purchaseQuotationsReport()
    {
        $invoices = OperHead::whereIn('pro_type', [16])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->paginate(50);
        return view('reports.invoices.purchase-quotations', compact('invoices'));
    }

    public function supplierRfqsReport()
    {
        $invoices = OperHead::whereIn('pro_type', [17])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->paginate(50);
        return view('reports.invoices.supplier-rfqs', compact('invoices'));
    }

    public function manufacturingReport()
    {
        $invoices = OperHead::whereIn('pro_type', [59])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user'])
            ->orderByDesc('pro_date')
            ->paginate(50);
        return view('reports.invoices.manufacturing-report', compact('invoices'));
    }

    public function editPurchasePriceInvoice($id)
    {
        return view('reports.invoices.edit-purchase-price-invoice-report', compact('id'));
    }

    public function invoicesBarcodeReport($id)
    {
        return view('reports.invoices.invoices-barcode-report', compact('id'));
    }

    public function convertToPurchaseInvoice($id)
    {
        try {
            $originalInvoice = OperHead::with(['operationItems.item.units'])->find($id);
            if (!$originalInvoice) {
                return redirect()->back()->with('error', 'المستند الأصلي غير موجود');
            }

            if (!in_array($originalInvoice->pro_type, [15, 17])) {
                return redirect()->back()->with('error', 'نوع المستند غير صحيح للتحويل إلى فاتورة مشتريات');
            }

            $invoiceData = [
                'type' => 11,
                'original_invoice_id' => $originalInvoice->id,
                'original_invoice_number' => $originalInvoice->pro_id,
                'supplier_id' => $originalInvoice->acc1,
                'store_id' => $originalInvoice->acc2,
                'employee_id' => $originalInvoice->emp_id,
                'notes' => 'تم التحويل من ' . ($originalInvoice->pro_type == 15 ? 'أمر شراء' : 'عرض سعر مورد') . ' رقم: ' . $originalInvoice->pro_id,
                'invoice_date' => now()->format('Y-m-d'),
            ];

            $itemsData = [];
            foreach ($originalInvoice->operationItems as $item) {
                if ($item->item && $item->unit_id) {
                    $availableUnits = $item->item->units->map(function ($unit) {
                        return (object) [
                            'id' => $unit->id,
                            'name' => $unit->name,
                        ];
                    });

                    $quantity = $item->fat_quantity ?? $item->qty_in ?? 1;
                    $price = $item->cost_price ?? $item->fat_price ?? 0;
                    $itemDiscount = $item->item_discount ?? 0;
                    $subValue = $item->detail_value ?? (($quantity * $price) - $itemDiscount);

                    $itemsData[] = [
                        'item_id' => $item->item_id,
                        'unit_id' => $item->unit_id,
                        'name' => $item->item->name ?? '',
                        'quantity' => $quantity,
                        'price' => $price,
                        'discount' => $itemDiscount,
                        'sub_value' => $subValue,
                        'available_units' => $availableUnits,
                    ];
                }
            }

            if (empty($itemsData)) {
                return redirect()->back()->with('error', 'لا توجد أصناف صالحة للتحويل في هذا المستند');
            }

            $sessionData = [
                'invoice_data' => $invoiceData,
                'items_data' => $itemsData,
                'discount_percentage' => $originalInvoice->fat_disc_per ?? 0,
                'additional_percentage' => $originalInvoice->fat_plus_per ?? 0,
                'discount_value' => $originalInvoice->fat_disc ?? 0,
                'additional_value' => $originalInvoice->fat_plus ?? 0,
                'total_after_additional' => $originalInvoice->fat_net ?? 0,
                'subtotal' => $originalInvoice->fat_total ?? 0,
            ];

            session()->put('convert_invoice_data', $sessionData);

            if (!session()->has('convert_invoice_data')) {
                return redirect()->back()->with('error', 'فشل في حفظ بيانات التحويل');
            }

            return redirect()->route('invoices.create', [
                'type' => 11,
                'hash' => md5(11)
            ])->with('success', 'تم تحميل بيانات الطلب. يمكنك الآن التعديل عليها وحفظها كفاتورة مشتريات.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التحويل: ');
        }
    }
    // دالة تحويل أمر البيع أو عرض السعر للعميل إلى صفحة إنشاء فاتورة مبيعات
    public function convertToSalesInvoice($id)
    {
        try {
            $originalInvoice = OperHead::with(['operationItems.item.units'])->find($id);

            if (!$originalInvoice) {
                return redirect()->back()->with('error', 'الفاتورة غير موجودة');
            }

            if (!in_array($originalInvoice->pro_type, [14, 16])) {
                return redirect()->back()->with('error', 'نوع الفاتورة غير صحيح للتحويل');
            }

            $invoiceData = [
                'type' => 10,
                'original_invoice_id' => $originalInvoice->id,
                'original_invoice_number' => $originalInvoice->pro_id,
                'client_id' => $originalInvoice->acc1,
                'store_id' => $originalInvoice->acc2,
                'employee_id' => $originalInvoice->emp_id,
                'notes' => 'تم التحويل من ' . ($originalInvoice->pro_type == 14 ? 'أمر بيع' : 'عرض سعر') . ' رقم: ' . $originalInvoice->pro_id,
                'invoice_date' => now()->format('Y-m-d'),
            ];

            $itemsData = [];
            foreach ($originalInvoice->operationItems as $item) {
                if ($item->item && $item->unit_id) {
                    $availableUnits = $item->item->units->map(function ($unit) {
                        return (object) [
                            'id' => $unit->id,
                            'name' => $unit->name,
                        ];
                    });

                    $quantity = $item->fat_quantity ?? $item->qty_in ?? 1;
                    $price = $item->fat_price ?? $item->item_price ?? 0;
                    $itemDiscount = $item->item_discount ?? 0;
                    $subValue = $item->detail_value ?? (($quantity * $price) - $itemDiscount);
                    $itemsData[] = [
                        'item_id' => $item->item_id,
                        'unit_id' => $item->unit_id,
                        'name' => $item->item->name ?? '',
                        'quantity' => $quantity,
                        'price' => $price,
                        'discount' => $itemDiscount,
                        'sub_value' => $subValue,
                        'available_units' => $availableUnits,
                    ];
                }
            }

            if (empty($itemsData)) {
                return redirect()->back()->with('error', 'لا توجد أصناف صالحة للتحويل في هذه الفاتورة');
            }

            $sessionData = [
                'invoice_data' => $invoiceData,
                'items_data' => $itemsData,
                'discount_percentage' => $originalInvoice->fat_disc_per ?? 0,
                'additional_percentage' => $originalInvoice->fat_plus_per ?? 0,
                'discount_value' => $originalInvoice->fat_disc ?? 0,
                'additional_value' => $originalInvoice->fat_plus ?? 0,
                'total_after_additional' => $originalInvoice->fat_net ?? 0,
                'subtotal' => $originalInvoice->fat_total ?? 0,
            ];

            session()->put('convert_invoice_data', $sessionData);

            if (!session()->has('convert_invoice_data')) {
                return redirect()->back()->with('error', 'فشل في حفظ بيانات التحويل');
            }

            return redirect()->route('invoices.create', [
                'type' => 10,
                'q' => md5(10)
            ])->with('success', 'تم تحميل بيانات الطلب. يمكنك الآن التعديل عليها وحفظها كفاتورة مبيعات.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التحويل: ');
        }
    }
}
