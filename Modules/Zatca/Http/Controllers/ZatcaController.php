<?php

namespace Modules\Zatca\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Zatca\Models\ZatcaInvoice;
use Modules\Zatca\Services\ZatcaService;
use Illuminate\Support\Facades\Validator;
use Modules\Zatca\Models\ZatcaInvoiceItem;

class ZatcaController extends Controller
{
    private $zatcaService;

    public function __construct(ZatcaService $zatcaService)
    {
        $this->zatcaService = $zatcaService;
    }

    /**
     * اختبار الاتصال مع ZATCA
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->zatcaService->testConnection();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * إنشاء فاتورة تجريبية للاختبار
     */
    public function createTestInvoice(): JsonResponse
    {
        try {
            DB::beginTransaction();

            // إنشاء فاتورة تجريبية
            $invoice = ZatcaInvoice::create([
                'invoice_number' => 'TEST-' . time(),
                'invoice_date' => now(),
                'customer_name' => 'عميل تجريبي',
                'customer_vat' => '123456789012345',
                'customer_address' => 'الرياض، المملكة العربية السعودية',
                'subtotal' => 200.00,
                'vat_amount' => 30.00,
                'total_amount' => 230.00,
                'currency' => 'SAR',
                'invoice_type' => '388',
                'zatca_status' => 'draft'
            ]);

            // إضافة عناصر الفاتورة
            $items = [
                [
                    'item_name' => 'منتج تجريبي 1',
                    'quantity' => 2,
                    'unit_price' => 50.00,
                    'vat_rate' => 15.00,
                    'vat_amount' => 15.00,
                    'total_amount' => 115.00
                ],
                [
                    'item_name' => 'منتج تجريبي 2',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'vat_rate' => 15.00,
                    'vat_amount' => 15.00,
                    'total_amount' => 115.00
                ]
            ];

            foreach ($items as $item) {
                ZatcaInvoiceItem::create(array_merge($item, [
                    'zatca_invoice_id' => $invoice->id
                ]));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء فاتورة تجريبية بنجاح',
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->zatca_status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الفاتورة التجريبية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنتاج XML للفاتورة
     */
    public function generateXML(Request $request): JsonResponse
    {
        $invoice = ZatcaInvoice::findOrFail($request->invoice_id);

        $result = $this->zatcaService->generateInvoiceXML($invoice);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * إنتاج QR Code للفاتورة
     */
    public function generateQR(Request $request): JsonResponse
    {
        $invoice = ZatcaInvoice::findOrFail($request->invoice_id);

        $result = $this->zatcaService->generateQRCode($invoice);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * إرسال الفاتورة إلى ZATCA
     */
    public function submitInvoice(Request $request): JsonResponse
    {
        $invoice = ZatcaInvoice::findOrFail($request->invoice_id);

        $result = $this->zatcaService->submitInvoice($invoice);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * عرض حالة الفاتورة
     */
    public function getInvoiceStatus(Request $request): JsonResponse
    {
        $invoice = ZatcaInvoice::with('items')->findOrFail($request->invoice_id);

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer_name,
                'total_amount' => $invoice->total_amount,
                'zatca_status' => $invoice->zatca_status,
                'zatca_uuid' => $invoice->zatca_uuid,
                'has_xml' => !empty($invoice->xml_content),
                'has_qr' => !empty($invoice->qr_code),
                'items_count' => $invoice->items->count(),
                'created_at' => $invoice->created_at,
            ]
        ]);
    }

    /**
     * عملية كاملة: إنشاء وإنتاج وإرسال
     */
    public function fullProcess(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_vat' => 'nullable|string|max:15',
            'customer_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // الخطوة 1: إنشاء الفاتورة
            $invoice = $this->createInvoiceFromRequest($request);

            // الخطوة 2: إنتاج XML
            $xmlResult = $this->zatcaService->generateInvoiceXML($invoice);
            if (!$xmlResult['success']) {
                throw new \Exception('فشل في إنتاج XML: ' . $xmlResult['error']);
            }

            // الخطوة 3: إنتاج QR Code
            $qrResult = $this->zatcaService->generateQRCode($invoice);
            if (!$qrResult['success']) {
                throw new \Exception('فشل في إنتاج QR: ' . $qrResult['error']);
            }

            // الخطوة 4: إرسال إلى ZATCA (في البيئة التجريبية فقط)
            $submitResult = null;
            if (config('zatca.mode') === 'sandbox') {
                $submitResult = $this->zatcaService->submitInvoice($invoice);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تمت العملية بنجاح',
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->zatca_status,
                    'xml_generated' => $xmlResult['success'],
                    'qr_generated' => $qrResult['success'],
                    'submitted_to_zatca' => $submitResult ? $submitResult['success'] : false,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'فشلت العملية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function createInvoiceFromRequest(Request $request): ZatcaInvoice
    {
        // حساب الإجماليات
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $vatAmount = $subtotal * 0.15; // ضريبة 15%
        $totalAmount = $subtotal + $vatAmount;

        // إنشاء الفاتورة
        $invoice = ZatcaInvoice::create([
            'invoice_number' => ZatcaInvoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'customer_name' => $request->customer_name,
            'customer_vat' => $request->customer_vat ?? null,
            'customer_address' => $request->customer_address ?? null,
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'currency' => 'SAR',
            'invoice_type' => '388',
            'zatca_status' => 'draft'
        ]);

        // إضافة العناصر
        foreach ($request->items as $item) {
            $itemVat = ($item['quantity'] * $item['unit_price']) * 0.15;
            $itemTotal = ($item['quantity'] * $item['unit_price']) + $itemVat;

            ZatcaInvoiceItem::create([
                'zatca_invoice_id' => $invoice->id,
                'item_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => 15.00,
                'vat_amount' => $itemVat,
                'total_amount' => $itemTotal
            ]);
        }

        return $invoice;
    }

    /**
     * الحصول على قائمة الفواتير
     */
    public function getInvoices(Request $request): JsonResponse
    {
        $invoices = ZatcaInvoice::query()
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * الحصول على تفاصيل فاتورة معينة
     */
    public function getInvoiceDetails($id): JsonResponse
    {
        $invoice = ZatcaInvoice::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'invoice' => $invoice
        ]);
    }

    /**
     * تحديث حالة الفاتورة
     */
    public function updateInvoiceStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,submitted,approved,rejected'
        ]);

        $invoice = ZatcaInvoice::findOrFail($id);
        $invoice->update(['zatca_status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الفاتورة بنجاح'
        ]);
    }
}
