<?php

if (!function_exists('formatNumber')) {
    /**
     * Format a number with a specified number of decimal places.
     *
     * @param float $number
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandsSeparator
     * @return string
     */
    function formatNumber($number, $decimals = 2, $decimalSeparator = '.', $thousandsSeparator = ',')
    {
        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format a currency value with a specified number of decimal places and currency symbol.
     *
     * @param float $amount
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandsSeparator
     * @param string $currencySymbol
     * @return string
     */
    function formatCurrency($amount, $decimals = 2, $decimalSeparator = '.', $thousandsSeparator = ',', )
    {
        return formatNumber($amount, $decimals, $decimalSeparator, $thousandsSeparator);
    }
    // $currencySymbol ='ج.م' last attribute for first function
    // .'     -'. $currencySymbol last attribute for second function


    function formatBarcode($barcode)
    {
        return str_replace(' ', '', $barcode);
    }

    // format quantity
    function formatQuantity($quantity)
    {
        return number_format($quantity, 2, '.', '');
    }
}
