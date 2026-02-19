<?php

declare(strict_types=1);

namespace Modules\POS\Services;

use Illuminate\Support\Collection;
use Modules\POS\Models\CashierTransaction;
use Modules\POS\Models\KitchenPrinterStation;

/**
 * Service for managing kitchen printer operations.
 *
 * This service handles determining which printer stations should receive
 * print jobs based on transaction items and their categories.
 */
class KitchenPrinterService
{
    /**
     * Determine which printer stations are required based on transaction items.
     *
     * This method analyzes all items in the transaction, identifies their categories,
     * and returns the unique set of active printer stations that should receive
     * print jobs. If no stations are found, it returns the default station if available.
     *
     * @param  CashierTransaction  $transaction  The transaction to analyze
     * @return Collection<int, KitchenPrinterStation> Collection of printer stations
     */
    public function determinePrinterStations(CashierTransaction $transaction): Collection
    {
        $stations = collect();

        // Get all items from the transaction
        $items = $transaction->items ?? [];

        foreach ($items as $item) {
            // Get the category_id from the item
            $categoryId = $item['category_id'] ?? null;

            if ($categoryId) {
                // Get active printer stations associated with this category
                $categoryStations = KitchenPrinterStation::whereHas('categories', function ($query) use ($categoryId) {
                    $query->where('categories.id', $categoryId);
                })
                    ->where('is_active', true)
                    ->get();

                $stations = $stations->merge($categoryStations);
            }
        }

        // Remove duplicates based on station ID
        $stations = $stations->unique('id');

        // If no stations found, use the default station
        if ($stations->isEmpty()) {
            $defaultStation = KitchenPrinterStation::where('is_default', true)
                ->where('is_active', true)
                ->first();

            if ($defaultStation) {
                $stations->push($defaultStation);
            }
        }

        return $stations;
    }

    /**
     * Get items from a transaction that should be printed to a specific station.
     *
     * This method filters transaction items to return only those whose categories
     * are associated with the given printer station. If the station is the default
     * station and no items match, it returns all items.
     *
     * @param  CashierTransaction  $transaction  The transaction containing items
     * @param  KitchenPrinterStation  $station  The printer station to filter for
     * @return Collection<int, array> Collection of items for this station
     */
    public function getItemsForStation(
        CashierTransaction $transaction,
        KitchenPrinterStation $station
    ): Collection {
        $items = collect();
        $transactionItems = $transaction->items ?? [];

        // Get category IDs associated with this station
        $stationCategoryIds = $station->categories->pluck('id')->toArray();

        foreach ($transactionItems as $item) {
            $categoryId = $item['category_id'] ?? null;

            // Include item if its category is associated with this station
            if ($categoryId && in_array($categoryId, $stationCategoryIds)) {
                $items->push($item);
            }
        }

        // If this is the default station and no items matched, include all items
        if ($station->is_default && $items->isEmpty()) {
            $items = collect($transactionItems);
        }

        return $items;
    }
}
