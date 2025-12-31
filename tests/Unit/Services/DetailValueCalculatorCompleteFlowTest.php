<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Invoice\DetailValueCalculator;
use PHPUnit\Framework\TestCase;

/**
 * اختبار شامل للتأكد من أن جميع الحسابات على مستوى الصنف الواحد
 * تتبع الترتيب والمبادئ الصحيحة
 *
 * الترتيب الصحيح:
 * 1. حساب قيمة الصنف = (السعر × الكمية) - خصم الصنف + إضافي الصنف
 * 2. توزيع خصم الفاتورة على الصنف
 * 3. توزيع إضافي الفاتورة على الصنف
 * 4. حساب الصافي = قيمة الصنف - خصم الفاتورة + إضافي الفاتورة
 * 5. حساب ضريبة القيمة المضافة من الصافي
 * 6. حساب الخصم الضريبي من الصافي
 * 7. التكلفة النهائية = الصافي + الضريبة - الخصم الضريبي
 */
class DetailValueCalculatorCompleteFlowTest extends TestCase
{
    private DetailValueCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DetailValueCalculator();
    }

    /**
     * اختبار شامل: صنف واحد مع جميع أنواع الخصومات والإضافات والضرائب
     *
     * @test
     */
    public function it_calculates_single_item_with_all_adjustments_correctly(): void
    {
        // الترتيب: صنف واحد بسعر 100 جنيه × 10 وحدات = 1000 جنيه
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,    // خصم على مستوى الصنف
            'additional' => 30,       // إضافي على مستوى الصنف
        ];

        $invoiceData = [
            'fat_disc' => 100,                    // خصم الفاتورة
            'fat_plus' => 50,                     // إضافي الفاتورة
            'vat_percentage' => 15,               // ضريبة القيمة المضافة 15%
            'withholding_tax_percentage' => 5,   // الخصم الضريبي 5%
        ];

        // إجمالي الفاتورة قبل خصم/إضافي الفاتورة
        $invoiceSubtotal = 980; // (100 × 10) - 50 + 30 = 980

        // تنفيذ الحساب
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // التحقق من الخطوات:
        
        // 1. قيمة الصنف = (100 × 10) - 50 + 30 = 980
        $this->assertEquals(980.0, $result['item_subtotal'], 'خطأ في حساب قيمة الصنف');

        // 2. خصم الفاتورة الموزع = 100 (كل الخصم لأن الصنف الوحيد)
        $this->assertEquals(100.0, $result['distributed_discount'], 'خطأ في توزيع خصم الفاتورة');

        // 3. إضافي الفاتورة الموزع = 50 (كل الإضافي لأن الصنف الوحيد)
        $this->assertEquals(50.0, $result['distributed_additional'], 'خطأ في توزيع إضافي الفاتورة');

        // 4. الصافي بعد التعديلات = 980 - 100 + 50 = 930
        $netAfterAdjustments = 930.0;

        // 5. ضريبة القيمة المضافة = 930 × 0.15 = 139.50
        $this->assertEquals(139.50, $result['invoice_level_vat'], 'خطأ في حساب ضريبة القيمة المضافة من الصافي');

        // 6. الخصم الضريبي = 930 × 0.05 = 46.50
        $this->assertEquals(46.50, $result['invoice_level_withholding_tax'], 'خطأ في حساب الخصم الضريبي من الصافي');

        // 7. التكلفة النهائية = 930 + 139.50 - 46.50 = 1023
        $this->assertEquals(1023.0, $result['detail_value'], 'خطأ في حساب التكلفة النهائية');

        // التحقق من أن الضرائب تم حسابها من الصافي وليس من القيمة الأصلية
        $vatFromOriginal = 980 * 0.15; // 147 (خطأ)
        $vatFromNet = 930 * 0.15;      // 139.50 (صحيح)
        
        $this->assertNotEquals($vatFromOriginal, $result['invoice_level_vat'], 
            'الضريبة يجب أن تُحسب من الصافي وليس من القيمة الأصلية');
        $this->assertEquals($vatFromNet, $result['invoice_level_vat'], 
            'الضريبة يجب أن تُحسب من الصافي');
    }

    /**
     * اختبار: صنف واحد مع خصم على مستوى الصنف فقط
     *
     * @test
     */
    public function it_handles_item_level_discount_only(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 5,
            'item_discount' => 100,  // خصم 100 جنيه على الصنف
            'additional' => 0,
        ];

        $invoiceData = [
            'vat_percentage' => 15,
        ];

        $invoiceSubtotal = 400; // (100 × 5) - 100 = 400

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // قيمة الصنف = 500 - 100 = 400
        $this->assertEquals(400.0, $result['item_subtotal']);
        
        // لا يوجد خصم فاتورة
        $this->assertEquals(0.0, $result['distributed_discount']);
        
        // الصافي = 400
        // الضريبة = 400 × 0.15 = 60
        $this->assertEquals(60.0, $result['invoice_level_vat']);
        
        // التكلفة النهائية = 400 + 60 = 460
        $this->assertEquals(460.0, $result['detail_value']);
    }

    /**
     * اختبار: صنف واحد مع إضافي على مستوى الصنف فقط
     *
     * @test
     */
    public function it_handles_item_level_additional_only(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 5,
            'item_discount' => 0,
            'additional' => 50,  // إضافي 50 جنيه على الصنف
        ];

        $invoiceData = [
            'withholding_tax_percentage' => 5,
        ];

        $invoiceSubtotal = 550; // (100 × 5) + 50 = 550

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // قيمة الصنف = 500 + 50 = 550
        $this->assertEquals(550.0, $result['item_subtotal']);
        
        // لا يوجد إضافي فاتورة
        $this->assertEquals(0.0, $result['distributed_additional']);
        
        // الصافي = 550
        // الخصم الضريبي = 550 × 0.05 = 27.50
        $this->assertEquals(27.50, $result['invoice_level_withholding_tax']);
        
        // التكلفة النهائية = 550 - 27.50 = 522.50
        $this->assertEquals(522.50, $result['detail_value']);
    }

    /**
     * اختبار: صنف واحد مع خصم وإضافي على مستوى الصنف والفاتورة
     *
     * @test
     */
    public function it_handles_both_item_and_invoice_level_adjustments(): void
    {
        $itemData = [
            'item_price' => 200,
            'quantity' => 5,
            'item_discount' => 100,  // خصم على الصنف
            'additional' => 50,      // إضافي على الصنف
        ];

        $invoiceData = [
            'fat_disc' => 50,        // خصم الفاتورة
            'fat_plus' => 25,        // إضافي الفاتورة
            'vat_percentage' => 15,
            'withholding_tax_percentage' => 5,
        ];

        // قيمة الصنف = (200 × 5) - 100 + 50 = 950
        $invoiceSubtotal = 950;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // 1. قيمة الصنف = 950
        $this->assertEquals(950.0, $result['item_subtotal']);

        // 2. خصم الفاتورة = 50
        $this->assertEquals(50.0, $result['distributed_discount']);

        // 3. إضافي الفاتورة = 25
        $this->assertEquals(25.0, $result['distributed_additional']);

        // 4. الصافي = 950 - 50 + 25 = 925
        $netAfterAdjustments = 925.0;

        // 5. الضريبة = 925 × 0.15 = 138.75
        $this->assertEquals(138.75, $result['invoice_level_vat']);

        // 6. الخصم الضريبي = 925 × 0.05 = 46.25
        $this->assertEquals(46.25, $result['invoice_level_withholding_tax']);

        // 7. التكلفة النهائية = 925 + 138.75 - 46.25 = 1017.50
        $this->assertEquals(1017.50, $result['detail_value']);
    }

    /**
     * اختبار: التأكد من أن الضرائب تُحسب من الصافي في جميع الحالات
     *
     * @test
     */
    public function it_always_calculates_taxes_from_net_value(): void
    {
        // حالة 1: مع خصم فاتورة
        $itemData1 = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
        ];

        $invoiceData1 = [
            'fat_disc' => 200,
            'vat_percentage' => 15,
        ];

        $result1 = $this->calculator->calculate($itemData1, $invoiceData1, 1000);

        // الصافي = 1000 - 200 = 800
        // الضريبة = 800 × 0.15 = 120 (وليس 1000 × 0.15 = 150)
        $this->assertEquals(120.0, $result1['invoice_level_vat'], 
            'الضريبة يجب أن تُحسب من الصافي بعد خصم الفاتورة');

        // حالة 2: مع إضافي فاتورة
        $itemData2 = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
        ];

        $invoiceData2 = [
            'fat_plus' => 200,
            'withholding_tax_percentage' => 5,
        ];

        $result2 = $this->calculator->calculate($itemData2, $invoiceData2, 1000);

        // الصافي = 1000 + 200 = 1200
        // الخصم الضريبي = 1200 × 0.05 = 60 (وليس 1000 × 0.05 = 50)
        $this->assertEquals(60.0, $result2['invoice_level_withholding_tax'], 
            'الخصم الضريبي يجب أن يُحسب من الصافي بعد إضافي الفاتورة');
    }

    /**
     * اختبار: صنف واحد مع نسب مئوية للخصم والإضافي على مستوى الفاتورة
     *
     * @test
     */
    public function it_handles_percentage_based_invoice_adjustments(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
        ];

        $invoiceData = [
            'fat_disc_per' => 10,    // خصم 10%
            'fat_plus_per' => 5,     // إضافي 5%
            'vat_percentage' => 15,
        ];

        $invoiceSubtotal = 1000;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // قيمة الصنف = 1000
        $this->assertEquals(1000.0, $result['item_subtotal']);

        // خصم الفاتورة = 1000 × 0.10 = 100
        $this->assertEquals(100.0, $result['distributed_discount']);

        // إضافي الفاتورة = 1000 × 0.05 = 50
        $this->assertEquals(50.0, $result['distributed_additional']);

        // الصافي = 1000 - 100 + 50 = 950
        // الضريبة = 950 × 0.15 = 142.50
        $this->assertEquals(142.50, $result['invoice_level_vat']);

        // التكلفة النهائية = 950 + 142.50 = 1092.50
        $this->assertEquals(1092.50, $result['detail_value']);
    }

    /**
     * اختبار: التحقق من التفاصيل (breakdown) تحتوي على جميع الخطوات
     *
     * @test
     */
    public function it_includes_all_calculation_steps_in_breakdown(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 5,
            'item_discount' => 50,
            'additional' => 25,
        ];

        $invoiceData = [
            'fat_disc' => 30,
            'fat_plus' => 15,
            'vat_percentage' => 15,
            'withholding_tax_percentage' => 5,
        ];

        $invoiceSubtotal = 475; // (100 × 5) - 50 + 25 = 475

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // التحقق من وجود جميع الحقول في breakdown
        $this->assertArrayHasKey('breakdown', $result);
        $breakdown = $result['breakdown'];

        $this->assertArrayHasKey('item_price', $breakdown);
        $this->assertArrayHasKey('quantity', $breakdown);
        $this->assertArrayHasKey('item_discount', $breakdown);
        $this->assertArrayHasKey('item_additional', $breakdown);
        $this->assertArrayHasKey('item_subtotal', $breakdown);
        $this->assertArrayHasKey('distributed_discount', $breakdown);
        $this->assertArrayHasKey('distributed_additional', $breakdown);
        $this->assertArrayHasKey('invoice_level_vat', $breakdown);
        $this->assertArrayHasKey('invoice_level_withholding_tax', $breakdown);
        $this->assertArrayHasKey('detail_value', $breakdown);

        // التحقق من القيم
        $this->assertEquals(100, $breakdown['item_price']);
        $this->assertEquals(5, $breakdown['quantity']);
        $this->assertEquals(50, $breakdown['item_discount']);
        $this->assertEquals(25, $breakdown['item_additional']);
    }
}
