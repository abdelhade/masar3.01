<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Enums\ItemType;
use App\Helpers\ItemViewModel;
use App\Models\OperationItems;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoiceItemController extends Controller
{
    /**
     * Get item data for adding to invoice
     * Returns the same structure as addItemFromSearchFast but via API
     */
    public function getItemForInvoice(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|integer',
            'price_type' => 'nullable|integer',
            'store_id' => 'nullable|integer',
            'current_items' => 'nullable|array', // للتحقق من وجود الصنف
        ]);

        $itemId = $request->input('item_id');
        $type = $request->input('type');
        $priceTypeId = $request->input('price_type', 1);
        $storeId = $request->input('store_id');
        $currentItems = $request->input('current_items', []);

        $item = Item::with([
            'units' => fn($q) => $q->orderBy('item_units.u_val', 'asc'),
            'prices'
        ])->find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => __('Item not found')
            ], 404);
        }

        // ✅ فحص الرصيد المتاح قبل الإضافة (فقط لفواتير البيع)
        if (in_array($type, [10, 12, 14, 16, 22])) {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user && !$user->can('prevent_transactions_without_stock')) {
                $availableQty = OperationItems::where('item_id', $item->id)
                    ->where('detail_store', $storeId)
                    ->selectRaw('SUM(qty_in - qty_out) as total')
                    ->value('total') ?? 0;

                if ($availableQty <= 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'insufficient_stock',
                        'message' => __('Insufficient stock available for this item in the selected store.')
                    ], 400);
                }
            }
        }

        // التحقق من وجود الصنف في الفاتورة
        $existingItemIndex = null;
        foreach ($currentItems as $index => $invoiceItem) {
            if (($invoiceItem['item_id'] ?? null) == $item->id) {
                $existingItemIndex = $index;
                break;
            }
        }

        // إذا كان الصنف موجود
        if ($existingItemIndex !== null) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'index' => $existingItemIndex
            ]);
        }

        // إضافة صنف جديد
        $firstUnit = $item->units->first();
        $unitId = $firstUnit?->id;

        // حساب السعر
        $price = $this->calculateItemPrice($item, $unitId, $priceTypeId, $type);

        // ✅ فحص السعر الصفري للمشتريات
        if (in_array($type, [11, 15]) && $price == 0) {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user && !$user->can('allow_purchase_with_zero_price')) {
                return response()->json([
                    'success' => false,
                    'error' => 'zero_price',
                    'message' => __('Purchase price cannot be zero.')
                ], 400);
            }
        }

        $vm = new ItemViewModel(null, $item, $unitId);
        $unitOptions = $vm->getUnitOptions();
        $availableUnits = collect($unitOptions)->map(function ($unit) {
            return [
                'id' => $unit['value'],
                'name' => $unit['label'],
                'u_val' => $unit['u_val'] ?? 1,
            ];
        });

        $quantity = 1; // Default quantity

        $newItem = [
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'name' => $item->name,
            'quantity' => $quantity,
            'price' => $price,
            'sub_value' => $price * $quantity,
            'discount' => 0,
            'available_units' => $availableUnits->toArray(),
            'length' => null,
            'width' => null,
            'height' => null,
            'density' => 1,
            'batch_number' => null,
            'expiry_date' => null,
        ];

        return response()->json([
            'success' => true,
            'index' => count($currentItems), // Index where it will be added
            'item' => $newItem
        ]);
    }

    /**
     * Calculate item price based on invoice type and unit
     * Same logic as CreateInvoiceForm::calculateItemPrice
     */
    private function calculateItemPrice($item, $unitId, $priceTypeId, $type)
    {
        if (!$item || !$unitId) {
            return 0;
        }

        $price = 0;

        // 1. منطق فواتير المشتريات وأوامر الشراء (11, 15)
        if (in_array($type, [11, 15])) {
            // محاولة جلب آخر سعر شراء لنفس الصنف ونفس الوحدة
            $lastPurchasePrice = OperationItems::where('item_id', $item->id)
                ->where('unit_id', $unitId)
                ->where('is_stock', 1)
                ->whereIn('pro_tybe', [11, 20])
                ->where('qty_in', '>', 0)
                ->orderBy('created_at', 'desc')
                ->value('item_price');

            if ($lastPurchasePrice && $lastPurchasePrice > 0) {
                $price = $lastPurchasePrice;
            } else {
                // إذا لم يوجد سعر سابق لهذه الوحدة، نحسب بناءً على التكلفة المتوسطة ومعامل التحويل
                $unit = $item->units->where('id', $unitId)->first();
                $uVal = $unit->pivot->u_val ?? 1;
                $averageCost = $item->average_cost ?? 0;
                $price = $averageCost * $uVal;
            }
        }
        // 2. منطق فواتير التوالف (18)
        elseif ($type == 18) {
            $unit = $item->units->where('id', $unitId)->first();
            $uVal = $unit->pivot->u_val ?? 1;
            $averageCost = $item->average_cost ?? 0;
            $price = $averageCost * $uVal;
        }
        // 3. منطق فواتير المبيعات وغيرها
        else {
            $vm = new ItemViewModel(null, $item, $unitId);
            $salePrices = $vm->getUnitSalePrices();
            $price = $salePrices[$priceTypeId]['price'] ?? 0;
        }

        return $price;
    }

    /**
     * Get item details for display
     */
    public function getItemDetails(Request $request, $id)
    {
        $item = Item::with(['units', 'prices'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => __('Item not found')
            ], 404);
        }

        $storeId = $request->input('store_id');
        $type = $request->input('type', 10);

        $availableQtyInSelectedStore = OperationItems::where('item_id', $item->id)
            ->where('detail_store', $type == 21 ? $request->input('acc1_id') : $storeId)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        $totalAvailableQty = OperationItems::where('item_id', $item->id)
            ->selectRaw('SUM(qty_in - qty_out) as total')
            ->value('total') ?? 0;

        $lastPurchasePrice = OperationItems::where('item_id', $item->id)
            ->where('is_stock', 1)
            ->whereIn('pro_tybe', [11, 20])
            ->where('qty_in', '>', 0)
            ->orderBy('created_at', 'desc')
            ->value('item_price') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $item->name,
                'code' => $item->code ?? '',
                'available_quantity_in_store' => $availableQtyInSelectedStore,
                'total_available_quantity' => $totalAvailableQty,
                'average_cost' => $item->average_cost ?? 0,
                'last_purchase_price' => $lastPurchasePrice,
                'description' => $item->description ?? ''
            ]
        ]);
    }
}

