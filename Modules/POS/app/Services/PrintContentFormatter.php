<?php

declare(strict_types=1);

namespace Modules\POS\Services;

use Illuminate\Support\Str;
use Modules\POS\Models\CashierTransaction;
use Modules\POS\Models\KitchenPrinterStation;

/**
 * Service for formatting print content for thermal printers.
 *
 * This service formats transaction data into a readable format suitable
 * for thermal printers with a configurable maximum line width.
 */
class PrintContentFormatter
{
    /**
     * Maximum line width for thermal printers.
     */
    private int $lineWidth;

    /**
     * Constructor with dependency injection.
     *
     * @param  KitchenPrinterService  $printerService  Service for printer operations
     */
    public function __construct(
        private KitchenPrinterService $printerService
    ) {
        $this->lineWidth = config('kitchen-printer.line_width', 32);
    }

    /**
     * Format transaction data for printing.
     *
     * Creates a formatted string containing transaction details, items,
     * and station information suitable for thermal printer output.
     *
     * @param  CashierTransaction  $transaction  The transaction to format
     * @param  KitchenPrinterStation  $station  The printer station
     * @return string Formatted print content
     */
    public function format(
        CashierTransaction $transaction,
        KitchenPrinterStation $station
    ): string {
        $lines = [];

        // Header
        $lines[] = $this->centerText('================================');
        $lines[] = $this->centerText(config('app.name', 'RESTAURANT'));
        $lines[] = $this->centerText('طلب مطبخ');
        $lines[] = $this->centerText('================================');
        $lines[] = '';

        // Order information
        $lines[] = $this->formatLine('رقم الطلب:', "#{$transaction->id}");
        $lines[] = $this->formatLine('التاريخ:', now()->format('Y-m-d'));
        $lines[] = $this->formatLine('الوقت:', now()->format('H:i:s'));

        // Conditional table number
        if (! empty($transaction->table_number)) {
            $lines[] = $this->formatLine('رقم الطاولة:', (string) $transaction->table_number);
        }

        // Cashier name
        $cashierName = $transaction->user->name ?? 'N/A';
        $lines[] = $this->formatLine('أمين الصندوق:', $cashierName);
        $lines[] = $this->separator();

        // Items
        $items = $this->printerService->getItemsForStation($transaction, $station);

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 0;
            $productName = $item['product_name'] ?? $item['name'] ?? 'Unknown';
            $unit = $item['unit_name'] ?? $item['unit'] ?? '';

            $lines[] = $this->formatItemLine(
                (float) $quantity,
                $productName,
                $unit
            );

            // Special notes
            if (! empty($item['notes'])) {
                $lines[] = $this->indent("ملاحظة: {$item['notes']}");
            }

            $lines[] = '';
        }

        $lines[] = $this->separator();

        // Footer
        $lines[] = $this->centerText("محطة: {$station->name}");
        $lines[] = $this->centerText('================================');
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Format a line with label and value.
     *
     * @param  string  $label  The label text
     * @param  string  $value  The value text
     * @return string Formatted line
     */
    private function formatLine(string $label, string $value): string
    {
        $labelWidth = 15;
        $label = Str::padRight($label, $labelWidth);

        return $label.$value;
    }

    /**
     * Format an item line with quantity, unit, and name.
     *
     * @param  float  $quantity  Item quantity
     * @param  string  $name  Item name
     * @param  string  $unit  Unit of measurement
     * @return string Formatted item line
     */
    private function formatItemLine(float $quantity, string $name, string $unit): string
    {
        $qtyStr = number_format($quantity, 2);
        $qtyWithUnit = "{$qtyStr} {$unit}";

        // Truncate name if too long
        $maxNameLength = $this->lineWidth - mb_strlen($qtyWithUnit) - 3;
        $name = Str::limit($name, $maxNameLength, '');

        return "{$qtyWithUnit} x {$name}";
    }

    /**
     * Center text within the line width.
     *
     * @param  string  $text  Text to center
     * @return string Centered text
     */
    private function centerText(string $text): string
    {
        $padding = max(0, ($this->lineWidth - mb_strlen($text)) / 2);

        return str_repeat(' ', (int) $padding).$text;
    }

    /**
     * Add indentation to text.
     *
     * @param  string  $text  Text to indent
     * @return string Indented text
     */
    private function indent(string $text): string
    {
        return '  '.$text;
    }

    /**
     * Create a separator line.
     *
     * @return string Separator line
     */
    private function separator(): string
    {
        return str_repeat('-', $this->lineWidth);
    }
}
