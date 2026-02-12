<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use Modules\Invoices\Repositories\InvoiceDataRepository;

/**
 * Service for preparing invoice data for frontend
 */
class InvoiceDataPreparationService
{
    public function __construct(
        private readonly InvoiceDataRepository $invoiceDataRepository
    ) {}

    /**
     * Prepare initial data for invoice form
     *
     * @param int $type
     * @param int|null $branchId
     * @return array
     */
    public function prepareInitialData(int $type, ?int $branchId = null): array
    {
        $data = $this->invoiceDataRepository->getInitialData($type, $branchId);

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Prepare invoice data for editing
     *
     * @param int $invoiceId
     * @return array
     */
    public function prepareInvoiceForEdit(int $invoiceId): array
    {
        $invoiceData = $this->invoiceDataRepository->getInvoiceForEdit($invoiceId);

        if (empty($invoiceData)) {
            return [
                'success' => false,
                'message' => __('invoices.invoice_not_found'),
            ];
        }

        return [
            'success' => true,
            'data' => $invoiceData,
        ];
    }
}
