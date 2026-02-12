<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invoices\Services\InvoiceDataPreparationService;

/**
 * API Controller for invoice data operations
 */
class InvoiceDataApiController extends Controller
{
    public function __construct(
        private readonly InvoiceDataPreparationService $dataPreparationService
    ) {}

    /**
     * Get initial data for invoice form
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getInitialData(Request $request): JsonResponse
    {
        $type = (int) $request->query('type');
        $branchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;

        $result = $this->dataPreparationService->prepareInitialData($type, $branchId);

        return response()->json($result);
    }

    /**
     * Get invoice data for editing
     *
     * @param int $invoiceId
     * @return JsonResponse
     */
    public function getInvoiceForEdit(int $invoiceId): JsonResponse
    {
        $result = $this->dataPreparationService->prepareInvoiceForEdit($invoiceId);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }
}
