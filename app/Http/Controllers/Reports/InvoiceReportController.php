<?php

namespace App\Http\Controllers\Reports;

use App\Models\OperHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $invoices = OperHead::whereIn('pro_type', [17])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user', 'operationItems.item'])
            ->orderByDesc('pro_date')
            ->get();

        $itemsComparison = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->operationItems as $item) {
                if (!$item->item_id || $item->isdeleted) continue;

                $itemId = $item->item_id;
                $itemName = $item->item->name ?? 'غير معروف';
                $price = $item->item_price ?? 0;
                $quantity = $item->qty_in ?? 1;

                if ($price <= 0) continue;

                if (!isset($itemsComparison[$itemId])) {
                    $itemsComparison[$itemId] = [
                        'item_name' => $itemName,
                        'quotations' => []
                    ];
                }

                $itemsComparison[$itemId]['quotations'][] = [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->pro_id,
                    'supplier_name' => $invoice->acc1Head->aname ?? '-',
                    'supplier_id' => $invoice->acc1,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $price * $quantity,
                    'invoice_date' => $invoice->pro_date,
                    'employee' => $invoice->employee->aname ?? '-',
                    'user' => $invoice->user->name ?? '-',
                ];
            }
        }

        foreach ($itemsComparison as $itemId => &$itemData) {
            usort($itemData['quotations'], function ($a, $b) {
                return $a['price'] <=> $b['price'];
            });
        }

        return view('reports.invoices.purchase-quotations', compact('itemsComparison'));
    }

    public function supplierQuotationsComparisonReport()
    {
        $itemsComparison = $this->getQuotationsComparison(17);

        return view('reports.supplier-quotations-comparison', [
            'itemsComparison' => $itemsComparison
        ]);
    }

    public function customerQuotationsComparisonReport()
    {
        $itemsComparison = $this->getQuotationsComparison(14);

        return view('reports.customer-quotations-comparison', [
            'itemsComparison' => $itemsComparison
        ]);
    }

    private function getQuotationsComparison($proType)
    {
        // جلب عروض الأسعار مع العلاقات
        $quotations = OperHead::with(['acc1Head', 'acc2Head', 'operationItems.item', 'user'])
            ->where('pro_type', $proType)
            ->where('status', 0) // عروض غير محولة
            ->whereNull('deleted_at')
            ->latest('pro_date')
            ->get();

        $itemsComparison = [];

        foreach ($quotations as $quotation) {
            foreach ($quotation->operationItems as $detail) {
                $itemId = $detail->item_id;

                // تخطي الأصناف المحذوفة أو غير الصالحة
                if (!$itemId || $detail->isdeleted) continue;

                if (!isset($itemsComparison[$itemId])) {
                    $itemsComparison[$itemId] = [
                        'item_name' => $detail->item->name ?? 'صنف غير معروف',
                        'quotations' => []
                    ];
                }

                // حساب الكمية الصحيحة
                $quantity = $proType == 17
                    ? ($detail->qty_in ?: 1)
                    : ($detail->qty_out ?: 1);

                $itemsComparison[$itemId]['quotations'][] = [
                    'invoice_id' => $quotation->id,
                    'invoice_number' => $quotation->pro_id ?? 'غير متوفر',
                    'price' => $detail->item_price ?? 0,
                    'quantity' => $quantity,
                    'total' => $detail->detail_value ?? 0,
                    'invoice_date' => $quotation->pro_date ? date('Y-m-d', strtotime($quotation->pro_date)) : 'غير متوفر',
                    'employee' => $quotation->user->name ?? 'غير معروف',
                    'pro_type' => $proType
                ];
            }
        }

        foreach ($itemsComparison as &$item) {
            usort($item['quotations'], function ($a, $b) use ($proType) {
                return $proType == 17
                    ? $a['price'] <=> $b['price']
                    : $b['price'] <=> $a['price'];
            });
        }

        return $itemsComparison;
    }

    public function supplierRfqsReport()
    {
        $invoices = OperHead::whereIn('pro_type', [17])
            ->with(['acc1Head', 'acc2Head', 'employee', 'user', 'operationItems.item'])
            ->orderByDesc('pro_date')
            ->get();

        $itemsComparison = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->operationItems as $item) {
                if (!$item->item_id || $item->isdeleted) continue;

                $itemId = $item->item_id;
                $itemName = $item->item->name ?? 'غير معروف';
                $price = $item->item_price ?? 0;
                $quantity = $item->qty_in ?? 1;

                if ($price <= 0) continue;

                if (!isset($itemsComparison[$itemId])) {
                    $itemsComparison[$itemId] = [
                        'item_name' => $itemName,
                        'quotations' => []
                    ];
                }

                $itemsComparison[$itemId]['quotations'][] = [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->pro_id,
                    'supplier_name' => $invoice->acc1Head->aname ?? '-',
                    'supplier_id' => $invoice->acc1,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $price * $quantity,
                    'invoice_date' => $invoice->pro_date,
                    'employee' => $invoice->employee->aname ?? '-',
                    'user' => $invoice->user->name ?? '-',
                ];
            }
        }

        foreach ($itemsComparison as $itemId => &$itemData) {
            usort($itemData['quotations'], function ($a, $b) {
                return $a['price'] <=> $b['price'];
            });
        }

        return view('reports.invoices.supplier-rfqs', compact('itemsComparison'));
    }

    private function getBestPricesForAllItems()
    {
        $bestPrices = DB::table('operation_items as oi')
            ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
            ->join('acc_head as supplier', 'oh.acc1', '=', 'supplier.id')
            ->where('oh.pro_type', 17)
            ->where('oi.isdeleted', 0)
            ->whereNotNull('oi.item_price')
            ->where('oi.item_price', '>', 0)
            ->select(
                'oi.item_id',
                DB::raw('MIN(oi.item_price) as best_price'),
                DB::raw('MAX(oi.item_price) as worst_price'),
                DB::raw('AVG(oi.item_price) as avg_price'),
                DB::raw('COUNT(DISTINCT oh.id) as quotation_count')
            )
            ->groupBy('oi.item_id')
            ->get()
            ->keyBy('item_id');

        $bestSuppliers = DB::table('operation_items as oi')
            ->join('operhead as oh', 'oi.pro_id', '=', 'oh.id')
            ->join('acc_head as supplier', 'oh.acc1', '=', 'supplier.id')
            ->whereIn(DB::raw('(oi.item_id, oi.item_price)'), function ($query) {
                $query->select('item_id', DB::raw('MIN(item_price)'))
                    ->from('operation_items as oi2')
                    ->join('operhead as oh2', 'oi2.pro_id', '=', 'oh2.id')
                    ->where('oh2.pro_type', 17)
                    ->where('oi2.isdeleted', 0)
                    ->whereNotNull('oi2.item_price')
                    ->where('oi2.item_price', '>', 0)
                    ->groupBy('item_id');
            })
            ->where('oh.pro_type', 17)
            ->where('oi.isdeleted', 0)
            ->select(
                'oi.item_id',
                'supplier.aname as supplier_name',
                'oh.pro_id as quotation_number',
                'oh.id as invoice_id',
                'oi.item_price'
            )
            ->get()
            ->keyBy('item_id');

        return $bestPrices->map(function ($price, $itemId) use ($bestSuppliers) {
            $supplier = $bestSuppliers->get($itemId);
            return [
                'best_price' => $price->best_price,
                'worst_price' => $price->worst_price,
                'avg_price' => $price->avg_price,
                'quotation_count' => $price->quotation_count,
                'best_supplier' => $supplier->supplier_name ?? '-',
                'best_quotation_number' => $supplier->quotation_number ?? '-',
                'best_invoice_id' => $supplier->invoice_id ?? null,
            ];
        });
    }

    public function getSupplierRfqDetails($id)
    {
        $invoice = OperHead::with(['operationItems.item', 'acc1Head'])->findOrFail($id);
        $bestPrices = $this->getBestPricesForAllItems();

        $items = [];
        $total = 0;

        foreach ($invoice->operationItems as $item) {
            $currentPrice = $item->item_price ?? 0;
            $quantity = $item->qty_in ?? 1;
            $total += $currentPrice * $quantity;

            $bestData = $bestPrices->get($item->item_id);

            $items[] = [
                'item_name' => $item->item->name ?? '-',
                'quantity' => $quantity,
                'current_price' => $currentPrice,
                'best_price' => $bestData['best_price'] ?? $currentPrice,
                'quotation_count' => $bestData['quotation_count'] ?? 0,
                'best_supplier' => $bestData['best_supplier'] ?? '-',
                'best_quotation_number' => $bestData['best_quotation_number'] ?? '-',
            ];
        }

        return response()->json([
            'invoice_number' => $invoice->pro_id,
            'supplier_name' => $invoice->acc1Head->aname ?? '-',
            'invoice_date' => $invoice->pro_date,
            'total' => $total,
            'items' => $items,
        ]);
    }

    public function convertToPurchaseInvoice($id)
    {
        try {
            $originalInvoice = OperHead::with(['operationItems.item.units'])->findOrFail($id);

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
                'invoice_date' => $originalInvoice->pro_date ?? now()->format('Y-m-d'),
                'accural_date' => $originalInvoice->accural_date ?? now()->format('Y-m-d'),
                'branch_id' => $originalInvoice->branch_id,
                'delivery_id' => $originalInvoice->emp2_id,
                'cash_box_id' => null,
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

                    $quantity = $item->qty_in ?? 1;
                    $price = $item->item_price ?? 0;
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
                        'notes' => $item->notes ?? '',
                        'batch_number' => $item->batch_number ?? '',
                        'expiry_date' => $item->expiry_date ?? '',
                        'serial_numbers' => $item->serial_numbers ?? '',
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
                'tax_percentage' => $originalInvoice->fat_tax_per ?? 0,
                'tax_value' => $originalInvoice->fat_tax ?? 0,
            ];

            session()->put('convert_invoice_data', $sessionData);

            if (!session()->has('convert_invoice_data')) {
                return redirect()->back()->with('error', 'فشل في حفظ بيانات التحويل');
            }

            return redirect()->route('invoices.create', [
                'type' => 11,
                'q' => md5(11)
            ])->with('success', 'تم تحميل بيانات العرض. يمكنك الآن التعديل عليها وحفظها كفاتورة مشتريات.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التحويل: ' . $e->getMessage());
        }
    }

    public function convertToSalesInvoice($id)
    {
        try {
            $originalInvoice = OperHead::with(['operationItems.item.units'])->findOrFail($id);
            $invoiceData = [
                'type' => 10,
                'original_invoice_id' => $originalInvoice->id,
                'original_invoice_number' => $originalInvoice->pro_id,
                'client_id' => $originalInvoice->acc1,
                'store_id' => $originalInvoice->acc2,
                'employee_id' => $originalInvoice->emp_id,
                'notes' => 'تم التحويل من ' . ($originalInvoice->pro_type == 14 ? 'أمر بيع' : 'عرض سعر للعميل') . ' رقم: ' . $originalInvoice->pro_id,
                'invoice_date' => $originalInvoice->pro_date ?? now()->format('Y-m-d'),
                'accural_date' => $originalInvoice->accural_date ?? now()->format('Y-m-d'),
                'branch_id' => $originalInvoice->branch_id,
                'delivery_id' => $originalInvoice->emp2_id,
                'cash_box_id' => null,
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

                    $quantity = $item->qty_out ?? 1;
                    $price = $item->item_price ?? 0;
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
                        'notes' => $item->notes ?? '',
                        'batch_number' => $item->batch_number ?? '',
                        'expiry_date' => $item->expiry_date ?? '',
                        'serial_numbers' => $item->serial_numbers ?? '',
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
                'tax_percentage' => $originalInvoice->fat_tax_per ?? 0,
                'tax_value' => $originalInvoice->fat_tax ?? 0,
            ];

            session()->put('convert_invoice_data', $sessionData);

            if (!session()->has('convert_invoice_data')) {
                return redirect()->back()->with('error', 'فشل في حفظ بيانات التحويل');
            }

            return redirect()->route('invoices.create', [
                'type' => 10,
                'q' => md5(10)
            ])->with('success', 'تم تحميل بيانات العرض. يمكنك الآن التعديل عليها وحفظها كفاتورة مبيعات.');
        } catch (\Exception) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التحويل: ');
        }
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
}
