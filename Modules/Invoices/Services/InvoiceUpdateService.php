<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Invoices\Repositories\InvoiceRepository;

/**
 * Service for updating invoices
 */
class InvoiceUpdateService
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceValidationService $validationService,
        private readonly SaveInvoiceService $saveInvoiceService
    ) {}

    /**
     * Update existing invoice
     *
     * @param int $invoiceId
     * @param array $data
     * @return array
     */
    public function updateInvoice(int $invoiceId, array $data): array
    {
        try {
            // Check if invoice can be edited
            if (!$this->invoiceRepository->canEdit($invoiceId)) {
                return [
                    'success' => false,
                    'message' => __('invoices.cannot_edit_invoice'),
                ];
            }

            // Server-side validation
            $validation = $this->validationService->validateInvoiceData($data, $invoiceId);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => __('invoices.validation_failed'),
                    'errors' => $validation['errors'],
                ];
            }

            // Add invoice ID to data
            $data['id'] = $invoiceId;

            // Update invoice using existing SaveInvoiceService
            $result = $this->saveInvoiceService->saveInvoice((object) $data, true);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => __('invoices.update_failed'),
                ];
            }

            Log::info('Invoice updated successfully', [
                'invoice_id' => $invoiceId,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => __('invoices.updated_successfully'),
                'invoice' => [
                    'id' => $invoiceId,
                ],
            ];

        } catch (Exception $e) {
            Log::error('Invoice update failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => __('invoices.update_failed'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete invoice
     *
     * @param int $invoiceId
     * @return array
     */
    public function deleteInvoice(int $invoiceId): array
    {
        try {
            // Check if invoice can be deleted
            if (!$this->invoiceRepository->canDelete($invoiceId)) {
                return [
                    'success' => false,
                    'message' => __('invoices.cannot_delete_invoice'),
                ];
            }

            $this->invoiceRepository->delete($invoiceId);

            Log::info('Invoice deleted successfully', [
                'invoice_id' => $invoiceId,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => __('invoices.deleted_successfully'),
            ];

        } catch (Exception $e) {
            Log::error('Invoice deletion failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => __('invoices.deletion_failed'),
                'error' => $e->getMessage(),
            ];
        }
    }
}
