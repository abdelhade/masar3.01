<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invoices\Http\Requests\SaveInvoiceRequest;
use Modules\Invoices\Http\Requests\UpdateInvoiceRequest;
use Modules\Invoices\Services\InvoiceCreationService;
use Modules\Invoices\Services\InvoiceUpdateService;

/**
 * API Controller for invoice CRUD operations
 */
class InvoiceApiController extends Controller
{
    public function __construct(
        private readonly InvoiceCreationService $creationService,
        private readonly InvoiceUpdateService $updateService
    ) {}

    /**
     * Create new invoice
     *
     * @param SaveInvoiceRequest $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {



        $result = $this->creationService->createInvoice($request->validated());

        $statusCode = $result['success'] ? 201 : 422;

        return response()->json($result, $statusCode);
    }

    /**
     * Update existing invoice
     *
     * @param UpdateInvoiceRequest $request
     * @param int $invoiceId
     * @return JsonResponse
     */
    public function update(UpdateInvoiceRequest $request, int $invoiceId): JsonResponse
    {
        $result = $this->updateService->updateInvoice($invoiceId, $request->validated());

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    /**
     * Delete invoice
     *
     * @param int $invoiceId
     * @return JsonResponse
     */
    public function destroy(int $invoiceId): JsonResponse
    {
        $result = $this->updateService->deleteInvoice($invoiceId);

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
