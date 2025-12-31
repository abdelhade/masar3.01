<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use InvalidArgumentException;

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
     * Validate exclusive mode rules for discounts, additional charges, and taxes.
     *
     * Ensures that discounts/additional/taxes are applied at either item level OR invoice level, not both.
     *
     * @param array $itemData Item data containing discount/additional/tax fields
     * @param array $invoiceData Invoice data containing discount/additional/tax fields and discount_mode
     *
     * @throws InvalidArgumentException if exclusive mode rules are violated
     */
    public function validateExclusiveMode(array $itemData, array $invoiceData): void
    {
        $discountMode = $invoiceData['discount_mode'] ?? 'invoice_level';

        $itemDiscount = (float) ($itemData['item_discount'] ?? 0);
        $itemAdditional = (float) ($itemData['additional'] ?? 0);
        $itemVatPercentage = (float) ($itemData['item_vat_percentage'] ?? 0);
        $itemVatValue = (float) ($itemData['item_vat_value'] ?? 0);
        $itemWithholdingTaxPercentage = (float) ($itemData['item_withholding_tax_percentage'] ?? 0);
        $itemWithholdingTaxValue = (float) ($itemData['item_withholding_tax_value'] ?? 0);

        $invoiceDiscount = (float) ($invoiceData['fat_disc'] ?? 0);
        $invoiceDiscountPer = (float) ($invoiceData['fat_disc_per'] ?? 0);
        $invoiceAdditional = (float) ($invoiceData['fat_plus'] ?? 0);
        $invoiceAdditionalPer = (float) ($invoiceData['fat_plus_per'] ?? 0);
        $invoiceVatPercentage = (float) ($invoiceData['vat_percentage'] ?? 0);
        $invoiceVatValue = (float) ($invoiceData['vat_value'] ?? 0);
        $invoiceWithholdingTaxPercentage = (float) ($invoiceData['withholding_tax_percentage'] ?? 0);
        $invoiceWithholdingTaxValue = (float) ($invoiceData['withholding_tax_value'] ?? 0);

        if ($discountMode === 'item_level') {
            // In item_level mode: Invoice-level discount/additional/taxes must be zero
            if ($invoiceDiscount > 0 || $invoiceDiscountPer > 0 || $invoiceAdditional > 0 || $invoiceAdditionalPer > 0) {
                throw new InvalidArgumentException(
                    'في وضع الخصومات على مستوى الصنف، يجب أن تكون خصومات/إضافات الفاتورة صفر. ' .
                    \sprintf(
                        'القيم الحالية: خصم الفاتورة=%.2f، نسبة خصم الفاتورة=%.2f، إضافي الفاتورة=%.2f، نسبة إضافي الفاتورة=%.2f',
                        $invoiceDiscount,
                        $invoiceDiscountPer,
                        $invoiceAdditional,
                        $invoiceAdditionalPer
                    )
                );
            }

            // In item_level mode: Invoice-level taxes must be zero
            if ($invoiceVatPercentage > 0 || $invoiceVatValue > 0 || $invoiceWithholdingTaxPercentage > 0 || $invoiceWithholdingTaxValue > 0) {
                throw new InvalidArgumentException(
                    'في وضع الخصومات على مستوى الصنف، يجب أن تكون ضرائب الفاتورة صفر. ' .
                    \sprintf(
                        'القيم الحالية: نسبة ضريبة القيمة المضافة=%.2f، قيمة ضريبة القيمة المضافة=%.2f، نسبة الخصم الضريبي=%.2f، قيمة الخصم الضريبي=%.2f',
                        $invoiceVatPercentage,
                        $invoiceVatValue,
                        $invoiceWithholdingTaxPercentage,
                        $invoiceWithholdingTaxValue
                    )
                );
            }
        } elseif ($discountMode === 'invoice_level') {
            // In invoice_level mode: Item-level discount/additional/taxes must be zero
            if ($itemDiscount > 0 || $itemAdditional > 0) {
                throw new InvalidArgumentException(
                    'في وضع الخصومات على مستوى الفاتورة، يجب أن تكون خصومات/إضافات الصنف صفر. ' .
                    \sprintf(
                        'القيم الحالية: خصم الصنف=%.2f، إضافي الصنف=%.2f',
                        $itemDiscount,
                        $itemAdditional
                    )
                );
            }

            // In invoice_level mode: Item-level taxes must be zero
            if ($itemVatPercentage > 0 || $itemVatValue > 0 || $itemWithholdingTaxPercentage > 0 || $itemWithholdingTaxValue > 0) {
                throw new InvalidArgumentException(
                    'في وضع الخصومات على مستوى الفاتورة، يجب أن تكون ضرائب الصنف صفر. ' .
                    \sprintf(
                        'القيم الحالية: نسبة ضريبة القيمة المضافة=%.2f، قيمة ضريبة القيمة المضافة=%.2f، نسبة الخصم الضريبي=%.2f، قيمة الخصم الضريبي=%.2f',
                        $itemVatPercentage,
                        $itemVatValue,
                        $itemWithholdingTaxPercentage,
                        $itemWithholdingTaxValue
                    )
                );
            }
        } else {
            throw new InvalidArgumentException(
                \sprintf(
                    'وضع الخصومات غير صحيح: %s. القيم المسموحة: item_level أو invoice_level',
                    $discountMode
                )
            );
        }
    }

    /**
     * Validate calculated detail_value.
     *
     * Performs three validation checks:
     * 1. Non-negativity: detail_value must be >= 0
     * 2. Reasonableness: detail_value must be within reasonable bounds
     * 3. Calculation accuracy: detail_value must match the formula
     *
     * @param  float  $detailValue  Calculated detail value to validate
     * @param  array  $itemData  Original item data containing:
     *                           - item_price: float - Unit price
     *                           - quantity: float - Quantity
     *                           - item_discount: float - Item-level discount (optional)
     *                           - additional: float - Item-level additional (optional)
     * @param  array  $calculation  Calculation breakdown containing:
     *                              - item_subtotal: float - Item value before invoice adjustments
     *                              - distributed_discount: float - Invoice discount for this item
     *                              - distributed_additional: float - Invoice additional for this item
     *
     * @throws InvalidArgumentException if validation fails with descriptive error message
     */
    public function validate(float $detailValue, array $itemData, array $calculation): void
    {
        // Validation 1: Non-negativity check
        if ($detailValue < 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'Detail value cannot be negative. Got: %.2f',
                    $detailValue
                )
            );
        }

        // Extract item data for validation
        $itemPrice = (float) ($itemData['item_price'] ?? 0);
        $quantity = (float) ($itemData['quantity'] ?? 0);

        // Validation 2: Reasonableness check
        if (! $this->isReasonable($detailValue, $itemPrice, $quantity)) {
            $maxReasonable = $itemPrice * $quantity * self::MAX_MULTIPLIER;
            throw new InvalidArgumentException(
                sprintf(
                    'Detail value %.2f is unreasonable. Item price: %.2f, Quantity: %.2f, Max reasonable: %.2f',
                    $detailValue,
                    $itemPrice,
                    $quantity,
                    $maxReasonable
                )
            );
        }

        // Validation 3: Calculation accuracy check
        if (! $this->verifyCalculation($detailValue, $calculation)) {
            $expected = $this->calculateExpectedValue($calculation);
            throw new InvalidArgumentException(
                sprintf(
                    'Detail value calculation mismatch. Expected: %.2f, Got: %.2f, Difference: %.4f',
                    $expected,
                    $detailValue,
                    abs($expected - $detailValue)
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

        $expected = $itemSubtotal - $distributedDiscount + $distributedAdditional;

        // Ensure non-negative (same as calculator)
        return max(0, $expected);
    }
}
