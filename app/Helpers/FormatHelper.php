<?php

if (! function_exists('formatNumber')) {
    /**
     * Format a number with a specified number of decimal places.
     *
     * @param  float  $number
     * @param  int  $decimals
     * @param  string  $decimalSeparator
     * @param  string  $thousandsSeparator
     * @return string
     */
    function formatNumber($number, $decimals = 2, $decimalSeparator = '.', $thousandsSeparator = ',')
    {
        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}

if (! function_exists('formatCurrency')) {
    /**
     * Format a currency value with a specified number of decimal places and currency symbol.
     *
     * @param  float  $amount
     * @param  int  $decimals
     * @param  string  $decimalSeparator
     * @param  string  $thousandsSeparator
     * @param  string  $currencySymbol
     * @return string
     */
    function formatCurrency($amount, $decimals = 2, $decimalSeparator = '.', $thousandsSeparator = ',')
    {
        return formatNumber($amount, $decimals, $decimalSeparator, $thousandsSeparator);
    }
    // $currencySymbol ='ج.م' last attribute for first function
    // .'     -'. $currencySymbol last attribute for second function
}

if (! function_exists('formatBarcode')) {
    function formatBarcode($barcode)
    {
        return str_replace(' ', '', $barcode);
    }
}

if (! function_exists('formatQuantity')) {
    // format quantity
    function formatQuantity($quantity)
    {
        return number_format($quantity, 2, '.', '');
    }
}

if (! function_exists('formatHoursMinutes')) {
    /**
     * Format hours in hours.minutes format (e.g., 1.03 for 1 hour 3 minutes)
     * This function handles both input formats:
     * - Decimal hours (e.g., 1.5 = 1 hour 30 minutes) - OLD FORMAT
     * - Hours.minutes (e.g., 1.30 = 1 hour 30 minutes) - NEW FORMAT
     *
     * @param  float  $value  The value representing hours
     * @return string Formatted string in hours.minutes format
     */
    function formatHoursMinutes($value)
    {
        if (! $value || $value == 0) {
            return '0.00';
        }

        // Convert decimal hours to hours.minutes format
        // Input: 0.95 (decimal hours) = 57 minutes = 0.57 (hours.minutes)
        // Input: 1.5 (decimal hours) = 1 hour 30 minutes = 1.30 (hours.minutes)

        // Convert to total minutes
        $totalMinutes = round($value * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        // Format with leading zero for minutes if needed
        return sprintf('%d.%02d', $hours, $minutes);
    }
}

if (! function_exists('sumHoursMinutes')) {
    /**
     * Sum multiple hours.minutes values correctly
     * Example: 0.45 + 0.50 = 1.35 (45 min + 50 min = 95 min = 1h 35m)
     *
     * @param  array  $values  Array of hours.minutes values
     * @return float Sum in hours.minutes format
     */
    function sumHoursMinutes(array $values): float
    {
        $totalMinutes = 0;

        foreach ($values as $value) {
            if (! $value || $value == 0) {
                continue;
            }

            // Convert hours.minutes to total minutes
            $hours = floor($value);
            $minutes = round(($value - $hours) * 100);
            $totalMinutes += ($hours * 60) + $minutes;
        }

        // Convert back to hours.minutes format
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return $hours + ($minutes / 100);
    }
}
