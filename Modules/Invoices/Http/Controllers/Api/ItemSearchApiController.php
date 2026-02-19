<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invoices\Services\ItemSearchService;

/**
 * API Controller for item search operations
 */
class ItemSearchApiController extends Controller
{
    public function __construct(
        private readonly ItemSearchService $itemSearchService
    ) {}

    /**
     * Search items
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchItems(Request $request): JsonResponse
    {
        $term = $request->query('term', '');
        $branchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
        $limit = $request->query('limit', 50);

        $result = $this->itemSearchService->searchItems($term, $branchId, (int) $limit);

        return response()->json($result);
    }

    /**
     * Get item details
     *
     * @param Request $request
     * @param int $itemId
     * @return JsonResponse
     */
    public function getItemDetails(Request $request, int $itemId): JsonResponse
    {
        try {
            $customerId = $request->query('customer_id') ? (int) $request->query('customer_id') : null;
            $branchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
            $warehouseId = $request->query('warehouse_id') ? (int) $request->query('warehouse_id') : null;

            $result = $this->itemSearchService->getItemDetails($itemId, $customerId, $branchId, $warehouseId);

            if (!$result['success']) {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل الصنف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommended items for customer
     *
     * @param Request $request
     * @param int $customerId
     * @return JsonResponse
     */
    public function getRecommendedItems(Request $request, int $customerId): JsonResponse
    {
        $limit = $request->query('limit', 10);

        $result = $this->itemSearchService->getRecommendedItems($customerId, (int) $limit);

        return response()->json($result);
    }

    /**
     * Get all items in lite format (for client-side search)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLiteItems(Request $request): JsonResponse
    {
        // Handle branch_id properly - convert "null" string to actual null
        $branchIdParam = $request->query('branch_id');
        $branchId = null;

        if ($branchIdParam !== null && $branchIdParam !== 'null' && $branchIdParam !== '') {
            $branchId = (int) $branchIdParam;
        }

        $typeParam = $request->query('type');
        $type = null;

        if ($typeParam !== null && $typeParam !== 'null' && $typeParam !== '') {
            $type = (int) $typeParam;
        }

        $result = $this->itemSearchService->getAllItemsLite($branchId, $type);

        return response()->json($result);
    }

    /**
     * Quick create item (for inline creation during invoice)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function quickCreateItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'unit_id' => 'required|integer|exists:units,id',
        ]);

        try {
            $item = $this->itemSearchService->quickCreateItem($validated);

            return response()->json([
                'success' => true,
                'message' => __('Item created successfully'),
                'item' => $item,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to create item: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get item price for specific price list and unit
     *
     * @param Request $request
     * @param int $itemId
     * @return JsonResponse
     */
    public function getItemPrice(Request $request, int $itemId): JsonResponse
    {
        $priceListId = $request->query('price_list_id') ? (int) $request->query('price_list_id') : null;
        $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;

        if (!$priceListId || !$unitId) {
            return response()->json([
                'success' => false,
                'message' => 'price_list_id and unit_id are required',
                'price' => null
            ], 400);
        }

        try {
            $result = $this->itemSearchService->getItemPriceForPriceList($itemId, $priceListId, $unitId);
            return response()->json($result);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب السعر: ' . $e->getMessage(),
                'price' => null
            ], 500);
        }
    }
}
