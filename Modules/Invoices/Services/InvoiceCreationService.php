<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Invoices\Repositories\InvoiceRepository;

/**
 * Service for creating invoices
 */
class InvoiceCreationService
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceValidationService $validationService,
        private readonly SaveInvoiceService $saveInvoiceService
    ) {}

    /**
     * Create new invoice
     *
     * @param array $data
     * @return array
     */
    public function createInvoice(array $data): array
    {
        try {
            // Server-side validation
            $validation = $this->validationService->validateInvoiceData($data);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => __('invoices.validation_failed'),
                    'errors' => $validation['errors'],
                ];
            }

            // Create invoice using existing SaveInvoiceService
            $invoice = $this->saveInvoiceService->saveInvoice((object) $data, false);

            if (!$invoice) {
                return [
                    'success' => false,
                    'message' => __('invoices.creation_failed'),
                ];
            }

            Log::info('Invoice created successfully', [
                'invoice_id' => $invoice,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => __('invoices.created_successfully'),
                'invoice' => [
                    'id' => $invoice,
                ],
            ];

        } catch (Exception $e) {
            Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => __('invoices.creation_failed'),
                'error' => $e->getMessage(),
            ];
        }
    }
}
