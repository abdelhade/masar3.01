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
     * @param array $itemData Item data
     * @param array $invoiceData Invoice data containing specific levels
     * @param float $invoiceSubtotal Total invoice value for distribution
     *
     * @return array Calculation results with breakdown
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
        
        // Extract level settings (default to invoice_level for backward compatibility)
        $discountLevel = $invoiceData['discount_level'] ?? 'invoice_level';
        $additionalLevel = $invoiceData['additional_level'] ?? 'invoice_level';
        $vatLevel = $invoiceData['vat_level'] ?? 'invoice_level';
        $withholdingTaxLevel = $invoiceData['withholding_tax_level'] ?? 'invoice_level';

        // 1. Calculate item-level discount and additional
        $itemDiscount = 0;
        $itemAdditional = 0;
        
        if ($discountLevel === 'item_level' || $discountLevel === 'both') {
            $itemDiscount = (float) ($itemData['item_discount'] ?? 0);
        }
        
        if ($additionalLevel === 'item_level' || $additionalLevel === 'both') {
            $itemAdditional = (float) ($itemData['additional'] ?? 0);
        }

        // 2. Base value before taxes
        $itemValueBeforeTaxes = ($itemPrice * $quantity) - $itemDiscount + $itemAdditional;

        // 3. Calculate item-level taxes
        $itemLevelVat = 0;
        $itemLevelWithholdingTax = 0;

        if ($vatLevel === 'item_level' || $vatLevel === 'both') {
            $itemLevelVat = $this->calculateItemLevelVat($itemValueBeforeTaxes, $itemData);
        }
        
        if ($withholdingTaxLevel === 'item_level' || $withholdingTaxLevel === 'both') {
            $itemLevelWithholdingTax = $this->calculateItemLevelWithholdingTax($itemValueBeforeTaxes, $itemData);
        }

        // 4. Item subtotal (base for invoice distribution)
        $itemSubtotal = $itemValueBeforeTaxes + $itemLevelVat - $itemLevelWithholdingTax;

        // 5. Calculate distributed invoice discount/additional
        $distributedDiscount = 0;
        $distributedAdditional = 0;

        if ($invoiceSubtotal > 0) {
            if ($discountLevel === 'invoice_level' || $discountLevel === 'both') {
                $distributedDiscount = $this->distributeInvoiceDiscount($itemSubtotal, $invoiceSubtotal, $invoiceData);
            }

            if ($additionalLevel === 'invoice_level' || $additionalLevel === 'both') {
                $distributedAdditional = $this->distributeInvoiceAdditional($itemSubtotal, $invoiceSubtotal, $invoiceData);
            }
        }

        // 6. Net after distribution adjustments
        $netAfterAdjustments = $itemSubtotal - $distributedDiscount + $distributedAdditional;

        // 7. Calculate invoice-level taxes
        $invoiceLevelVat = 0;
        $invoiceLevelWithholdingTax = 0;

        if ($invoiceSubtotal > 0) {
            if ($vatLevel === 'invoice_level' || $vatLevel === 'both') {
                $invoiceLevelVat = $this->distributeVat($netAfterAdjustments, $invoiceSubtotal, $invoiceData);
            }

            if ($withholdingTaxLevel === 'invoice_level' || $withholdingTaxLevel === 'both') {
                $invoiceLevelWithholdingTax = $this->distributeWithholdingTax($netAfterAdjustments, $invoiceSubtotal, $invoiceData);
            }
        }

        // 8. Final detail_value
        $detailValue = $netAfterAdjustments + $invoiceLevelVat - $invoiceLevelWithholdingTax;
        $detailValue = max(0, $detailValue);

        return [
            'detail_value' => round($detailValue, 2),
            'item_subtotal' => round($itemSubtotal, 2),
            'item_level_vat' => round($itemLevelVat, 2),
            'item_level_withholding_tax' => round($itemLevelWithholdingTax, 2),
            'distributed_discount' => round($distributedDiscount, 2),
            'distributed_additional' => round($distributedAdditional, 2),
            'invoice_level_vat' => round($invoiceLevelVat, 2),
            'invoice_level_withholding_tax' => round($invoiceLevelWithholdingTax, 2),
            'calculation_breakdown' => [ // Changed to match SaveInvoiceService expectation
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
     * Calculate invoice subtotal representing the distribution base.
     */
    public function calculateInvoiceSubtotal(array $items, array $levels = []): float
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Items array cannot be empty');
        }

        $discountLevel = $levels['discount_level'] ?? 'invoice_level';
        $additionalLevel = $levels['additional_level'] ?? 'invoice_level';
        $vatLevel = $levels['vat_level'] ?? 'invoice_level';
        $withholdingTaxLevel = $levels['withholding_tax_level'] ?? 'invoice_level';

        $totalSubtotal = 0.0;

        foreach ($items as $item) {
            $itemPrice = (float) $item['item_price'];
            $quantity = (float) $item['quantity'];
            
            $itemDiscount = 0;
            $itemAdditional = 0;
            
            if ($discountLevel === 'item_level' || $discountLevel === 'both') {
                $itemDiscount = (float) ($item['item_discount'] ?? 0);
            }
            if ($additionalLevel === 'item_level' || $additionalLevel === 'both') {
                $itemAdditional = (float) ($item['additional'] ?? 0);
            }

            $itemValueBeforeTaxes = ($itemPrice * $quantity) - $itemDiscount + $itemAdditional;

            $itemLevelVat = 0;
            $itemLevelWithholdingTax = 0;

            if ($vatLevel === 'item_level' || $vatLevel === 'both') {
                $itemLevelVat = $this->calculateItemLevelVat($itemValueBeforeTaxes, $item);
            }
            if ($withholdingTaxLevel === 'item_level' || $withholdingTaxLevel === 'both') {
                $itemLevelWithholdingTax = $this->calculateItemLevelWithholdingTax($itemValueBeforeTaxes, $item);
            }

            $totalSubtotal += ($itemValueBeforeTaxes + $itemLevelVat - $itemLevelWithholdingTax);
        }

        return round($totalSubtotal, 2);
    }

    private function distributeInvoiceDiscount(float $itemSubtotal, float $invoiceSubtotal, array $invoiceData): float 
    {
        $fatDisc = (float) ($invoiceData['fat_disc'] ?? 0);
        $fatDiscPer = (float) ($invoiceData['fat_disc_per'] ?? 0);
        if ($fatDisc <= 0 && $fatDiscPer <= 0) return 0.0;
        if ($fatDisc > 0) return $fatDisc * ($itemSubtotal / $invoiceSubtotal);
        return $itemSubtotal * ($fatDiscPer / 100);
    }

    private function distributeInvoiceAdditional(float $itemSubtotal, float $invoiceSubtotal, array $invoiceData): float 
    {
        $fatPlus = (float) ($invoiceData['fat_plus'] ?? 0);
        $fatPlusPer = (float) ($invoiceData['fat_plus_per'] ?? 0);
        if ($fatPlus <= 0 && $fatPlusPer <= 0) return 0.0;
        if ($fatPlus > 0) return $fatPlus * ($itemSubtotal / $invoiceSubtotal);
        return $itemSubtotal * ($fatPlusPer / 100);
    }

    private function distributeVat(float $netAfterAdjustments, float $invoiceSubtotal, array $invoiceData): float 
    {
        $vatValue = (float) ($invoiceData['vat_value'] ?? 0);
        $vatPercentage = (float) ($invoiceData['vat_percentage'] ?? 0);
        if ($vatValue <= 0 && $vatPercentage <= 0) return 0.0;
        if ($vatValue > 0) return $vatValue * ($netAfterAdjustments / $invoiceSubtotal);
        return $netAfterAdjustments * ($vatPercentage / 100);
    }

    private function distributeWithholdingTax(float $netAfterAdjustments, float $invoiceSubtotal, array $invoiceData): float 
    {
        $taxValue = (float) ($invoiceData['withholding_tax_value'] ?? 0);
        $taxPercentage = (float) ($invoiceData['withholding_tax_percentage'] ?? 0);
        if ($taxValue <= 0 && $taxPercentage <= 0) return 0.0;
        if ($taxValue > 0) return $taxValue * ($netAfterAdjustments / $invoiceSubtotal);
        return $netAfterAdjustments * ($taxPercentage / 100);
    }

    private function calculateItemLevelVat(float $itemValueBeforeTaxes, array $itemData): float
    {
        $val = (float) ($itemData['item_vat_value'] ?? 0);
        $per = (float) ($itemData['item_vat_percentage'] ?? 0);
        if ($val > 0) return $val;
        return $itemValueBeforeTaxes * ($per / 100);
    }

    private function calculateItemLevelWithholdingTax(float $itemValueBeforeTaxes, array $itemData): float
    {
        $val = (float) ($itemData['item_withholding_tax_value'] ?? 0);
        $per = (float) ($itemData['item_withholding_tax_percentage'] ?? 0);
        if ($val > 0) return $val;
        return $itemValueBeforeTaxes * ($per / 100);
    }
}
