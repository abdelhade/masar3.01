<?php

namespace App\Helpers;

use App\Models\Item;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemViewModel
{
    private $item;
    private $selectedUnitId;
    private $selectedWarehouse;
    private $baseQuantityCache = null;

    public function __construct(?string $selectedWarehouse = null, Item $item, ?int $selectedUnitId = null)
    {
        $this->item = $item;
        $this->selectedUnitId = $selectedUnitId;
        $this->selectedWarehouse = $selectedWarehouse;
    }

    public function getUnitOptions(): array
    {
        return $this->item->units->map(fn($unit) => [
            'value' => $unit->id,
            'label' => $unit->name . ' [' . number_format($unit->pivot->u_val ?? 1) . ']',
        ])->toArray();
    }

    /**
     * Get total quantity in base units (u_val = 1), cached per instance.
     */
    private function getTotalBaseQuantity(): float
    {
        if ($this->baseQuantityCache !== null) {
            return $this->baseQuantityCache;
        }

        $query = DB::table('operation_items')
            ->where('item_id', $this->item->id);

        if (!empty($this->selectedWarehouse)) {
            $query->where('detail_store', intval($this->selectedWarehouse));
        }

        $itemRows = $query->get();

        $totalBaseQty = 0;
        foreach ($itemRows as $row) {
            $unit = $this->item->units->firstWhere('id', $row->unit_id);
            $u_val = $unit && isset($unit->pivot) ? $unit->pivot->u_val : 1;
            $qty = ($row->qty_in - $row->qty_out) * $u_val;
            $totalBaseQty += $qty;
        }

        return $this->baseQuantityCache = $totalBaseQty;
    }

    /**
     * Get the quantity in the selected unit (as float).
     */
    public function getCurrentUnitQuantity(): float
    {
        $selectedUnit = $this->item->units->firstWhere('id', $this->selectedUnitId);
        $selectedUVal = $selectedUnit && isset($selectedUnit->pivot) ? $selectedUnit->pivot->u_val : 1;
        $totalBaseQty = $this->getTotalBaseQuantity();

        return $selectedUVal > 0 ? $totalBaseQty / $selectedUVal : 0;
    }

    /**
     * Get formatted quantity for display (integer part and remainder in smaller unit).
     */
    public function getFormattedQuantity(): array
    {
        $selectedUnit = $this->item->units->firstWhere('id', $this->selectedUnitId);
        $selectedUVal = $selectedUnit && isset($selectedUnit->pivot) ? $selectedUnit->pivot->u_val : 1;
        $unitName = $selectedUnit->name ?? '';
        $smallerUnit = $this->item->units->firstWhere('pivot.u_val', 1);
        $smallerUnitName = $smallerUnit->name ?? '';

        $totalBaseQty = $this->getTotalBaseQuantity();

        $integer = $selectedUVal > 0 ? floor($totalBaseQty / $selectedUVal) : 0;
        $remainder = $selectedUVal > 0 ? $totalBaseQty % $selectedUVal : 0;

        return [
            'quantity' => [
                'integer' => $integer,
                'remainder' => $remainder
            ],
            'unitName' => $unitName,
            'smallerUnitName' => $smallerUnitName,
        ];
    }

    public function getUnitBarcode(): array
    {
        return $this->item->barcodes
            ->where('unit_id', $this->selectedUnitId)
            ->map(fn($barcode) => [
                'id' => $barcode->id,
                'barcode' => $barcode->barcode,
            ])->toArray();
    }

    public function getUnitCostPrice(): float
    {
        return $this->item->units->firstWhere('id', $this->selectedUnitId)?->pivot->cost ?? 0;
    }

    public function getQuantityCost(): float
    {
        return $this->getCurrentUnitQuantity() * $this->getUnitCostPrice();
    }

    public function getUnitAverageCost(): float
    {
        return $this->item->average_cost * ($this->item->units->firstWhere('id', $this->selectedUnitId)?->pivot->u_val ?? 0);
    }

    public function getQuantityAverageCost(): float
    {
        return $this->getUnitAverageCost() * $this->getCurrentUnitQuantity();
    }
    
    public function getUnitSalePrices(): array
    {
        if (!$this->selectedUnitId) {
            return [];
        }
        return $this->item->prices
            ->where('pivot.unit_id', $this->selectedUnitId)
            ->mapWithKeys(fn($priceTypeModel) => [
                $priceTypeModel->id => [
                    'name' => $priceTypeModel->name,
                    'price' => $priceTypeModel->pivot->price,
                ]
            ])->toArray();
    }
}
