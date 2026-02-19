<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service for server-side invoice validation
 * Note: Most validation is done client-side, this is for security and data integrity
 */
class InvoiceValidationService
{
    /**
     * Validate invoice data
     *
     * @param array $data
     * @param int|null $invoiceId
     * @return array
     */
    public function validateInvoiceData(array $data, ?int $invoiceId = null): array
    {
        $errors = [];

        // Validate basic fields
        if (empty($data['acc1_id'])) {
            $errors[] = __('invoices.acc1_required');
        }

        if (empty($data['acc2_id'])) {
            $errors[] = __('invoices.acc2_required');
        }

        if (empty($data['pro_date'])) {
            $errors[] = __('invoices.date_required');
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            $errors[] = __('invoices.items_required');
        }

        // Validate items
        if (!empty($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                $itemErrors = $this->validateItem($item, $index + 1, $data);
                $errors = array_merge($errors, $itemErrors);
            }
        }

        // Validate credit limit (for sales invoices)
        if (in_array($data['type'] ?? 0, [10, 12, 14, 16])) {
            $creditLimitError = $this->validateCreditLimit($data);
            if ($creditLimitError) {
                $errors[] = $creditLimitError;
            }
        }

        // Validate expired items
        if (setting('prevent_selling_expired_items', '1') === '1') {
            $expiredErrors = $this->validateExpiredItems($data);
            $errors = array_merge($errors, $expiredErrors);
        }

        // Validate stock availability (if not allowing negative stock)
        if (setting('allow_negative_stock', '0') === '0') {
            $stockErrors = $this->validateStockAvailability($data, $invoiceId);
            $errors = array_merge($errors, $stockErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate single item
     *
     * @param array $item
     * @param int $index
     * @param array $invoiceData
     * @return array
     */
    private function validateItem(array $item, int $index, array $invoiceData): array
    {
        $errors = [];

        if (empty($item['item_id'])) {
            $errors[] = __('invoices.item_required', ['index' => $index]);
        }

        if (empty($item['unit_id'])) {
            $errors[] = __('invoices.unit_required', ['index' => $index]);
        }

        if (!isset($item['quantity']) || $item['quantity'] <= 0) {
            $errors[] = __('invoices.quantity_invalid', ['index' => $index]);
        }

        if (!isset($item['price']) || $item['price'] < 0) {
            $errors[] = __('invoices.price_invalid', ['index' => $index]);
        }

        // Validate item exists and is active
        if (!empty($item['item_id'])) {
            $itemExists = Item::where('id', $item['item_id'])
                ->where('active', 1)
                ->exists();

            if (!$itemExists) {
                $errors[] = __('invoices.item_not_found', ['index' => $index]);
            }
        }

        return $errors;
    }

    /**
     * Validate credit limit
     *
     * @param array $data
     * @return string|null
     */
    private function validateCreditLimit(array $data): ?string
    {
        if (empty($data['acc1_id'])) {
            return null;
        }

        $customer = DB::table('acc_head')
            ->where('id', $data['acc1_id'])
            ->first();

        if (!$customer || !isset($customer->debit_limit) || $customer->debit_limit === null) {
            return null;
        }

        // Calculate current balance
        $currentBalance = DB::table('journal_details')
            ->where('acc_id', $data['acc1_id'])
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? 0;

        $balanceAfterInvoice = $currentBalance + ($data['total'] ?? 0);

        if ($balanceAfterInvoice > $customer->debit_limit) {
            return __('invoices.credit_limit_exceeded', [
                'limit' => number_format($customer->debit_limit, 2),
                'balance' => number_format($balanceAfterInvoice, 2),
            ]);
        }

        return null;
    }

    /**
     * Validate expired items
     *
     * @param array $data
     * @return array
     */
    private function validateExpiredItems(array $data): array
    {
        $errors = [];

        if (empty($data['items'])) {
            return $errors;
        }

        foreach ($data['items'] as $index => $item) {
            if (!empty($item['expiry_date'])) {
                $expiryDate = Carbon::parse($item['expiry_date']);
                
                if ($expiryDate->isPast()) {
                    $itemName = Item::find($item['item_id'])->name ?? __('invoices.unknown_item');
                    $errors[] = __('invoices.item_expired', [
                        'name' => $itemName,
                        'date' => $expiryDate->format('Y-m-d'),
                    ]);
                }
            }
        }

        return $errors;
    }

    /**
     * Validate stock availability
     *
     * @param array $data
     * @param int|null $invoiceId
     * @return array
     */
    private function validateStockAvailability(array $data, ?int $invoiceId = null): array
    {
        $errors = [];

        // Only check for sales/output invoices
        if (!in_array($data['type'] ?? 0, [10, 12, 14, 16, 19, 22, 31, 33])) {
            return $errors;
        }

        if (empty($data['items'])) {
            return $errors;
        }

        foreach ($data['items'] as $index => $item) {
            if (empty($item['item_id']) || empty($item['unit_id'])) {
                continue;
            }

            // Calculate current stock
            $stockQuery = DB::table('operation_items as oi')
                ->join('oper_head as oh', 'oi.oper_id', '=', 'oh.id')
                ->where('oi.item_id', $item['item_id'])
                ->where('oi.unit_id', $item['unit_id']);

            if (!empty($data['branch_id'])) {
                $stockQuery->where('oh.branch_id', $data['branch_id']);
            }

            // Exclude current invoice if editing
            if ($invoiceId) {
                $stockQuery->where('oh.id', '!=', $invoiceId);
            }

            $stock = $stockQuery->selectRaw('
                SUM(CASE 
                    WHEN oh.type IN (11, 13, 15, 17, 20, 23, 30, 32) THEN oi.quantity 
                    WHEN oh.type IN (10, 12, 14, 16, 19, 22, 31, 33) THEN -oi.quantity 
                    ELSE 0 
                END) as stock_quantity
            ')->value('stock_quantity') ?? 0;

            $requiredQuantity = $item['quantity'] ?? 0;

            if ($stock < $requiredQuantity) {
                $itemName = Item::find($item['item_id'])->name ?? __('invoices.unknown_item');
                $errors[] = __('invoices.insufficient_stock', [
                    'name' => $itemName,
                    'available' => number_format($stock, 2),
                    'required' => number_format($requiredQuantity, 2),
                ]);
            }
        }

        return $errors;
    }
}
