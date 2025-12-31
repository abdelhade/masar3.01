<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Invoice\DetailValueCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test VAT and Withholding Tax distribution in DetailValueCalculator
 *
 * IMPORTANT: VAT and Withholding Tax are calculated from the NET VALUE
 * AFTER applying discounts and additional charges.
 *
 * Formula:
 * 1. item_subtotal = (item_price × quantity) - item_discount + item_additional
 * 2. net_after_adjustments = item_subtotal - distributed_discount + distributed_additional
 * 3. distributed_vat = net_after_adjustments × (vat_percentage / 100)
 * 4. distributed_withholding_tax = net_after_adjustments × (withholding_tax_percentage / 100)
 * 5. detail_value = net_after_adjustments + distributed_vat - distributed_withholding_tax
 */
class DetailValueCalculatorTaxTest extends TestCase
{
    private DetailValueCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DetailValueCalculator();
    }

    /**
     * Test VAT distribution with fixed amount
     *
     * @test
     */
    public function it_distributes_vat_fixed_amount_proportionally(): void
    {
        // Arrange: Invoice with 2 items and 100 EGP VAT
        $items = [
            ['item_price' => 100, 'quantity' => 1], // 100 EGP (50% of total)
            ['item_price' => 100, 'quantity' => 1], // 100 EGP (50% of total)
        ];

        $invoiceData = [
            'vat_value' => 100, // 100 EGP VAT to distribute
        ];

        $invoiceSubtotal = $this->calculator->calculateInvoiceSubtotal($items, 'invoice_level');

        // Act: Calculate detail_value for first item
        $result = $this->calculator->calculate($items[0], $invoiceData, $invoiceSubtotal);

        // Assert: VAT should be distributed proportionally (50 EGP to first item)
        $this->assertEquals(50.0, $result['invoice_level_vat']);
        $this->assertEquals(150.0, $result['detail_value']); // 100 + 50 VAT
    }

    /**
     * Test VAT distribution with percentage
     *
     * @test
     */
    public function it_distributes_vat_percentage_correctly(): void
    {
        // Arrange: Invoice with 15% VAT
        $itemData = ['item_price' => 100, 'quantity' => 2]; // 200 EGP subtotal

        $invoiceData = [
            'vat_percentage' => 15, // 15% VAT
        ];

        $invoiceSubtotal = 200;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: 15% of 200 = 30 EGP VAT
        // Since there's no discount/additional, net = 200
        $this->assertEquals(30.0, $result['invoice_level_vat']);
        $this->assertEquals(230.0, $result['detail_value']); // 200 + 30 VAT
    }

    /**
     * Test Withholding Tax distribution with fixed amount
     *
     * @test
     */
    public function it_distributes_withholding_tax_fixed_amount_proportionally(): void
    {
        // Arrange: Invoice with 2 items and 50 EGP withholding tax
        $items = [
            ['item_price' => 100, 'quantity' => 1], // 100 EGP (50% of total)
            ['item_price' => 100, 'quantity' => 1], // 100 EGP (50% of total)
        ];

        $invoiceData = [
            'withholding_tax_value' => 50, // 50 EGP withholding tax to distribute
        ];

        $invoiceSubtotal = $this->calculator->calculateInvoiceSubtotal($items, 'invoice_level');

        // Act: Calculate detail_value for first item
        $result = $this->calculator->calculate($items[0], $invoiceData, $invoiceSubtotal);

        // Assert: Withholding tax should be distributed proportionally (25 EGP to first item)
        $this->assertEquals(25.0, $result['invoice_level_withholding_tax']);
        $this->assertEquals(75.0, $result['detail_value']); // 100 - 25 withholding tax
    }

    /**
     * Test Withholding Tax distribution with percentage
     *
     * @test
     */
    public function it_distributes_withholding_tax_percentage_correctly(): void
    {
        // Arrange: Invoice with 5% withholding tax
        $itemData = ['item_price' => 100, 'quantity' => 2]; // 200 EGP subtotal

        $invoiceData = [
            'withholding_tax_percentage' => 5, // 5% withholding tax
        ];

        $invoiceSubtotal = 200;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: 5% of 200 = 10 EGP withholding tax
        $this->assertEquals(10.0, $result['invoice_level_withholding_tax']);
        $this->assertEquals(190.0, $result['detail_value']); // 200 - 10 withholding tax
    }

    /**
     * Test combined VAT and Withholding Tax
     *
     * @test
     */
    public function it_handles_both_vat_and_withholding_tax_together(): void
    {
        // Arrange: Invoice with both VAT and withholding tax
        $itemData = ['item_price' => 100, 'quantity' => 1]; // 100 EGP

        $invoiceData = [
            'vat_percentage' => 15, // 15% VAT = 15 EGP
            'withholding_tax_percentage' => 5, // 5% withholding tax = 5 EGP
        ];

        $invoiceSubtotal = 100;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert
        $this->assertEquals(15.0, $result['invoice_level_vat']);
        $this->assertEquals(5.0, $result['invoice_level_withholding_tax']);
        $this->assertEquals(110.0, $result['detail_value']); // 100 + 15 VAT - 5 withholding tax
    }

    /**
     * Test taxes calculated from NET after discount and additional
     * This is the CRITICAL test for the correct implementation
     *
     * @test
     */
    public function it_calculates_taxes_from_net_after_discount_and_additional(): void
    {
        // Arrange: Complex invoice with all adjustments
        $itemData = [
            'item_price' => 100,
            'quantity' => 10, // 1000 EGP
            'item_discount' => 0,
            'additional' => 0,
        ];

        $invoiceData = [
            'fat_disc' => 100, // Invoice discount
            'fat_plus' => 50, // Invoice additional
            'vat_percentage' => 15, // VAT
            'withholding_tax_percentage' => 5, // Withholding tax
        ];

        $invoiceSubtotal = 1000;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert
        // Item subtotal: 1000
        // Distributed discount: 100
        // Distributed additional: 50
        // Net after adjustments: 1000 - 100 + 50 = 950
        // Distributed VAT: 950 * 0.15 = 142.50
        // Distributed withholding tax: 950 * 0.05 = 47.50
        // Detail value: 950 + 142.50 - 47.50 = 1045

        $this->assertEquals(1000.0, $result['item_subtotal']);
        $this->assertEquals(100.0, $result['distributed_discount']);
        $this->assertEquals(50.0, $result['distributed_additional']);
        $this->assertEquals(142.50, $result['invoice_level_vat']);
        $this->assertEquals(47.50, $result['invoice_level_withholding_tax']);
        $this->assertEquals(1045.0, $result['detail_value']);
    }

    /**
     * Test that fixed amount takes precedence over percentage for VAT
     *
     * @test
     */
    public function it_prioritizes_vat_fixed_amount_over_percentage(): void
    {
        // Arrange: Both VAT amount and percentage provided
        $itemData = ['item_price' => 100, 'quantity' => 1];

        $invoiceData = [
            'vat_value' => 50, // Fixed amount (should be used)
            'vat_percentage' => 15, // Percentage (should be ignored)
        ];

        $invoiceSubtotal = 100;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: Should use fixed amount (50), not percentage (15)
        $this->assertEquals(50.0, $result['invoice_level_vat']);
        $this->assertEquals(150.0, $result['detail_value']);
    }

    /**
     * Test that fixed amount takes precedence over percentage for withholding tax
     *
     * @test
     */
    public function it_prioritizes_withholding_tax_fixed_amount_over_percentage(): void
    {
        // Arrange: Both withholding tax amount and percentage provided
        $itemData = ['item_price' => 100, 'quantity' => 1];

        $invoiceData = [
            'withholding_tax_value' => 20, // Fixed amount (should be used)
            'withholding_tax_percentage' => 5, // Percentage (should be ignored)
        ];

        $invoiceSubtotal = 100;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: Should use fixed amount (20), not percentage (5)
        $this->assertEquals(20.0, $result['invoice_level_withholding_tax']);
        $this->assertEquals(80.0, $result['detail_value']);
    }

    /**
     * Test zero VAT and withholding tax
     *
     * @test
     */
    public function it_handles_zero_taxes_correctly(): void
    {
        // Arrange: No taxes
        $itemData = ['item_price' => 100, 'quantity' => 1];

        $invoiceData = [
            'vat_value' => 0,
            'vat_percentage' => 0,
            'withholding_tax_value' => 0,
            'withholding_tax_percentage' => 0,
        ];

        $invoiceSubtotal = 100;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: No taxes should be distributed
        $this->assertEquals(0.0, $result['invoice_level_vat']);
        $this->assertEquals(0.0, $result['invoice_level_withholding_tax']);
        $this->assertEquals(100.0, $result['detail_value']);
    }

    /**
     * Test missing tax fields (should default to 0)
     *
     * @test
     */
    public function it_handles_missing_tax_fields_gracefully(): void
    {
        // Arrange: Invoice data without tax fields
        $itemData = ['item_price' => 100, 'quantity' => 1];

        $invoiceData = []; // No tax fields

        $invoiceSubtotal = 100;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: Should default to 0
        $this->assertEquals(0.0, $result['invoice_level_vat']);
        $this->assertEquals(0.0, $result['invoice_level_withholding_tax']);
        $this->assertEquals(100.0, $result['detail_value']);
    }

    /**
     * Test proportional distribution with multiple items
     *
     * @test
     */
    public function it_distributes_taxes_proportionally_across_multiple_items(): void
    {
        // Arrange: Invoice with 3 items of different values
        $items = [
            ['item_price' => 100, 'quantity' => 1], // 100 EGP (25% of 400)
            ['item_price' => 150, 'quantity' => 1], // 150 EGP (37.5% of 400)
            ['item_price' => 150, 'quantity' => 1], // 150 EGP (37.5% of 400)
        ];

        $invoiceData = [
            'vat_value' => 60, // 60 EGP VAT
            'withholding_tax_value' => 20, // 20 EGP withholding tax
        ];

        $invoiceSubtotal = $this->calculator->calculateInvoiceSubtotal($items, 'invoice_level');

        // Act: Calculate for each item
        $results = [];
        foreach ($items as $item) {
            $results[] = $this->calculator->calculate($item, $invoiceData, $invoiceSubtotal);
        }

        // Assert: Check proportional distribution
        // Item 1: 100/400 = 25% → VAT: 15, Withholding: 5
        $this->assertEquals(15.0, $results[0]['invoice_level_vat']);
        $this->assertEquals(5.0, $results[0]['invoice_level_withholding_tax']);
        $this->assertEquals(110.0, $results[0]['detail_value']); // 100 + 15 - 5

        // Item 2: 150/400 = 37.5% → VAT: 22.5, Withholding: 7.5
        $this->assertEquals(22.5, $results[1]['invoice_level_vat']);
        $this->assertEquals(7.5, $results[1]['invoice_level_withholding_tax']);
        $this->assertEquals(165.0, $results[1]['detail_value']); // 150 + 22.5 - 7.5

        // Item 3: Same as item 2
        $this->assertEquals(22.5, $results[2]['invoice_level_vat']);
        $this->assertEquals(7.5, $results[2]['invoice_level_withholding_tax']);
        $this->assertEquals(165.0, $results[2]['detail_value']);

        // Verify total taxes distributed equals invoice taxes
        $totalVat = array_sum(array_column($results, 'invoice_level_vat'));
        $totalWithholding = array_sum(array_column($results, 'invoice_level_withholding_tax'));

        $this->assertEquals(60.0, $totalVat);
        $this->assertEquals(20.0, $totalWithholding);
    }

    /**
     * Test purchase return scenario with taxes
     *
     * @test
     */
    public function it_handles_purchase_return_with_taxes(): void
    {
        // Arrange: Purchase return (negative quantity conceptually, but we use positive qty with return type)
        $itemData = ['item_price' => 100, 'quantity' => 2]; // 200 EGP

        $invoiceData = [
            'vat_percentage' => 15, // VAT should reduce cost on return
            'withholding_tax_percentage' => 5, // Withholding tax should increase cost on return
        ];

        $invoiceSubtotal = 200;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: For returns, the detail_value calculation is the same
        // The negative effect is handled by the invoice type logic in SaveInvoiceService
        $this->assertEquals(30.0, $result['invoice_level_vat']); // 15% of 200
        $this->assertEquals(10.0, $result['invoice_level_withholding_tax']); // 5% of 200
        $this->assertEquals(220.0, $result['detail_value']); // 200 + 30 - 10
    }

    /**
     * Test breakdown includes tax information
     *
     * @test
     */
    public function it_includes_tax_information_in_breakdown(): void
    {
        // Arrange
        $itemData = ['item_price' => 100, 'quantity' => 1];

        $invoiceData = [
            'vat_percentage' => 15,
            'withholding_tax_percentage' => 5,
        ];

        $invoiceSubtotal = 100;

        // Act
        $result = $this->calculator->calculate($itemData, $invoiceData, $invoiceSubtotal);

        // Assert: Breakdown should include tax fields
        $this->assertArrayHasKey('breakdown', $result);
        $this->assertArrayHasKey('invoice_level_vat', $result['breakdown']);
        $this->assertArrayHasKey('invoice_level_withholding_tax', $result['breakdown']);
        $this->assertEquals(15.0, $result['breakdown']['invoice_level_vat']);
        $this->assertEquals(5.0, $result['breakdown']['invoice_level_withholding_tax']);
    }
}
