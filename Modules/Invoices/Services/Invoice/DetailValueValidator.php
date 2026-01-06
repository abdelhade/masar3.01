<?php

declare(strict_types=1);

namespace Modules\Invoices\Services\Invoice;

use InvalidArgumentException;
use Modules\Invoices\Services\Invoice\DetailValueCalculator;

/**
 * DetailValueValidator Service
 *
 * Validates calculated detail_value for correctness and reasonableness.
 * Ensures data integrity before saving invoice items to the database.
 *
 * Validation Rules:
 * 1. Detail value must not be negative
 * 2. Detail value must be within reasonable bounds (0 to 10x item price × quantity)
 * 3. Calculation must be accurate (matches formula within tolerance)
 * 4. Exclusive mode rules: Either item-level OR invoice-level discounts/additional (not both)
 */
class DetailValueValidator
{
    /**
     * Rounding tolerance for floating point comparisons (0.01)
     */
    private const TOLERANCE = 0.01;

    /**
     * Maximum reasonable multiplier for detail_value validation
     * Detail value should not exceed (item_price × quantity × MAX_MULTIPLIER)
     */
    private const MAX_MULTIPLIER = 10.0;

    /**
     * Validate levels for discounts, additional charges, and taxes.
     *
     * Ensures that fields align with their configured level (item_level, invoice_level, both, disabled).
     *
     * @param array $itemData Item data containing discount/additional/tax fields
     * @param array $invoiceData Invoice data containing discount/additional/tax fields and level settings
     *
     * @throws InvalidArgumentException if level rules are violated
     */
    public function validateLevels(array $itemData, array $invoiceData): void
    {
        $discountLevel = $invoiceData['discount_level'] ?? 'invoice_level';
        $additionalLevel = $invoiceData['additional_level'] ?? 'invoice_level';
        $vatLevel = $invoiceData['vat_level'] ?? 'invoice_level';
        $withholdingTaxLevel = $invoiceData['withholding_tax_level'] ?? 'invoice_level';

        // 1. Validate Discounts
        $this->validateComponentLevel(
            'الخصم',
            $discountLevel,
            (float) ($itemData['item_discount'] ?? 0),
            (float) ($invoiceData['fat_disc'] ?? 0) + (float) ($invoiceData['fat_disc_per'] ?? 0)
        );

        // 2. Validate Additional Charges
        $this->validateComponentLevel(
            'الإضافي',
            $additionalLevel,
            (float) ($itemData['additional'] ?? 0),
            (float) ($invoiceData['fat_plus'] ?? 0) + (float) ($invoiceData['fat_plus_per'] ?? 0)
        );

        // 3. Validate VAT
        $this->validateComponentLevel(
            'ضريبة القيمة المضافة',
            $vatLevel,
            (float) ($itemData['item_vat_percentage'] ?? 0) + (float) ($itemData['item_vat_value'] ?? 0),
            (float) ($invoiceData['vat_percentage'] ?? 0) + (float) ($invoiceData['vat_value'] ?? 0)
        );

        // 4. Validate Withholding Tax
        $this->validateComponentLevel(
            'الخصم الضريبي',
            $withholdingTaxLevel,
            (float) ($itemData['item_withholding_tax_percentage'] ?? 0) + (float) ($itemData['item_withholding_tax_value'] ?? 0),
            (float) ($invoiceData['withholding_tax_percentage'] ?? 0) + (float) ($invoiceData['withholding_tax_value'] ?? 0)
        );
    }

    /**
     * Helper to validate a specific component (discount, additional, etc) against its level setting.
     */
    private function validateComponentLevel(string $label, string $level, float $itemValue, float $invoiceValue): void
    {
        switch ($level) {
            case 'disabled':
                if ($itemValue > 0 || $invoiceValue > 0) {
                    throw new InvalidArgumentException("إعداد {$label} معطل، ولكن تم إرسال قيم. (صنف: {$itemValue}، فاتورة: {$invoiceValue})");
                }
                break;
            case 'item_level':
                if ($invoiceValue > 0) {
                    throw new InvalidArgumentException("إعداد {$label} على مستوى الصنف فقط، ولكن تم إرسال قيمة على مستوى الفاتورة ({$invoiceValue}).");
                }
                break;
            case 'invoice_level':
                if ($itemValue > 0) {
                    throw new InvalidArgumentException("إعداد {$label} على مستوى الفاتورة فقط، ولكن تم إرسال قيمة على مستوى الصنف ({$itemValue}).");
                }
                break;
            case 'both':
                // Both are allowed
                break;
            default:
                throw new InvalidArgumentException("مستوى غير صحيح للإعداد {$label}: {$level}");
        }
    }


    /**
     * Validate calculated detail_value.
     *
     * Performs three validation checks:
     * 1. Non-negativity: detail_value must be >= 0
     * 2. Reasonableness: detail_value must be within reasonable bounds
     * 3. Calculation accuracy: detail_value must match the formula
     /**
     * Validate calculated detail_value.
     *
     * Performs a two-fold validation:
     * 1. Base Integrity: Ensures frontend sub_value matches (Price * Qty - ItemDiscount + ItemAdditional).
     * 2. Distribution Accuracy: Ensures calculated detail_value matches internal formula within tolerance.
     *
     * @param  float  $calculatedDetailValue  The value calculated by Backend
     * @param  float  $frontendSubValue  The sub_value provided by the UI
     * @param  array  $itemData  Original item data
     * @param  array  $calculation  Calculation breakdown
     *
     * @throws InvalidArgumentException if validation fails
     */
    public function validate(float $calculatedDetailValue, float $frontendSubValue, array $itemData, array $calculation): void
    {
        // 1. Validation: Non-negativity
        if ($calculatedDetailValue < 0) {
            throw new InvalidArgumentException(sprintf('قيمة الصنف لا يمكن أن تكون سالبة. (%.2f)', $calculatedDetailValue));
        }

        // 2. Validation: Base Integrity (Price * Qty vs Frontend SubValue)
        // We verify that the row subtotal BEFORE global distribution matches the inputs.
        $itemPrice = (float) ($itemData['item_price'] ?? 0);
        $quantity = (float) ($itemData['quantity'] ?? 0);
        $itemDiscount = (float) ($itemData['item_discount'] ?? 0);
        $itemAdditional = (float) ($itemData['additional'] ?? 0);
        $itemVatValue = (float) ($itemData['item_vat_value'] ?? 0);
        $itemWithholdingTaxValue = (float) ($itemData['item_withholding_tax_value'] ?? 0);

        // Formula: (Price * Qty) - Discount + Additional + VAT - WithholdingTax
        $expectedBase = ($itemPrice * $quantity) - $itemDiscount + $itemAdditional + $itemVatValue - $itemWithholdingTaxValue;

        if (abs($expectedBase - $frontendSubValue) > self::TOLERANCE) {
            throw new InvalidArgumentException(
                sprintf(
                    'تعارض في بيانات الصنف الأساسية. المتوقع لأساس الصنف: %.2f، المستلم من الواجهة: %.2f. (سعر: %.2f، كمية: %.2f، خصم: %.2f، إضافي: %.2f، ضريبة: %.2f، خصم منبع: %.2f)',
                    $expectedBase,
                    $frontendSubValue,
                    $itemPrice,
                    $quantity,
                    $itemDiscount,
                    $itemAdditional,
                    $itemVatValue,
                    $itemWithholdingTaxValue
                )
            );
        }

        // 3. Validation: Calculation Accuracy (Backend Self-Check)
        if (!$this->verifyCalculation($calculatedDetailValue, $calculation)) {
            $expected = $this->calculateExpectedValue($calculation);
            throw new InvalidArgumentException(
                sprintf(
                    'خطأ في حساب توزيع قيم الفاتورة. المتوقع: %.2f، المحسوب: %.2f. (فرق: %.4f)',
                    $expected,
                    $calculatedDetailValue,
                    abs($expected - $calculatedDetailValue)
                )
            );
        }
    }


    /**
     * Check if detail_value is within reasonable bounds.
     *
     * A detail_value is considered reasonable if:
     * - It is non-negative (>= 0)
     * - It does not exceed (item_price × quantity × MAX_MULTIPLIER)
     *
     * The MAX_MULTIPLIER allows for additional charges but prevents
     * obviously incorrect values (e.g., due to calculation errors).
     *
     * @param  float  $detailValue  Calculated detail value
     * @param  float  $itemPrice  Item unit price
     * @param  float  $quantity  Item quantity
     * @return bool True if detail_value is reasonable, false otherwise
     */
    private function isReasonable(float $detailValue, float $itemPrice, float $quantity): bool
    {
        // Must be non-negative
        if ($detailValue < 0) {
            return false;
        }

        // Must not exceed reasonable maximum
        // Allow up to 10x the base value to account for additional charges
        $maxReasonable = $itemPrice * $quantity * self::MAX_MULTIPLIER;

        return $detailValue <= $maxReasonable;
    }

    /**
     * Verify calculation accuracy.
     *
     * Verifies that the detail_value matches the expected formula:
     * detail_value = item_subtotal - distributed_discount + distributed_additional
     *
     * Uses TOLERANCE for floating point comparison to handle rounding differences.
     *
     * @param  float  $detailValue  Calculated detail value to verify
     * @param  array  $calculation  Calculation breakdown containing:
     *                              - item_subtotal: float
     *                              - distributed_discount: float
     *                              - distributed_additional: float
     * @return bool True if calculation is accurate within tolerance, false otherwise
     */
    private function verifyCalculation(float $detailValue, array $calculation): bool
    {
        $expected = $this->calculateExpectedValue($calculation);

        // Compare with tolerance for floating point precision
        $difference = abs($expected - $detailValue);

        return $difference <= self::TOLERANCE;
    }

    /**
     * Calculate expected detail_value from calculation breakdown.
     *
     * Formula:
     * expected = item_subtotal - distributed_discount + distributed_additional
     * expected = max(0, expected)  // Cannot be negative
     *
     * @param  array  $calculation  Calculation breakdown
     * @return float Expected detail value
     */
    private function calculateExpectedValue(array $calculation): float
    {
        $itemSubtotal = (float) ($calculation['item_subtotal'] ?? 0);
        $distributedDiscount = (float) ($calculation['distributed_discount'] ?? 0);
        $distributedAdditional = (float) ($calculation['distributed_additional'] ?? 0);
        $invoiceLevelVat = (float) ($calculation['invoice_level_vat'] ?? 0);
        $invoiceLevelWithholdingTax = (float) ($calculation['invoice_level_withholding_tax'] ?? 0);

        $expected = $itemSubtotal - $distributedDiscount + $distributedAdditional + $invoiceLevelVat - $invoiceLevelWithholdingTax;

        // Ensure non-negative (same as calculator)
        return max(0, $expected);
    }

    /**
     * Validate the sum of all detail values against the total invoice amount.
     *
     * @param array $calculatedItems Items with calculated_detail_value
     * @param array $invoiceData Invoice-level data
     * @throws InvalidArgumentException if totals mismatch
     */
    public function validateInvoiceTotals(array $calculatedItems, array $invoiceData, float $invoiceSubtotal): void
    {
        $sumDetailValues = array_sum(array_map(fn($item) => (float)($item['calculated_detail_value'] ?? 0), $calculatedItems));

        $expectedTotal = $invoiceSubtotal;

        // 1. Calculate Expected Invoice-Level Discount
        $invoiceDiscount = 0;
        $fatDisc = (float) ($invoiceData['fat_disc'] ?? 0);
        $fatDiscPer = (float) ($invoiceData['fat_disc_per'] ?? 0);
        if ($fatDisc > 0) {
            $invoiceDiscount = $fatDisc;
        } elseif ($fatDiscPer > 0) {
            $invoiceDiscount = $invoiceSubtotal * ($fatDiscPer / 100);
        }
        $expectedTotal -= $invoiceDiscount;

        // 2. Calculate Expected Invoice-Level Additional
        $invoiceAdditional = 0;
        $fatPlus = (float) ($invoiceData['fat_plus'] ?? 0);
        $fatPlusPer = (float) ($invoiceData['fat_plus_per'] ?? 0);
        if ($fatPlus > 0) {
            $invoiceAdditional = $fatPlus;
        } elseif ($fatPlusPer > 0) {
            $invoiceAdditional = $invoiceSubtotal * ($fatPlusPer / 100);
        }
        $expectedTotal += $invoiceAdditional;

        // 3. Calculate Expected Invoice-Level VAT
        $invoiceVat = 0;
        $vatValue = (float) ($invoiceData['vat_value'] ?? 0);
        $vatPercentage = (float) ($invoiceData['vat_percentage'] ?? 0);
        if ($vatValue > 0) {
            $invoiceVat = $vatValue;
        } elseif ($vatPercentage > 0) {
            $invoiceVat = $expectedTotal * ($vatPercentage / 100);
        }
        $expectedTotal += $invoiceVat;

        // 4. Calculate Expected Invoice-Level Withholding Tax
        $invoiceTax = 0;
        $taxValue = (float) ($invoiceData['withholding_tax_value'] ?? 0);
        $taxPercentage = (float) ($invoiceData['withholding_tax_percentage'] ?? 0);
        if ($taxValue > 0) {
            $invoiceTax = $taxValue;
        } elseif ($taxPercentage > 0) {
            $invoiceTax = ($expectedTotal - $invoiceVat) * ($taxPercentage / 100);
        }
        $expectedTotal -= $invoiceTax;

        if (abs($sumDetailValues - $expectedTotal) > self::TOLERANCE * count($calculatedItems)) {
            throw new InvalidArgumentException(
                sprintf(
                    'خطأ في إجمالي الفاتورة النهائي. المتوقع: %.2f، مجموع الأصناف: %.2f. (فرعي: %.2f، خصم فاتورة: %.2f، إضافي فاتورة: %.2f، ضريبة فاتورة: %.2f، خصم منبع: %.2f)',
                    $expectedTotal,
                    $sumDetailValues,
                    $invoiceSubtotal,
                    $invoiceDiscount,
                    $invoiceAdditional,
                    $invoiceVat,
                    $invoiceTax
                )
            );
        }
    }
}
