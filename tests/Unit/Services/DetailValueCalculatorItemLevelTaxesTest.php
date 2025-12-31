<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Invoice\DetailValueCalculator;
use PHPUnit\Framework\TestCase;

/**
 * اختبارات شاملة للضرائب على مستوى الصنف
 *
 * الترتيب الصحيح:
 * 1. قيمة الصنف = (السعر × الكمية) - خصم الصنف + إضافي الصنف
 * 2. ضريبة الصنف = قيمة الصنف × نسبة ضريبة الصنف
 * 3. خصم ضريبي الصنف = قيمة الصنف × نسبة خصم ضريبي الصنف
 * 4. item_subtotal = قيمة الصنف + ضريبة الصنف - خصم ضريبي الصنف
 * 5. توزيع خصم/إضافي الفاتورة
 * 6. net_after_adjustments = item_subtotal - خصم الفاتورة + إضافي الفاتورة
 * 7. ضريبة الفاتورة = net_after_adjustments × نسبة ضريبة الفاتورة
 * 8. خصم ضريبي الفاتورة = net_after_adjustments × نسبة خصم ضريبي الفاتورة
 * 9. detail_value = net_after_adjustments + ضريبة الفاتورة - خصم ضريبي الفاتورة
 */
class DetailValueCalculatorItemLevelTaxesTest extends TestCase
{
    private DetailValueCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DetailValueCalculator();
    }

    /**
     * اختبار: صنف مع ضريبة على مستوى الصنف فقط (نسبة مئوية)
     *
     * @test
     */
    public function it_calculates_item_level_vat_percentage(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 30,
            'item_vat_percentage' => 10, // 10% VAT على مستوى الصنف
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode for item-level taxes
        ];
        $invoiceSubtotal = 1078; // سيتم حسابه

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // قيمة الصنف قبل الضرائب = (100 × 10) - 50 + 30 = 980
        $this->assertEquals(980.0, $result['breakdown']['item_value_before_taxes']);

        // ضريبة الصنف = 980 × 0.10 = 98
        $this->assertEquals(98.0, $result['item_level_vat']);

        // item_subtotal = 980 + 98 = 1078
        $this->assertEquals(1078.0, $result['item_subtotal']);

        // detail_value = 1078 (لا توجد تعديلات على مستوى الفاتورة)
        $this->assertEquals(1078.0, $result['detail_value']);
    }

    /**
     * اختبار: صنف مع خصم ضريبي على مستوى الصنف فقط (نسبة مئوية)
     *
     * @test
     */
    public function it_calculates_item_level_withholding_tax_percentage(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 30,
            'item_withholding_tax_percentage' => 2, // 2% خصم ضريبي على مستوى الصنف
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode for item-level taxes
        ];
        $invoiceSubtotal = 960.40;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // قيمة الصنف قبل الضرائب = 980
        $this->assertEquals(980.0, $result['breakdown']['item_value_before_taxes']);

        // خصم ضريبي الصنف = 980 × 0.02 = 19.60
        $this->assertEquals(19.60, $result['item_level_withholding_tax']);

        // item_subtotal = 980 - 19.60 = 960.40
        $this->assertEquals(960.40, $result['item_subtotal']);

        // detail_value = 960.40
        $this->assertEquals(960.40, $result['detail_value']);
    }

    /**
     * اختبار: صنف مع كلا الضريبتين على مستوى الصنف
     *
     * @test
     */
    public function it_calculates_both_item_level_taxes(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 30,
            'item_vat_percentage' => 10,
            'item_withholding_tax_percentage' => 2,
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode for item-level taxes
        ];
        $invoiceSubtotal = 1058.40;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // قيمة الصنف قبل الضرائب = 980
        $this->assertEquals(980.0, $result['breakdown']['item_value_before_taxes']);

        // ضريبة الصنف = 980 × 0.10 = 98
        $this->assertEquals(98.0, $result['item_level_vat']);

        // خصم ضريبي الصنف = 980 × 0.02 = 19.60
        $this->assertEquals(19.60, $result['item_level_withholding_tax']);

        // item_subtotal = 980 + 98 - 19.60 = 1058.40
        $this->assertEquals(1058.40, $result['item_subtotal']);

        // detail_value = 1058.40
        $this->assertEquals(1058.40, $result['detail_value']);
    }

    /**
     * اختبار شامل: صنف مع جميع أنواع الضرائب على مستوى الصنف فقط
     *
     * @test
     */
    public function it_calculates_complete_flow_with_all_taxes(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 30,
            'item_vat_percentage' => 10,
            'item_withholding_tax_percentage' => 2,
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode only
        ];

        $invoiceSubtotal = 1058.40;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // 1. قيمة الصنف قبل الضرائب = 980
        $this->assertEquals(980.0, $result['breakdown']['item_value_before_taxes']);

        // 2. ضريبة الصنف = 98
        $this->assertEquals(98.0, $result['item_level_vat']);

        // 3. خصم ضريبي الصنف = 19.60
        $this->assertEquals(19.60, $result['item_level_withholding_tax']);

        // 4. item_subtotal = 1058.40
        $this->assertEquals(1058.40, $result['item_subtotal']);

        // 5. لا توجد تعديلات على مستوى الفاتورة في وضع item_level
        $this->assertEquals(0.0, $result['distributed_discount']);
        $this->assertEquals(0.0, $result['distributed_additional']);
        $this->assertEquals(0.0, $result['invoice_level_vat']);
        $this->assertEquals(0.0, $result['invoice_level_withholding_tax']);

        // 6. detail_value = 1058.40 (no invoice-level adjustments)
        $this->assertEquals(1058.40, $result['detail_value']);
    }

    /**
     * اختبار: مبلغ ثابت للضريبة على مستوى الصنف
     *
     * @test
     */
    public function it_uses_fixed_amount_for_item_level_vat(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            'item_vat_value' => 150, // مبلغ ثابت
            'item_vat_percentage' => 10, // يجب تجاهله
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode for item-level taxes
        ];
        $invoiceSubtotal = 1150;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // يجب استخدام المبلغ الثابت (150) وليس النسبة المئوية (100)
        $this->assertEquals(150.0, $result['item_level_vat']);
        $this->assertEquals(1150.0, $result['detail_value']);
    }

    /**
     * اختبار: بعض الأصناف لها ضرائب على مستوى الصنف والبعض لا
     *
     * @test
     */
    public function it_handles_mixed_items_with_and_without_item_level_taxes(): void
    {
        // الصنف أ: له ضرائب على مستوى الصنف
        $itemA = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            'item_vat_percentage' => 10,
            'item_withholding_tax_percentage' => 2,
        ];

        // الصنف ب: ليس له ضرائب على مستوى الصنف
        $itemB = [
            'item_price' => 50,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            // لا توجد ضرائب على مستوى الصنف
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode
        ];

        // حساب الصنف أ
        $resultA = $this->calculator->calculate($itemA, $invoiceData, 1580);
        
        // الصنف أ: له ضرائب على مستوى الصنف
        $this->assertEquals(100.0, $resultA['item_level_vat']); // 1000 × 0.10
        $this->assertEquals(20.0, $resultA['item_level_withholding_tax']); // 1000 × 0.02
        $this->assertEquals(1080.0, $resultA['item_subtotal']); // 1000 + 100 - 20

        // حساب الصنف ب
        $resultB = $this->calculator->calculate($itemB, $invoiceData, 1580);
        
        // الصنف ب: ليس له ضرائب على مستوى الصنف
        $this->assertEquals(0.0, $resultB['item_level_vat']);
        $this->assertEquals(0.0, $resultB['item_level_withholding_tax']);
        $this->assertEquals(500.0, $resultB['item_subtotal']); // 500 فقط
    }

    /**
     * اختبار: حساب إجمالي الفاتورة مع ضرائب على مستوى الصنف
     *
     * @test
     */
    public function it_calculates_invoice_subtotal_with_item_level_taxes(): void
    {
        $items = [
            [
                'item_price' => 100,
                'quantity' => 10,
                'item_discount' => 50,
                'additional' => 30,
                'item_vat_percentage' => 10,
                'item_withholding_tax_percentage' => 2,
            ],
            [
                'item_price' => 50,
                'quantity' => 10,
                'item_discount' => 0,
                'additional' => 0,
                // لا توجد ضرائب على مستوى الصنف
            ],
        ];

        $invoiceSubtotal = $this->calculator->calculateInvoiceSubtotal($items, 'item_level');

        // الصنف 1: 980 + 98 - 19.60 = 1058.40
        // الصنف 2: 500 + 0 - 0 = 500
        // الإجمالي: 1558.40
        $this->assertEquals(1558.40, $invoiceSubtotal);
    }

    /**
     * اختبار: الضرائب على مستوى الصنف تُحسب من الصافي بعد الخصم/الإضافي
     *
     * @test
     */
    public function it_calculates_item_level_taxes_from_net_value(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 200, // خصم كبير
            'additional' => 100,
            'item_vat_percentage' => 10,
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode for item-level taxes
        ];
        $invoiceSubtotal = 990;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // قيمة الصنف قبل الضرائب = 1000 - 200 + 100 = 900
        $this->assertEquals(900.0, $result['breakdown']['item_value_before_taxes']);

        // ضريبة الصنف = 900 × 0.10 = 90 (من الصافي وليس من 1000)
        $this->assertEquals(90.0, $result['item_level_vat']);

        // التأكد من أنها ليست من القيمة الأصلية
        $vatFromOriginal = 1000 * 0.10; // 100 (خطأ)
        $this->assertNotEquals($vatFromOriginal, $result['item_level_vat']);
    }

    /**
     * اختبار: صنف بدون أي ضرائب على مستوى الصنف
     *
     * @test
     */
    public function it_handles_item_without_item_level_taxes(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            // لا توجد ضرائب على مستوى الصنف
        ];

        $invoiceData = [
            'vat_percentage' => 15,
        ];

        $invoiceSubtotal = 1000;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // لا توجد ضرائب على مستوى الصنف
        $this->assertEquals(0.0, $result['item_level_vat']);
        $this->assertEquals(0.0, $result['item_level_withholding_tax']);

        // item_subtotal = 1000 (بدون ضرائب على مستوى الصنف)
        $this->assertEquals(1000.0, $result['item_subtotal']);

        // ضريبة الفاتورة = 1000 × 0.15 = 150
        $this->assertEquals(150.0, $result['invoice_level_vat']);

        // detail_value = 1000 + 150 = 1150
        $this->assertEquals(1150.0, $result['detail_value']);
    }

    /**
     * اختبار: التفاصيل (breakdown) تحتوي على جميع الحقول الجديدة
     *
     * @test
     */
    public function it_includes_item_level_taxes_in_breakdown(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            'item_vat_percentage' => 10,
            'item_withholding_tax_percentage' => 2,
        ];

        $invoiceData = [
            'discount_mode' => 'item_level', // Item-level mode for item-level taxes
        ];
        $invoiceSubtotal = 1080;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // التحقق من وجود الحقول الجديدة
        $this->assertArrayHasKey('item_value_before_taxes', $result['breakdown']);
        $this->assertArrayHasKey('item_level_vat', $result['breakdown']);
        $this->assertArrayHasKey('item_level_withholding_tax', $result['breakdown']);

        $this->assertEquals(1000.0, $result['breakdown']['item_value_before_taxes']);
        $this->assertEquals(100.0, $result['breakdown']['item_level_vat']);
        $this->assertEquals(20.0, $result['breakdown']['item_level_withholding_tax']);
    }
}
