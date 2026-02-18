<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use Modules\Invoices\Repositories\ItemSearchRepository;

/**
 * Service for item search operations
 */
class ItemSearchService
{
    public function __construct(
        private readonly ItemSearchRepository $itemSearchRepository
    ) {}

    /**
     * Search items
     *
     * @param string $term
     * @param int|null $branchId
     * @param int $limit
     * @return array
     */
    public function searchItems(string $term, ?int $branchId = null, int $limit = 50): array
    {
        if (strlen($term) < 2) {
            return [
                'success' => true,
                'items' => [],
                'message' => __('invoices.search_term_too_short'),
            ];
        }

        $items = $this->itemSearchRepository->searchItems($term, $branchId, $limit);

        return [
            'success' => true,
            'items' => $items,
            'count' => count($items),
        ];
    }

    /**
     * Get item details
     *
     * @param int $itemId
     * @param int|null $customerId
     * @param int|null $branchId
     * @return array
     */
    public function getItemDetails(int $itemId, ?int $customerId = null, ?int $branchId = null, ?int $warehouseId = null): array
    {
        try {
            $details = $this->itemSearchRepository->getItemDetails($itemId, $customerId, $branchId, $warehouseId);

            return [
                'success' => true,
                'data' => $details,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Item Details Error: ' . $e->getMessage(), [
                'item_id' => $itemId,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'message' => __('invoices.item_not_found'),
            ];
        }
    }

    /**
     * Get recommended items for customer
     *
     * @param int $customerId
     * @param int $limit
     * @return array
     */
    public function getRecommendedItems(int $customerId, int $limit = 10): array
    {
        $items = $this->itemSearchRepository->getRecommendedItems($customerId, $limit);

        return [
            'success' => true,
            'items' => $items,
        ];
    }

    /**
     * Get all items in lite format (for client-side search)
     *
     * @param int|null $branchId
     * @param int|null $type
     * @return array
     */
    public function getAllItemsLite(?int $branchId = null, ?int $type = null): array
    {
        $items = $this->itemSearchRepository->getAllItemsLite($branchId, $type);

        return $items;
    }

    /**
     * Quick create item (for inline creation during invoice)
     *
     * @param array $data
     * @return array
     */
    public function quickCreateItem(array $data): array
    {
        return $this->itemSearchRepository->quickCreateItem($data);
    }

    /**
     * Get item price for specific price list and unit
     *
     * @param int $itemId
     * @param int $priceListId
     * @param int $unitId
     * @return array
     */
    public function getItemPriceForPriceList(int $itemId, int $priceListId, int $unitId): array
    {
        $price = $this->itemSearchRepository->getItemPriceForPriceList($itemId, $priceListId, $unitId);

        return [
            'success' => true,
            'price' => $price,
        ];
    }
}
