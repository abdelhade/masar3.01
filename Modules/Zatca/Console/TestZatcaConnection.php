<?php

namespace Modules\Zatca\Console;

use Illuminate\Console\Command;
use Modules\Zatca\Services\ZatcaService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TestZatcaConnection extends Command
{
    protected $signature = 'zatca:test';
    protected $description = 'اختبار الاتصال مع ZATCA';

    public function handle()
    {
        $this->info('🚀 بدء اختبار ZATCA...');

        try {
            // 1. اختبار الاتصال
            $this->info('1️⃣  اختبار الاتصال مع الخادم...');
            $this->testConnection();

            // 2. اختبار إنتاج الفاتورة
            $this->info('2️⃣  اختبار إنتاج فاتورة تجريبية...');
            $this->testInvoiceGeneration();

            // 3. اختبار QR Code
            $this->info('3️⃣  اختبار إنتاج QR Code...');
            $this->testQRGeneration();

            // 4. اختبار الإرسال (في البيئة التجريبية)
            if (config('zatca.mode') === 'sandbox') {
                $this->info('4️⃣  اختبار إرسال الفاتورة...');
                $this->testSubmission();
            }

            $this->info('✅ جميع الاختبارات تمت بنجاح!');
        } catch (\Exception $e) {
            $this->error('❌ فشل الاختبار: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testConnection()
    {
        $client = new \GuzzleHttp\Client();
        $url = config('zatca.mode') === 'production'
            ? config('zatca.production_url')
            : config('zatca.sandbox_url');

        $response = $client->get($url . '/health');

        if ($response->getStatusCode() === 200) {
            $this->line('   ✅ الاتصال مع الخادم يعمل بنجاح');
        } else {
            throw new \Exception('فشل الاتصال مع خادم ZATCA');
        }
    }

    // private function testInvoiceGeneration()
    // {
    //     // إنشاء فاتورة تجريبية
    //     $invoice = Invoice::create([
    //         'invoice_number' => 'TEST-' . time(),
    //         'invoice_date' => now(),
    //         'customer_name' => 'عميل تجريبي',
    //         'customer_vat' => '123456789012345',
    //         'subtotal' => 100,
    //         'vat_amount' => 15,
    //         'total_amount' => 115,
    //         'currency' => 'SAR',
    //         'zatca_status' => 'draft'
    //     ]);

    //     // إضافة عنصر
    //     $invoice->items()->create([
    //         'item_name' => 'منتج تجريبي',
    //         'quantity' => 1,
    //         'unit_price' => 100,
    //         'vat_rate' => 15,
    //         'vat_amount' => 15,
    //         'total_amount' => 115
    //     ]);

    //     $zatcaService = new ZatcaService();
    //     $result = $zatcaService->generateInvoice($invoice);

    //     if ($result['success']) {
    //         $this->line('   ✅ تم إنتاج الفاتورة بنجاح');
    //         $this->line('   📄 طول XML: ' . strlen($result['xml']) . ' حرف');
    //     } else {
    //         throw new \Exception('فشل في إنتاج الفاتورة: ' . $result['error']);
    //     }
    // }

    // private function testQRGeneration()
    // {
    //     $invoice = Invoice::first();
    //     if ($invoice && $invoice->qr_code) {
    //         $this->line('   ✅ تم إنتاج QR Code بنجاح');
    //         $this->line('   🔍 طول QR: ' . strlen($invoice->qr_code) . ' حرف');
    //     } else {
    //         throw new \Exception('فشل في إنتاج QR Code');
    //     }
    // }

    // private function testSubmission()
    // {
    //     $invoice = Invoice::where('zatca_status', 'generated')->first();

    //     if (!$invoice) {
    //         $this->line('   ⚠️  لا توجد فاتورة جاهزة للإرسال');
    //         return;
    //     }

    //     $zatcaService = new ZatcaService();
    //     $result = $zatcaService->submitToZatca($invoice);

    //     if ($result['success']) {
    //         $this->line('   ✅ تم إرسال الفاتورة بنجاح');
    //         $this->line('   📨 حالة الفاتورة: ' . $invoice->fresh()->zatca_status);
    //     } else {
    //         $this->warn('   ⚠️  فشل الإرسال: ' . $result['error']);
    //     }
    // }
}
