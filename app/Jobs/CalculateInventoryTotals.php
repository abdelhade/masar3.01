<?php

namespace App\Jobs;

use App\Services\ItemsQueryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CalculateInventoryTotals implements ShouldQueue
{
    use Queueable;

    private string $search;
    private ?int $selectedGroup;
    private ?int $selectedCategory;
    private string $priceType;
    private ?int $selectedWarehouse;
    private string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $search = '',
        ?int $selectedGroup = null,
        ?int $selectedCategory = null,
        string $priceType = 'average_cost',
        ?int $selectedWarehouse = null
    ) {
        $this->search = $search;
        $this->selectedGroup = $selectedGroup;
        $this->selectedCategory = $selectedCategory;
        $this->priceType = $priceType;
        $this->selectedWarehouse = $selectedWarehouse;
        
        // Generate unique cache key based on filters
        $this->cacheKey = 'inventory_totals_' . md5(json_encode([
            'search' => $search,
            'group' => $selectedGroup,
            'category' => $selectedCategory,
            'price' => $priceType,
            'warehouse' => $selectedWarehouse,
        ]));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $queryService = new ItemsQueryService();

        // Calculate totals
        $totals = [
            'quantity' => $queryService->getTotalQuantity(
                $this->search,
                $this->selectedGroup,
                $this->selectedCategory,
                $this->selectedWarehouse
            ),
            'amount' => $queryService->getTotalAmount(
                $this->search,
                $this->selectedGroup,
                $this->selectedCategory,
                $this->priceType,
                $this->selectedWarehouse
            ),
            'items' => $queryService->getTotalItems(
                $this->search,
                $this->selectedGroup,
                $this->selectedCategory,
                $this->selectedWarehouse
            ),
            'calculated_at' => now()->toDateTimeString(),
        ];

        // Store in cache for 5 minutes
        Cache::put($this->cacheKey, $totals, 300);

        // Broadcast event (optional - for real-time updates)
        // event(new InventoryTotalsCalculated($this->cacheKey, $totals));
    }

    /**
     * Get cache key
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }
}
