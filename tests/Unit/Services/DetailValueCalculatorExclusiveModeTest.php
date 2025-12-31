<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Invoice\DetailValueCalculator;
use App\Services\Invoice\DetailValueValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test Exclusive Mode for Discounts, Additional Charges, and Taxes
 *
 * Tests the two exclusive modes:
 * 1. item_level: Discounts/additional/taxes at item level only
 * 2. invoice_level: Discounts/additional/taxes at invoice level only (distributed)
 */
class DetailValueCalculatorExclusiveModeTest extends TestCase
{
    private DetailValueCalculator $calculator;
    private DetailValueValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DetailValueCalculator();
        $this->validator = new DetailValueValidator();
    }

    /**
     * Test item_level mode: Discounts/additional at item level only
     */
    public function test_item_level_mode_with_item_discounts(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,      // Item-level discount
            'additional' => 30,         // Item-level additional
        ];

        $invoiceData = [
            'fat_disc' => 0,           // Must be zero in item_level mode
            'fat_plus' => 0,           // Must be zero in item_level mode
            'discount_mode' => 'item_level',
        ];

        $invoiceSubtotal = 980; // (100 × 10) - 50 + 30

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // In item_level mode: No distribution, so distributed values are 0
        $this->assertEquals(0, $result['distributed_discount']);
        $this->assertEquals(0, $result['distributed_additional']);

        // detail_value = item_subtotal (no invoice-level adjustments)
        $this->assertEquals(980, $result['detail_value']);
        $this->assertEquals(980, $result['item_subtotal']);
    }

    /**
     * Test invoice_level mode: Discounts/additional at invoice level only
     */
    public function test_invoice_level_mode_with_invoice_discounts(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,      // Must be zero in invoice_level mode
            'additional' => 0,         // Must be zero in invoice_level mode
        ];

        $invoiceData = [
            'fat_disc' => 100,         // Invoice-level discount
            'fat_plus' => 50,          // Invoice-level additional
            'discount_mode' => 'invoice_level',
        ];

        $invoiceSubtotal = 1000; // (100 × 10)

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // In invoice_level mode: Distribution happens
        $this->assertEquals(100, $result['distributed_discount']);
        $this->assertEquals(50, $result['distributed_additional']);

        // detail_value = 1000 - 100 + 50 = 950
        $this->assertEquals(950, $result['detail_value']);
        $this->assertEquals(1000, $result['item_subtotal']);
    }

    /**
     * Test item_level mode with taxes (taxes only at item level)
     */
    public function test_item_level_mode_with_taxes(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 30,
            'item_vat_percentage' => 10,           // Item-level VAT
            'item_withholding_tax_percentage' => 2, // Item-level withholding tax
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 0,
            'vat_percentage' => 0,                // Must be zero in item_level mode
            'withholding_tax_percentage' => 0,     // Must be zero in item_level mode
            'discount_mode' => 'item_level',
        ];

        // item_value_before_taxes = (100 × 10) - 50 + 30 = 980
        // item_level_vat = 980 × 0.10 = 98
        // item_level_withholding_tax = 980 × 0.02 = 19.60
        // item_subtotal = 980 + 98 - 19.60 = 1058.40
        $invoiceSubtotal = 1058.40;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Item-level taxes
        $this->assertEquals(98, $result['item_level_vat']);
        $this->assertEquals(19.60, $result['item_level_withholding_tax']);

        // No distribution in item_level mode
        $this->assertEquals(0, $result['distributed_discount']);
        $this->assertEquals(0, $result['distributed_additional']);

        // Invoice-level taxes must be zero in item_level mode
        $this->assertEquals(0, $result['invoice_level_vat']);
        $this->assertEquals(0, $result['invoice_level_withholding_tax']);

        // detail_value = 1058.40 (no invoice-level adjustments or taxes)
        $this->assertEquals(1058.40, $result['detail_value']);
    }

    /**
     * Test invoice_level mode with taxes (taxes only at invoice level)
     */
    public function test_invoice_level_mode_with_taxes(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            'item_vat_percentage' => 0,           // Must be zero in invoice_level mode
            'item_withholding_tax_percentage' => 0, // Must be zero in invoice_level mode
        ];

        $invoiceData = [
            'fat_disc' => 100,
            'fat_plus' => 50,
            'vat_percentage' => 15,
            'withholding_tax_percentage' => 5,
            'discount_mode' => 'invoice_level',
        ];

        // item_value_before_taxes = 1000
        // item_level_vat = 0 (invoice_level mode)
        // item_level_withholding_tax = 0 (invoice_level mode)
        // item_subtotal = 1000
        $invoiceSubtotal = 1000;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Item-level taxes must be zero in invoice_level mode
        $this->assertEquals(0, $result['item_level_vat']);
        $this->assertEquals(0, $result['item_level_withholding_tax']);

        // Distribution in invoice_level mode
        $this->assertEquals(100, $result['distributed_discount']);
        $this->assertEquals(50, $result['distributed_additional']);

        // net_after_adjustments = 1000 - 100 + 50 = 950
        // invoice_level_vat = 950 × 0.15 = 142.50
        // invoice_level_withholding_tax = 950 × 0.05 = 47.50
        $this->assertEquals(142.50, $result['invoice_level_vat']);
        $this->assertEquals(47.50, $result['invoice_level_withholding_tax']);

        // detail_value = 950 + 142.50 - 47.50 = 1045
        $this->assertEquals(1045, $result['detail_value']);
    }

    /**
     * Test validation: item_level mode rejects invoice-level discounts
     */
    public function test_validation_item_level_mode_rejects_invoice_discounts(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 0,
        ];

        $invoiceData = [
            'fat_disc' => 100,         // ❌ Not allowed in item_level mode
            'fat_plus' => 0,
            'discount_mode' => 'item_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الصنف، يجب أن تكون خصومات/إضافات الفاتورة صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test validation: item_level mode rejects invoice-level additional
     */
    public function test_validation_item_level_mode_rejects_invoice_additional(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 30,
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 50,          // ❌ Not allowed in item_level mode
            'discount_mode' => 'item_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الصنف، يجب أن تكون خصومات/إضافات الفاتورة صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test validation: invoice_level mode rejects item-level discounts
     */
    public function test_validation_invoice_level_mode_rejects_item_discounts(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,     // ❌ Not allowed in invoice_level mode
            'additional' => 0,
        ];

        $invoiceData = [
            'fat_disc' => 100,
            'fat_plus' => 0,
            'discount_mode' => 'invoice_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الفاتورة، يجب أن تكون خصومات/إضافات الصنف صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test validation: invoice_level mode rejects item-level additional
     */
    public function test_validation_invoice_level_mode_rejects_item_additional(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 30,        // ❌ Not allowed in invoice_level mode
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 50,
            'discount_mode' => 'invoice_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الفاتورة، يجب أن تكون خصومات/إضافات الصنف صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test validation: Invalid discount_mode
     */
    public function test_validation_invalid_discount_mode(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 0,
            'discount_mode' => 'invalid_mode', // ❌ Invalid mode
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('وضع الخصومات غير صحيح');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test default mode: invoice_level (backward compatibility)
     */
    public function test_default_mode_is_invoice_level(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
        ];

        $invoiceData = [
            'fat_disc' => 100,
            'fat_plus' => 50,
            // discount_mode not specified - should default to 'invoice_level'
        ];

        $invoiceSubtotal = 1000;

        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Should work as invoice_level mode (distribution happens)
        $this->assertEquals(100, $result['distributed_discount']);
        $this->assertEquals(50, $result['distributed_additional']);
        $this->assertEquals(950, $result['detail_value']);
    }

    /**
     * Test multiple items in item_level mode
     */
    public function test_multiple_items_in_item_level_mode(): void
    {
        // Item 1
        $item1Data = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 30,
        ];

        // Item 2
        $item2Data = [
            'item_price' => 50,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 20,
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 0,
            'discount_mode' => 'item_level',
        ];

        // Calculate invoice subtotal
        $items = [$item1Data, $item2Data];
        $invoiceSubtotal = $this->calculator->calculateInvoiceSubtotal($items, 'item_level');

        // item1_subtotal = (100 × 10) - 50 + 30 = 980
        // item2_subtotal = (50 × 10) + 20 = 520
        // invoiceSubtotal = 980 + 520 = 1500
        $this->assertEquals(1500, $invoiceSubtotal);

        // Calculate detail_value for each item
        $result1 = $this->calculator->calculate($item1Data, $invoiceData, $invoiceSubtotal);
        $result2 = $this->calculator->calculate($item2Data, $invoiceData, $invoiceSubtotal);

        // No distribution in item_level mode
        $this->assertEquals(0, $result1['distributed_discount']);
        $this->assertEquals(0, $result1['distributed_additional']);
        $this->assertEquals(0, $result2['distributed_discount']);
        $this->assertEquals(0, $result2['distributed_additional']);

        // detail_value = item_subtotal (no adjustments)
        $this->assertEquals(980, $result1['detail_value']);
        $this->assertEquals(520, $result2['detail_value']);
    }

    /**
     * Test multiple items in invoice_level mode
     */
    public function test_multiple_items_in_invoice_level_mode(): void
    {
        // Item 1
        $item1Data = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
        ];

        // Item 2
        $item2Data = [
            'item_price' => 50,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
        ];

        $invoiceData = [
            'fat_disc' => 150,         // Total invoice discount
            'fat_plus' => 75,          // Total invoice additional
            'discount_mode' => 'invoice_level',
        ];

        // Calculate invoice subtotal
        $items = [$item1Data, $item2Data];
        $invoiceSubtotal = $this->calculator->calculateInvoiceSubtotal($items, 'invoice_level');

        // item1_subtotal = 1000
        // item2_subtotal = 500
        // invoiceSubtotal = 1500
        $this->assertEquals(1500, $invoiceSubtotal);

        // Calculate detail_value for each item
        $result1 = $this->calculator->calculate($item1Data, $invoiceData, $invoiceSubtotal);
        $result2 = $this->calculator->calculate($item2Data, $invoiceData, $invoiceSubtotal);

        // Distribution in invoice_level mode (proportional)
        // Item 1: 1000/1500 = 66.67%
        // Item 2: 500/1500 = 33.33%
        $this->assertEquals(100, $result1['distributed_discount']); // 150 × (1000/1500)
        $this->assertEquals(50, $result1['distributed_additional']); // 75 × (1000/1500)
        $this->assertEquals(50, $result2['distributed_discount']); // 150 × (500/1500)
        $this->assertEquals(25, $result2['distributed_additional']); // 75 × (500/1500)

        // detail_value = item_subtotal - distributed_discount + distributed_additional
        // Item 1: 1000 - 100 + 50 = 950
        // Item 2: 500 - 50 + 25 = 475
        $this->assertEquals(950, $result1['detail_value']);
        $this->assertEquals(475, $result2['detail_value']);
    }

    /**
     * Test validation: item_level mode rejects invoice-level VAT
     */
    public function test_validation_item_level_mode_rejects_invoice_vat(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 50,
            'additional' => 0,
            'item_vat_percentage' => 10,
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 0,
            'vat_percentage' => 15,    // ❌ Not allowed in item_level mode
            'discount_mode' => 'item_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الصنف، يجب أن تكون ضرائب الفاتورة صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test validation: item_level mode rejects invoice-level withholding tax
     */
    public function test_validation_item_level_mode_rejects_invoice_withholding_tax(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 30,
            'item_withholding_tax_percentage' => 2,
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 0,
            'withholding_tax_percentage' => 5, // ❌ Not allowed in item_level mode
            'discount_mode' => 'item_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الصنف، يجب أن تكون ضرائب الفاتورة صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test validation: invoice_level mode rejects item-level VAT
     */
    public function test_validation_invoice_level_mode_rejects_item_vat(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            'item_vat_percentage' => 10,   // ❌ Not allowed in invoice_level mode
        ];

        $invoiceData = [
            'fat_disc' => 100,
            'fat_plus' => 0,
            'vat_percentage' => 15,
            'discount_mode' => 'invoice_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الفاتورة، يجب أن تكون ضرائب الصنف صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }

    /**
     * Test validation: invoice_level mode rejects item-level withholding tax
     */
    public function test_validation_invoice_level_mode_rejects_item_withholding_tax(): void
    {
        $itemData = [
            'item_price' => 100,
            'quantity' => 10,
            'item_discount' => 0,
            'additional' => 0,
            'item_withholding_tax_percentage' => 2, // ❌ Not allowed in invoice_level mode
        ];

        $invoiceData = [
            'fat_disc' => 0,
            'fat_plus' => 50,
            'withholding_tax_percentage' => 5,
            'discount_mode' => 'invoice_level',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('في وضع الخصومات على مستوى الفاتورة، يجب أن تكون ضرائب الصنف صفر');

        $this->validator->validateExclusiveMode($itemData, $invoiceData);
    }
}
