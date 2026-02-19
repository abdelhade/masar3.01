<?php

declare(strict_types=1);

namespace Modules\Invoices\Repositories;

use App\Models\OperHead;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;

/**
 * Repository for invoice CRUD operations
 */
class InvoiceRepository
{
    /**
     * Create new invoice
     *
     * @param array $data
     * @return OperHead
     */
    public function create(array $data): OperHead
    {
        return DB::transaction(function () use ($data) {
            // Create invoice header
            $invoice = OperHead::create([
                'type' => $data['type'],
                'branch_id' => $data['branch_id'],
                'acc1_id' => $data['acc1_id'],
                'acc2_id' => $data['acc2_id'],
                'pro_date' => $data['pro_date'],
                'notes' => $data['notes'] ?? null,
                'currency_id' => $data['currency_id'] ?? 1,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'subtotal' => $data['subtotal'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_value' => $data['discount_value'] ?? 0,
                'additional_percentage' => $data['additional_percentage'] ?? 0,
                'additional_value' => $data['additional_value'] ?? 0,
                'vat_percentage' => $data['vat_percentage'] ?? 0,
                'vat_value' => $data['vat_value'] ?? 0,
                'withholding_tax_percentage' => $data['withholding_tax_percentage'] ?? 0,
                'withholding_tax_value' => $data['withholding_tax_value'] ?? 0,
                'total' => $data['total'],
                'received_from_client' => $data['received_from_client'] ?? 0,
                'remaining' => $data['remaining'] ?? 0,
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);

            // Create invoice items
            if (!empty($data['items'])) {
                $this->createInvoiceItems($invoice->id, $data['items']);
            }

            return $invoice;
        });
    }

    /**
     * Update existing invoice
     *
     * @param int $invoiceId
     * @param array $data
     * @return OperHead
     */
    public function update(int $invoiceId, array $data): OperHead
    {
        return DB::transaction(function () use ($invoiceId, $data) {
            $invoice = OperHead::findOrFail($invoiceId);

            // Update invoice header
            $invoice->update([
                'acc1_id' => $data['acc1_id'],
                'acc2_id' => $data['acc2_id'],
                'pro_date' => $data['pro_date'],
                'notes' => $data['notes'] ?? null,
                'currency_id' => $data['currency_id'] ?? 1,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'subtotal' => $data['subtotal'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_value' => $data['discount_value'] ?? 0,
                'additional_percentage' => $data['additional_percentage'] ?? 0,
                'additional_value' => $data['additional_value'] ?? 0,
                'vat_percentage' => $data['vat_percentage'] ?? 0,
                'vat_value' => $data['vat_value'] ?? 0,
                'withholding_tax_percentage' => $data['withholding_tax_percentage'] ?? 0,
                'withholding_tax_value' => $data['withholding_tax_value'] ?? 0,
                'total' => $data['total'],
                'received_from_client' => $data['received_from_client'] ?? 0,
                'remaining' => $data['remaining'] ?? 0,
                'updated_at' => now(),
            ]);

            // Delete old items and create new ones
            OperationItems::where('oper_id', $invoiceId)->delete();
            
            if (!empty($data['items'])) {
                $this->createInvoiceItems($invoiceId, $data['items']);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Delete invoice
     *
     * @param int $invoiceId
     * @return bool
     */
    public function delete(int $invoiceId): bool
    {
        return DB::transaction(function () use ($invoiceId) {
            $invoice = OperHead::findOrFail($invoiceId);

            // Delete invoice items
            OperationItems::where('oper_id', $invoiceId)->delete();

            // Delete journal entries if exists
            DB::table('journal_details')
                ->where('oper_id', $invoiceId)
                ->delete();

            DB::table('journal_head')
                ->where('oper_id', $invoiceId)
                ->delete();

            // Delete invoice
            return $invoice->delete();
        });
    }

    /**
     * Create invoice items
     *
     * @param int $invoiceId
     * @param array $items
     * @return void
     */
    private function createInvoiceItems(int $invoiceId, array $items): void
    {
        foreach ($items as $index => $item) {
            OperationItems::create([
                'oper_id' => $invoiceId,
                'item_id' => $item['item_id'],
                'unit_id' => $item['unit_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'discount' => $item['discount'] ?? 0,
                'additional' => $item['additional'] ?? 0,
                'sub_value' => $item['sub_value'],
                'batch_number' => $item['batch_number'] ?? null,
                'expiry_date' => $item['expiry_date'] ?? null,
                'notes' => $item['notes'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * Get invoice by ID
     *
     * @param int $invoiceId
     * @return OperHead|null
     */
    public function findById(int $invoiceId): ?OperHead
    {
        return OperHead::with(['items', 'account1', 'account2', 'branch'])
            ->find($invoiceId);
    }

    /**
     * Get invoice items
     *
     * @param int $invoiceId
     * @return array
     */
    public function getInvoiceItems(int $invoiceId): array
    {
        return OperationItems::where('oper_id', $invoiceId)
            ->with(['item', 'unit'])
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    /**
     * Check if invoice can be edited
     *
     * @param int $invoiceId
     * @return bool
     */
    public function canEdit(int $invoiceId): bool
    {
        $invoice = OperHead::find($invoiceId);
        
        if (!$invoice) {
            return false;
        }

        // Check if invoice is locked
        if ($invoice->locked) {
            return false;
        }

        // Check if invoice is converted
        if ($invoice->converted_to) {
            return false;
        }

        return true;
    }

    /**
     * Check if invoice can be deleted
     *
     * @param int $invoiceId
     * @return bool
     */
    public function canDelete(int $invoiceId): bool
    {
        $invoice = OperHead::find($invoiceId);
        
        if (!$invoice) {
            return false;
        }

        // Check if invoice is locked
        if ($invoice->locked) {
            return false;
        }

        // Check if invoice has related documents
        $hasRelated = OperHead::where('converted_from', $invoiceId)->exists();
        
        return !$hasRelated;
    }
}
