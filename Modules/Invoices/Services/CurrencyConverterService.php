<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use Modules\Settings\Models\Currency;

/**
 * Service for converting amounts between currencies
 *
 * This service handles all currency conversions in the system,
 * converting amounts to/from the base currency using exchange rates.
 */
class CurrencyConverterService
{
    /**
     * Convert amount from specified currency to base currency
     *
     * @param float $amount Amount in original currency
     * @param int $currencyId Currency ID
     * @param float|null $exchangeRate Optional exchange rate (uses latest if null)
     * @return float Amount in base currency
     */
    public function convertToBase(float $amount, int $currencyId, ?float $exchangeRate = null): float
    {
        // Get currency
        $currency = Currency::find($currencyId);

        // If currency not found or is default, no conversion needed
        if (!$currency || $currency->is_default) {
            return round($amount, 2);
        }

        // Use provided exchange rate or fetch latest
        $rate = $exchangeRate ?? getExchangeRate($currencyId);

        // Convert: amount × exchange_rate
        $converted = $amount * $rate;

        return round($converted, 2);
    }

    /**
     * Convert amount from base currency to specified currency
     *
     * @param float $amount Amount in base currency
     * @param int $currencyId Target currency ID
     * @param float|null $exchangeRate Optional exchange rate (uses latest if null)
     * @return float Amount in target currency
     */
    public function convertFromBase(float $amount, int $currencyId, ?float $exchangeRate = null): float
    {
        // Get currency
        $currency = Currency::find($currencyId);

        // If currency not found or is default, no conversion needed
        if (!$currency || $currency->is_default) {
            return round($amount, 2);
        }

        // Use provided exchange rate or fetch latest
        $rate = $exchangeRate ?? getExchangeRate($currencyId);

        // Prevent division by zero
        if ($rate == 0) {
            return round($amount, 2);
        }

        // Convert: amount ÷ exchange_rate
        $converted = $amount / $rate;

        return round($converted, 2);
    }

    /**
     * Convert multiple fields in an array to base currency
     *
     * @param array $data Data array
     * @param array $fields Field names to convert
     * @param int $currencyId Currency ID
     * @param float|null $exchangeRate Optional exchange rate
     * @return array Data with converted fields
     */
    public function convertFieldsToBase(array $data, array $fields, int $currencyId, ?float $exchangeRate = null): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_numeric($data[$field])) {
                $data[$field] = $this->convertToBase(
                    (float) $data[$field],
                    $currencyId,
                    $exchangeRate
                );
            }
        }

        return $data;
    }

    /**
     * Convert fields in array of items (e.g., invoice items)
     *
     * @param array $items Array of items
     * @param array $fields Field names to convert in each item
     * @param int $currencyId Currency ID
     * @param float|null $exchangeRate Optional exchange rate
     * @return array Items with converted fields
     */
    public function convertItemsToBase(array $items, array $fields, int $currencyId, ?float $exchangeRate = null): array
    {
        return array_map(function ($item) use ($fields, $currencyId, $exchangeRate) {
            return $this->convertFieldsToBase($item, $fields, $currencyId, $exchangeRate);
        }, $items);
    }
}
