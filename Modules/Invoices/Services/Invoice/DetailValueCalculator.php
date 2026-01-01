<?php

declare(strict_types=1);

namespace Modules\Invoices\Services\Invoice;

use InvalidArgumentException;

/**
 * DetailValueCalculator Service
 *
 * Calculates accurate detail_value for invoice items including:
 * - Item-level discounts and additional charges
 * - Item-level taxes (VAT and Withholding Tax)
 * - Proportionally distributed invoice-level discounts
 * - Proportionally distributed invoice-level additional charges
 * - Proportionally distributed invoice-level taxes
 *
 * The detail_value is used in average cost calculations:
 * average_cost = SUM(detail_value) / SUM(qty_in - qty_out)
 *
 * @package App\Services\Invoice
 */
class DetailValueCalculator
{

    /**
     * Calculate detail_value for an item with distributed invoice discounts/additions.
     *
     * This method calculates the final value of an invoice item after applying:
     * 1. Item-level discount
     * 2. Item-level additional charges
     * 3. Item-level VAT (calculated from net after item-level discount/additional)
     * 4. Item-level Withholding Tax (calculated from net after item-level discount/additional)
     * 5. Proportionally distributed invoice-level discount
     * 6. Proportionally distributed invoice-level additional charges
     * 7. Proportionally distributed invoice-level VAT (calculated from net after invoice-level discount/additional)
     * 8. Proportionally distributed invoice-level Withholding Tax (calculated from net after invoice-level discount/additional)
     *
     * Formula:
     * 1. item_value_before_taxes = (item_price × quantity) - item_discount + item_additional
     * 2. item_level_vat = item_value_before_taxes × (item_vat_percentage / 100)
     * 3. item_level_withholding_tax = item_value_before_taxes × (item_withholding_tax_percentage / 100)
     * 4. item_subtotal = item_value_before_taxes + item_level_vat - item_level_withholding_tax
     * 5. net_after_adjustments = item_subtotal - distributed_discount + distributed_additional
     * 6. invoice_level_vat = net_after_adjustments × (vat_percentage / 100)
     * 7. invoice_level_withholding_tax = net_after_adjustments × (withholding_tax_percentage / 100)
     * 8. detail_value = net_after_adjustments + invoice_level_vat - invoice_level_withholding_tax
     *
     * IMPORTANT: Taxes are calculated from the net value AFTER applying regular adjustments:
     * - Item-level taxes are calculated from item value after item-level discount/additional
     * - Invoice-level taxes are calculated from net value after invoice-level discount/additional
     *
     * EXCLUSIVE MODE SUPPORT:
     * - 'item_level': Discounts/additional AND taxes at item level only. Invoice-level fields must be zero.
     * - 'invoice_level': Discounts/additional AND taxes at invoice level only (distributed). Item-level fields must be zero.
     *
     * @param array $itemData Item data containing:
     *                        - item_price: float - Unit price of the item
     *                        - quantity: float - Quantity (qty_in or qty_out)
     *                        - item_discount: float - Item-level discount (default: 0)
     *                        - additional: float - Item-level additional charges (default: 0)
     *                        - item_vat_percentage: float - Item-level VAT percentage (default: 0)
     *                        - item_vat_value: float - Item-level VAT fixed amount (default: 0)
     *                        - item_withholding_tax_percentage: float - Item-level withholding tax percentage (default: 0)
     *                        - item_withholding_tax_value: float - Item-level withholding tax fixed amount (default: 0)
     * @param array $invoiceData Invoice data containing:
     *                           - fat_disc: float - Invoice discount amount (default: 0)
     *                           - fat_disc_per: float - Invoice discount percentage (default: 0)
     *                           - fat_plus: float - Invoice additional amount (default: 0)
     *                           - fat_plus_per: float - Invoice additional percentage (default: 0)
     *                           - vat_value: float - Invoice-level VAT amount (default: 0)
     *                           - vat_percentage: float - Invoice-level VAT percentage (default: 0)
     *                           - withholding_tax_value: float - Invoice-level withholding tax amount (default: 0)
     *                           - withholding_tax_percentage: float - Invoice-level withholding tax percentage (default: 0)
     *                           - discount_mode: string - 'item_level' or 'invoice_level' (default: 'invoice_level')
     * @param float $invoiceSubtotal Total invoice value before invoice-level discount/additional
     *
     * @return array Calculation results with:
     *               - detail_value: float - Final calculated value
     *               - item_subtotal: float - Item value after item-level taxes, before invoice-level adjustments
     *               - item_level_vat: float - VAT allocated at item level
     *               - item_level_withholding_tax: float - Withholding tax allocated at item level
     *               - distributed_discount: float - Invoice discount allocated to this item
     *               - distributed_additional: float - Invoice additional allocated to this item
     *               - invoice_level_vat: float - VAT allocated at invoice level
     *               - invoice_level_withholding_tax: float - Withholding tax allocated at invoice level
     *               - breakdown: array - Detailed calculation breakdown for audit
     *
     * @throws InvalidArgumentException if data is invalid or missing required fields
     */
    public function calculate(array $itemData, array $invoiceData, float $invoiceSubtotal): array
    {
        // Validate required item fields
        if (!isset($itemData['item_price']) || !isset($itemData['quantity'])) {
            throw new InvalidArgumentException('Item data must contain item_price and quantity');
        }

        // Extract and validate item data
        $itemPrice = (float) $itemData['item_price'];
        $quantity = (float) $itemData['quantity'];
        $itemDiscount = (float) ($itemData['item_discount'] ?? 0);
        $itemAdditional = (float) ($itemData['additional'] ?? 0);

        // Validate numeric values
        if ($itemPrice < 0) {
            throw new InvalidArgumentException('Item price cannot be negative');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }

        if ($itemDiscount < 0) {
            throw new InvalidArgumentException('Item discount cannot be negative');
        }

        if ($itemAdditional < 0) {
            throw new InvalidArgumentException('Item additional cannot be negative');
        }

        // Get discount mode (default: 'invoice_level' for backward compatibility)
        $discountMode = $invoiceData['discount_mode'] ?? 'invoice_level';

        // Calculate item subtotal (before invoice-level adjustments)
        $itemValueBeforeTaxes = ($itemPrice * $quantity) - $itemDiscount + $itemAdditional;

        // Calculate item-level taxes based on mode
        $itemLevelVat = 0;
        $itemLevelWithholdingTax = 0;

        if ($discountMode === 'item_level') {
            // In item_level mode: Calculate item-level taxes
            $itemLevelVat = $this->calculateItemLevelVat($itemValueBeforeTaxes, $itemData);
            $itemLevelWithholdingTax = $this->calculateItemLevelWithholdingTax($itemValueBeforeTaxes, $itemData);
        }
        // In invoice_level mode: Item-level taxes remain 0

        // Calculate item_subtotal (includes item-level taxes if in item_level mode)
        $itemSubtotal = $itemValueBeforeTaxes + $itemLevelVat - $itemLevelWithholdingTax;

        // Validate invoice subtotal
        if ($invoiceSubtotal <= 0) {
            throw new InvalidArgumentException('Invoice subtotal must be greater than zero');
        }

        // Calculate distributed invoice discount/additional based on mode
        $distributedDiscount = 0;
        $distributedAdditional = 0;

        if ($discountMode === 'invoice_level') {
            // Invoice-level mode: Distribute invoice discount/additional proportionally
            $distributedDiscount = $this->distributeInvoiceDiscount(
                $itemSubtotal,
                $invoiceSubtotal,
                $invoiceData
            );

            $distributedAdditional = $this->distributeInvoiceAdditional(
                $itemSubtotal,
                $invoiceSubtotal,
                $invoiceData
            );
        }
        // In 'item_level' mode: distributedDiscount and distributedAdditional remain 0

        // Calculate net value after discount and additional (before invoice-level taxes)
        // This is the base for invoice-level tax calculations
        $netAfterAdjustments = $itemSubtotal - $distributedDiscount + $distributedAdditional;

        // Calculate invoice-level taxes based on mode
        $invoiceLevelVat = 0;
        $invoiceLevelWithholdingTax = 0;

        if ($discountMode === 'invoice_level') {
            // In invoice_level mode: Calculate invoice-level taxes
            $invoiceLevelVat = $this->distributeVat(
                $netAfterAdjustments,
                $invoiceSubtotal,
                $invoiceData
            );

            $invoiceLevelWithholdingTax = $this->distributeWithholdingTax(
                $netAfterAdjustments,
                $invoiceSubtotal,
                $invoiceData
            );
        }
        // In item_level mode: Invoice-level taxes remain 0

        // Calculate final detail_value
        // detail_value = net_after_adjustments + invoice_level_vat - invoice_level_withholding_tax
        $detailValue = $netAfterAdjustments + $invoiceLevelVat - $invoiceLevelWithholdingTax;

        // Ensure detail_value is not negative
        $detailValue = max(0, $detailValue);

        // Return calculation results with breakdown
        return [
            'detail_value' => round($detailValue, 2),
            'item_subtotal' => round($itemSubtotal, 2),
            'item_level_vat' => round($itemLevelVat, 2),
            'item_level_withholding_tax' => round($itemLevelWithholdingTax, 2),
            'distributed_discount' => round($distributedDiscount, 2),
            'distributed_additional' => round($distributedAdditional, 2),
            'invoice_level_vat' => round($invoiceLevelVat, 2),
            'invoice_level_withholding_tax' => round($invoiceLevelWithholdingTax, 2),
            'breakdown' => [
                'item_price' => $itemPrice,
                'quantity' => $quantity,
                'item_discount' => $itemDiscount,
                'item_additional' => $itemAdditional,
                'item_value_before_taxes' => round($itemValueBeforeTaxes, 2),
                'item_level_vat' => round($itemLevelVat, 2),
                'item_level_withholding_tax' => round($itemLevelWithholdingTax, 2),
                'item_subtotal' => round($itemSubtotal, 2),
                'distributed_discount' => round($distributedDiscount, 2),
                'distributed_additional' => round($distributedAdditional, 2),
                'net_after_adjustments' => round($netAfterAdjustments, 2),
                'invoice_level_vat' => round($invoiceLevelVat, 2),
                'invoice_level_withholding_tax' => round($invoiceLevelWithholdingTax, 2),
                'detail_value' => round($detailValue, 2),
            ],
        ];
    }

    /**
     * Calculate invoice subtotal from items (before invoice-level discount/additional).
     *
     * The invoice subtotal is the sum of all item subtotals, where each item subtotal is:
     * - In item_level mode: item_subtotal = (item_price × quantity) - item_discount + item_additional + item_level_vat - item_level_withholding_tax
     * - In invoice_level mode: item_subtotal = (item_price × quantity) (no item-level adjustments or taxes)
     *
     * This value is used as the basis for proportional distribution of invoice-level
     * discounts and additional charges.
     *
     * @param array $items Array of items, each containing:
     *                     - item_price: float - Unit price
     *                     - quantity: float - Quantity
     *                     - item_discount: float - Item-level discount (optional)
     *                     - additional: float - Item-level additional (optional)
     *                     - item_vat_percentage: float - Item-level VAT percentage (optional)
     *                     - item_vat_value: float - Item-level VAT fixed amount (optional)
     *                     - item_withholding_tax_percentage: float - Item-level withholding tax percentage (optional)
     *                     - item_withholding_tax_value: float - Item-level withholding tax fixed amount (optional)
     * @param string $discountMode Discount mode: 'item_level' or 'invoice_level' (default: 'invoice_level')
     *
     * @return float Invoice subtotal
     *
     * @throws InvalidArgumentException if items array is empty or contains invalid data
     */
    public function calculateInvoiceSubtotal(array $items, string $discountMode = 'invoice_level'): float
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Items array cannot be empty');
        }

        $subtotal = 0.0;

        foreach ($items as $index => $item) {
            // Validate required fields
            if (!isset($item['item_price']) || !isset($item['quantity'])) {
                throw new InvalidArgumentException(
                    "Item at index {$index} must contain item_price and quantity"
                );
            }

            $itemPrice = (float) $item['item_price'];
            $quantity = (float) $item['quantity'];
            $itemDiscount = (float) ($item['item_discount'] ?? 0);
            $itemAdditional = (float) ($item['additional'] ?? 0);

            // Validate values
            if ($itemPrice < 0) {
                throw new InvalidArgumentException(
                    "Item price at index {$index} cannot be negative"
                );
            }

            if ($quantity <= 0) {
                throw new InvalidArgumentException(
                    "Quantity at index {$index} must be greater than zero"
                );
            }

            // Calculate item value before taxes
            $itemValueBeforeTaxes = ($itemPrice * $quantity) - $itemDiscount + $itemAdditional;

            // Calculate item-level taxes based on mode
            $itemLevelVat = 0;
            $itemLevelWithholdingTax = 0;

            if ($discountMode === 'item_level') {
                // In item_level mode: Calculate item-level taxes
                $itemLevelVat = $this->calculateItemLevelVat($itemValueBeforeTaxes, $item);
                $itemLevelWithholdingTax = $this->calculateItemLevelWithholdingTax($itemValueBeforeTaxes, $item);
            }
            // In invoice_level mode: Item-level taxes remain 0

            // Calculate item subtotal (includes item-level taxes if in item_level mode)
            $itemSubtotal = $itemValueBeforeTaxes + $itemLevelVat - $itemLevelWithholdingTax;
            $subtotal += $itemSubtotal;
        }

        return round($subtotal, 2);
    }

    /**
     * Distribute invoice-level discount across items proportionally.
     *
     * Supports two distribution methods:
     * 1. Fixed Amount: Distributes fat_disc proportionally based on item value
     *    Formula: distributed_discount = (item_subtotal / invoice_subtotal) × fat_disc
     *
     * 2. Percentage: Applies fat_disc_per percentage to item value
     *    Formula: distributed_discount = item_subtotal × (fat_disc_per / 100)
     *
     * If both fat_disc and fat_disc_per are provided, fat_disc takes precedence.
     *
     * @param float $itemSubtotal Item's subtotal value (before invoice-level adjustments)
     * @param float $invoiceSubtotal Total invoice subtotal (sum of all items)
     * @param array $invoiceData Invoice discount data containing:
     *                           - fat_disc: float - Fixed discount amount (optional)
     *                           - fat_disc_per: float - Discount percentage (optional)
     *
     * @return float Distributed discount amount for this item
     */
    private function distributeInvoiceDiscount(
        float $itemSubtotal,
        float $invoiceSubtotal,
        array $invoiceData
    ): float {
        $fatDisc = (float) ($invoiceData['fat_disc'] ?? 0);
        $fatDiscPer = (float) ($invoiceData['fat_disc_per'] ?? 0);

        // No discount to distribute
        if ($fatDisc <= 0 && $fatDiscPer <= 0) {
            return 0.0;
        }

        // Fixed amount distribution (takes precedence)
        if ($fatDisc > 0) {
            $itemRatio = $itemSubtotal / $invoiceSubtotal;
            return $fatDisc * $itemRatio;
        }

        // Percentage distribution
        if ($fatDiscPer > 0) {
            return $itemSubtotal * ($fatDiscPer / 100);
        }

        return 0.0;
    }

    /**
     * Distribute invoice-level additional charges across items proportionally.
     *
     * Supports two distribution methods:
     * 1. Fixed Amount: Distributes fat_plus proportionally based on item value
     *    Formula: distributed_additional = (item_subtotal / invoice_subtotal) × fat_plus
     *
     * 2. Percentage: Applies fat_plus_per percentage to item value
     *    Formula: distributed_additional = item_subtotal × (fat_plus_per / 100)
     *
     * If both fat_plus and fat_plus_per are provided, fat_plus takes precedence.
     *
     * @param float $itemSubtotal Item's subtotal value (before invoice-level adjustments)
     * @param float $invoiceSubtotal Total invoice subtotal (sum of all items)
     * @param array $invoiceData Invoice additional data containing:
     *                           - fat_plus: float - Fixed additional amount (optional)
     *                           - fat_plus_per: float - Additional percentage (optional)
     *
     * @return float Distributed additional amount for this item
     */
    private function distributeInvoiceAdditional(
        float $itemSubtotal,
        float $invoiceSubtotal,
        array $invoiceData
    ): float {
        $fatPlus = (float) ($invoiceData['fat_plus'] ?? 0);
        $fatPlusPer = (float) ($invoiceData['fat_plus_per'] ?? 0);

        // No additional to distribute
        if ($fatPlus <= 0 && $fatPlusPer <= 0) {
            return 0.0;
        }

        // Fixed amount distribution (takes precedence)
        if ($fatPlus > 0) {
            $itemRatio = $itemSubtotal / $invoiceSubtotal;
            return $fatPlus * $itemRatio;
        }

        // Percentage distribution
        if ($fatPlusPer > 0) {
            return $itemSubtotal * ($fatPlusPer / 100);
        }

        return 0.0;
    }

    /**
     * Distribute VAT (Value Added Tax) across items.
     *
     * VAT is calculated from the net value AFTER applying discounts and additional charges.
     * This ensures that VAT is applied to the actual transaction value.
     *
     * Supports two distribution methods:
     * 1. Fixed Amount: Distributes vat_value proportionally based on item value
     *    Formula: distributed_vat = (net_after_adjustments / invoice_subtotal) × vat_value
     *
     * 2. Percentage: Applies vat_percentage to net value after adjustments
     *    Formula: distributed_vat = net_after_adjustments × (vat_percentage / 100)
     *
     * If both vat_value and vat_percentage are provided, vat_value takes precedence.
     *
     * @param float $netAfterAdjustments Net value after discount and additional
     * @param float $invoiceSubtotal Total invoice subtotal (sum of all items before adjustments)
     * @param array $invoiceData Invoice VAT data containing:
     *                           - vat_value: float - Fixed VAT amount (optional)
     *                           - vat_percentage: float - VAT percentage (optional)
     *
     * @return float Distributed VAT amount for this item
     */
    private function distributeVat(
        float $netAfterAdjustments,
        float $invoiceSubtotal,
        array $invoiceData
    ): float {
        $vatValue = (float) ($invoiceData['vat_value'] ?? 0);
        $vatPercentage = (float) ($invoiceData['vat_percentage'] ?? 0);

        // No VAT to distribute
        if ($vatValue <= 0 && $vatPercentage <= 0) {
            return 0.0;
        }

        // Fixed amount distribution (takes precedence)
        // Distribute proportionally based on original item subtotal ratio
        if ($vatValue > 0) {
            $itemRatio = $netAfterAdjustments / $invoiceSubtotal;
            return $vatValue * $itemRatio;
        }

        // Percentage distribution - apply to net value after adjustments
        if ($vatPercentage > 0) {
            return $netAfterAdjustments * ($vatPercentage / 100);
        }

        return 0.0;
    }

    /**
     * Distribute Withholding Tax across items.
     *
     * Withholding Tax is calculated from the net value AFTER applying discounts and additional charges.
     * This ensures that withholding tax is applied to the actual transaction value.
     *
     * Supports two distribution methods:
     * 1. Fixed Amount: Distributes withholding_tax_value proportionally based on item value
     *    Formula: distributed_tax = (net_after_adjustments / invoice_subtotal) × withholding_tax_value
     *
     * 2. Percentage: Applies withholding_tax_percentage to net value after adjustments
     *    Formula: distributed_tax = net_after_adjustments × (withholding_tax_percentage / 100)
     *
     * If both withholding_tax_value and withholding_tax_percentage are provided,
     * withholding_tax_value takes precedence.
     *
     * @param float $netAfterAdjustments Net value after discount and additional
     * @param float $invoiceSubtotal Total invoice subtotal (sum of all items before adjustments)
     * @param array $invoiceData Invoice withholding tax data containing:
     *                           - withholding_tax_value: float - Fixed tax amount (optional)
     *                           - withholding_tax_percentage: float - Tax percentage (optional)
     *
     * @return float Distributed withholding tax amount for this item
     */
    private function distributeWithholdingTax(
        float $netAfterAdjustments,
        float $invoiceSubtotal,
        array $invoiceData
    ): float {
        $withholdingTaxValue = (float) ($invoiceData['withholding_tax_value'] ?? 0);
        $withholdingTaxPercentage = (float) ($invoiceData['withholding_tax_percentage'] ?? 0);

        // No withholding tax to distribute
        if ($withholdingTaxValue <= 0 && $withholdingTaxPercentage <= 0) {
            return 0.0;
        }

        // Fixed amount distribution (takes precedence)
        // Distribute proportionally based on original item subtotal ratio
        if ($withholdingTaxValue > 0) {
            $itemRatio = $netAfterAdjustments / $invoiceSubtotal;
            return $withholdingTaxValue * $itemRatio;
        }

        // Percentage distribution - apply to net value after adjustments
        if ($withholdingTaxPercentage > 0) {
            return $netAfterAdjustments * ($withholdingTaxPercentage / 100);
        }

        return 0.0;
    }

    /**
     * Calculate item-level VAT (Value Added Tax).
     *
     * VAT is calculated from the item value AFTER applying item-level discount and additional charges.
     * This ensures that VAT is applied to the actual item transaction value.
     *
     * Supports two methods:
     * 1. Fixed Amount: Uses item_vat_value directly
     * 2. Percentage: Applies item_vat_percentage to item value
     *
     * If both item_vat_value and item_vat_percentage are provided, item_vat_value takes precedence.
     *
     * @param float $itemValueBeforeTaxes Item value after discount/additional, before taxes
     * @param array $itemData Item VAT data containing:
     *                        - item_vat_value: float - Fixed VAT amount (optional)
     *                        - item_vat_percentage: float - VAT percentage (optional)
     *
     * @return float Item-level VAT amount
     */
    private function calculateItemLevelVat(float $itemValueBeforeTaxes, array $itemData): float
    {
        $itemVatValue = (float) ($itemData['item_vat_value'] ?? 0);
        $itemVatPercentage = (float) ($itemData['item_vat_percentage'] ?? 0);

        // No item-level VAT
        if ($itemVatValue <= 0 && $itemVatPercentage <= 0) {
            return 0.0;
        }

        // Fixed amount (takes precedence)
        if ($itemVatValue > 0) {
            return $itemVatValue;
        }

        // Percentage - apply to item value after discount/additional
        if ($itemVatPercentage > 0) {
            return $itemValueBeforeTaxes * ($itemVatPercentage / 100);
        }

        return 0.0;
    }

    /**
     * Calculate item-level Withholding Tax.
     *
     * Withholding Tax is calculated from the item value AFTER applying item-level discount and additional charges.
     * This ensures that withholding tax is applied to the actual item transaction value.
     *
     * Supports two methods:
     * 1. Fixed Amount: Uses item_withholding_tax_value directly
     * 2. Percentage: Applies item_withholding_tax_percentage to item value
     *
     * If both item_withholding_tax_value and item_withholding_tax_percentage are provided,
     * item_withholding_tax_value takes precedence.
     *
     * @param float $itemValueBeforeTaxes Item value after discount/additional, before taxes
     * @param array $itemData Item withholding tax data containing:
     *                        - item_withholding_tax_value: float - Fixed tax amount (optional)
     *                        - item_withholding_tax_percentage: float - Tax percentage (optional)
     *
     * @return float Item-level withholding tax amount
     */
    private function calculateItemLevelWithholdingTax(float $itemValueBeforeTaxes, array $itemData): float
    {
        $itemWithholdingTaxValue = (float) ($itemData['item_withholding_tax_value'] ?? 0);
        $itemWithholdingTaxPercentage = (float) ($itemData['item_withholding_tax_percentage'] ?? 0);

        // No item-level withholding tax
        if ($itemWithholdingTaxValue <= 0 && $itemWithholdingTaxPercentage <= 0) {
            return 0.0;
        }

        // Fixed amount (takes precedence)
        if ($itemWithholdingTaxValue > 0) {
            return $itemWithholdingTaxValue;
        }

        // Percentage - apply to item value after discount/additional
        if ($itemWithholdingTaxPercentage > 0) {
            return $itemValueBeforeTaxes * ($itemWithholdingTaxPercentage / 100);
        }

        return 0.0;
    }
}
