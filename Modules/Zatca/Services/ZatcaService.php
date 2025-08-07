<?php

namespace Modules\Zatca\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Modules\Zatca\Models\ZatcaInvoice;

class ZatcaService
{
    private $config;
    private $client;
    private $apiUrl;

    public function __construct()
    {
        $this->config = config('zatca');
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // فقط للتجريب
        ]);

        $this->apiUrl = $this->config['mode'] === 'production'
            ? $this->config['production_url']
            : $this->config['sandbox_url'];
    }

    /**
     * اختبار الاتصال مع ZATCA
     */
    public function testConnection(): array
    {
        try {
            $response = $this->client->get($this->apiUrl . '/health');

            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'message' => 'الاتصال يعمل بنجاح'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'فشل في الاتصال'
            ];
        }
    }

    /**
     * إنتاج XML للفاتورة
     */
    public function generateInvoiceXML(ZatcaInvoice $invoice): array
    {
        try {
            $xmlData = $this->prepareInvoiceData($invoice);
            $xml = $this->buildXML($xmlData);

            // حفظ XML في قاعدة البيانات
            $invoice->update([
                'xml_content' => $xml,
                'zatca_status' => 'xml_generated'
            ]);

            return [
                'success' => true,
                'xml' => $xml,
                'message' => 'تم إنتاج XML بنجاح'
            ];
        } catch (\Exception $e) {
            Log::error('ZATCA XML Generation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * إنتاج QR Code
     */
    public function generateQRCode(ZatcaInvoice $invoice): array
    {
        try {
            $qrData = $this->prepareQRData($invoice);
            $qrString = $this->buildQRString($qrData);

            // تحويل إلى Base64
            $qrBase64 = base64_encode($qrString);

            $invoice->update([
                'qr_code' => $qrBase64,
                'zatca_status' => 'qr_generated'
            ]);

            return [
                'success' => true,
                'qr_code' => $qrBase64,
                'message' => 'تم إنتاج QR Code بنجاح'
            ];
        } catch (\Exception $e) {
            Log::error('ZATCA QR Generation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * إرسال الفاتورة إلى ZATCA
     */
    public function submitInvoice(ZatcaInvoice $invoice): array
    {
        try {
            if (empty($invoice->xml_content)) {
                throw new \Exception('يجب إنتاج XML أولاً');
            }

            $response = $this->client->post($this->apiUrl . '/invoices/reporting/single', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Accept-Version' => 'V2',
                ],
                'json' => [
                    'invoiceHash' => $this->generateInvoiceHash($invoice->xml_content),
                    'uuid' => $this->generateUUID(),
                    'invoice' => base64_encode($invoice->xml_content)
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            $invoice->update([
                'zatca_status' => $result['validationResults']['status'] ?? 'submitted',
                'zatca_uuid' => $result['uuid'] ?? null,
                'zatca_response' => $result,
                'zatca_hash' => $this->generateInvoiceHash($invoice->xml_content)
            ]);

            return [
                'success' => true,
                'response' => $result,
                'message' => 'تم إرسال الفاتورة بنجاح'
            ];
        } catch (\Exception $e) {
            Log::error('ZATCA Submission Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * تحضير بيانات الفاتورة
     */
    private function prepareInvoiceData(ZatcaInvoice $invoice): array
    {
        return [
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->invoice_date->format('Y-m-d'),
            'issue_time' => $invoice->invoice_date->format('H:i:s') . 'Z',
            'invoice_type_code' => $invoice->invoice_type,
            'currency_code' => $invoice->currency,
            'company' => $this->config['company'],
            'customer' => [
                'name' => $invoice->customer_name,
                'vat_number' => $invoice->customer_vat,
                'address' => $invoice->customer_address,
            ],
            'items' => $invoice->items->toArray(),
            'totals' => [
                'subtotal' => $invoice->subtotal,
                'vat_amount' => $invoice->vat_amount,
                'total' => $invoice->total_amount,
            ]
        ];
    }

    /**
     * بناء XML
     */
    private function buildXML(array $data): string
    {
        // هنا سنضع منطق بناء XML حسب مواصفات ZATCA
        // هذا مثال مبسط - يجب استخدام مكتبة Salla الفعلية

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl-schema:xsd:Invoice-2">';
        $xml .= '<ID>' . $data['invoice_number'] . '</ID>';
        $xml .= '<IssueDate>' . $data['issue_date'] . '</IssueDate>';
        $xml .= '<IssueTime>' . $data['issue_time'] . '</IssueTime>';
        $xml .= '<InvoiceTypeCode>' . $data['invoice_type_code'] . '</InvoiceTypeCode>';
        $xml .= '<DocumentCurrencyCode>' . $data['currency_code'] . '</DocumentCurrencyCode>';
        // ... باقي عناصر XML
        $xml .= '</Invoice>';

        return $xml;
    }

    /**
     * تحضير بيانات QR
     */
    private function prepareQRData(ZatcaInvoice $invoice): array
    {
        return [
            'seller_name' => $this->config['company']['name'],
            'vat_number' => $this->config['company']['vat_number'],
            'timestamp' => $invoice->invoice_date->format('Y-m-d\TH:i:s\Z'),
            'total_amount' => $invoice->total_amount,
            'vat_amount' => $invoice->vat_amount,
        ];
    }

    /**
     * بناء نص QR
     */
    private function buildQRString(array $data): string
    {
        $qr = '';
        $qr .= $this->addQRField(1, $data['seller_name']);
        $qr .= $this->addQRField(2, $data['vat_number']);
        $qr .= $this->addQRField(3, $data['timestamp']);
        $qr .= $this->addQRField(4, $data['total_amount']);
        $qr .= $this->addQRField(5, $data['vat_amount']);

        return $qr;
    }

    /**
     * إضافة حقل QR
     */
    private function addQRField(int $tag, string $value): string
    {
        $length = strlen($value);
        return chr($tag) . chr($length) . $value;
    }

    /**
     * إنتاج hash للفاتورة
     */
    private function generateInvoiceHash(string $xml): string
    {
        return hash('sha256', $xml);
    }

    /**
     * إنتاج UUID
     */
    private function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
