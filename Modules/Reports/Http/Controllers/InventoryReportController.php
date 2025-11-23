<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Note;
use App\Models\Unit;
use Modules\Accounts\Models\AccHead;
use App\Models\OperationItems;
use Modules\Reports\Services\ReportCalculationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryReportController extends Controller
{
    use ReportCalculationTrait;

    public function generalInventoryBalances()
    {
        // $notes = Note::with('noteDetails')->get();
        // $warehouses = AccHead::where('code', 'like', '1104%')->where('isdeleted', 0)->where('is_basic', 0)->get();

        // $inventoryBalances = Item::with(['units'])
        //     ->paginate(50)
        //     ->through(function ($item) {
        //         $item->current_balance = $this->calculateItemBalance($item->id);
        //         $item->min_balance = $item->min_balance ?? 0;
        //         $item->max_balance = $item->max_balance ?? 999999;
        //         $item->main_unit = $item->units->first();
        //         return $item;
        //     });

        // $totalBalance = $inventoryBalances->sum('current_balance');
        // $totalItems = $inventoryBalances->count();
        // $lowStockItems = $inventoryBalances->where('current_balance', '<=', 'min_balance')->count();
        // $normalStockItems = $inventoryBalances->where('current_balance', '>', 'min_balance')->count();

        // return view('reports::inventory.general-inventory-balances', compact(
        //     'notes',
        //     'warehouses',
        //     'inventoryBalances',
        //     'totalBalance',
        //     'totalItems',
        //     'lowStockItems',
        //     'normalStockItems'
        // ));
        return view('item-management.items.index');
    }

    public function generalInventoryBalancesByStore()
    {
        $warehouses = AccHead::where('code', 'like', '1104%')->where('isdeleted', 0)->where('is_basic', 0)->get();
        $notes = Note::with('noteDetails')->get();
        $selectedWarehouse = null;
        $inventoryBalances = new LengthAwarePaginator([], 0, 50, 1, ['path' => request()->url(), 'query' => request()->query()]);

        if (request('warehouse_id')) {
            $selectedWarehouse = AccHead::find(request('warehouse_id'));
            if ($selectedWarehouse) {
                $query = Item::with(['units'])->where('isdeleted', 0);

                // Apply search filter
                if (request('search')) {
                    $search = request('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('aname', 'like', '%' . $search . '%')
                          ->orWhere('code', 'like', '%' . $search . '%');
                    });
                }

                // Apply note filters
                foreach ($notes as $note) {
                    $noteValue = request('note_' . $note->id);
                    if ($noteValue) {
                        $query->whereHas('notes', function ($q) use ($note, $noteValue) {
                            $q->where('notes.id', $note->id)
                              ->wherePivot('note_detail_name', $noteValue);
                        });
                    }
                }

                $inventoryBalances = $query->paginate(50)
                    ->through(function ($item) use ($selectedWarehouse) {
                        $item->current_balance = $this->calculateItemBalanceByWarehouse($item->id, $selectedWarehouse->id);
                        $item->value = $item->current_balance * ($item->cost_price ?? 0);
                        return $item;
                    });
            }
        }

        $totalBalance = $inventoryBalances->sum('current_balance') ?? 0;
        $totalValue = $inventoryBalances->sum('value') ?? 0;
        $totalItems = $inventoryBalances->count();
        $lowStockItems = $inventoryBalances->filter(function ($item) {
            return $item->current_balance <= ($item->min_balance ?? 0);
        })->count();
        $highStockItems = $inventoryBalances->filter(function ($item) {
            return $item->current_balance >= ($item->max_balance ?? 999999);
        })->count();
        $normalStockItems = $inventoryBalances->filter(function ($item) {
            $min = $item->min_balance ?? 0;
            $max = $item->max_balance ?? 999999;
            return $item->current_balance > $min && $item->current_balance < $max;
        })->count();

        return view('reports::inventory.general-inventory-balances-by-store', compact(
            'warehouses',
            'notes',
            'selectedWarehouse',
            'inventoryBalances',
            'totalBalance',
            'totalValue',
            'totalItems',
            'lowStockItems',
            'highStockItems',
            'normalStockItems'
        ));
    }

    public function generalInventoryMovements()
    {
        // $items = Item::where('isdeleted', 0)->get();
        // $warehouses = AccHead::where('code', 'like', '1104%')->where('isdeleted', 0)->where('is_basic', 0)->get();
        // $notes = Note::with('noteDetails')->get();
        // $selectedItem = null;
        // $movements = OperationItems::whereRaw('0=1')->paginate(50);
        // $currentBalance = 0;

        // if (request('item_id')) {
        //     $selectedItem = Item::with('units')->find(request('item_id'));
        //     if ($selectedItem) {
        //         $warehouseId = request('warehouse_id', 'all');
        //         $fromDate = request('from_date');
        //         $toDate = request('to_date');

        //         $query = OperationItems::where('item_id', $selectedItem->id)
        //             ->where('isdeleted', 0)
        //             ->with(['operhead.type']);

        //         if ($warehouseId !== 'all') {
        //             $query->where('detail_store', $warehouseId);
        //         }

        //         if ($fromDate) {
        //             $query->whereDate('created_at', '>=', $fromDate);
        //         }

        //         if ($toDate) {
        //             $query->whereDate('created_at', '<=', $toDate);
        //         }

        //         $movements = $query->orderBy('created_at', 'asc')->paginate(50);
                
        //         // Calculate running balance for each movement
        //         $runningBalance = 0;
        //         if ($movements->count() > 0) {
        //             // Get the first movement's date to calculate balance before
        //             $firstMovement = $movements->first();
        //             $firstDate = $firstMovement->created_at ? $firstMovement->created_at->format('Y-m-d') : null;
                    
        //             if ($firstDate) {
        //                 // Calculate balance before the first movement in the result set
        //                 $balanceQuery = OperationItems::where('item_id', $selectedItem->id)
        //                     ->where('isdeleted', 0);
                        
        //                 if ($warehouseId !== 'all') {
        //                     $balanceQuery->where('detail_store', $warehouseId);
        //                 }
                        
        //                 $balanceQuery->whereDate('created_at', '<', $firstDate);
        //                 $runningBalance = $balanceQuery->sum('qty_in') - $balanceQuery->sum('qty_out');
        //             }
                    
        //             // Add running balance to each movement
        //             $movements->getCollection()->transform(function ($movement) use (&$runningBalance) {
        //                 $runningBalance += $movement->qty_in - $movement->qty_out;
        //                 $movement->running_balance = $runningBalance;
        //                 return $movement;
        //             });
        //         }
                
        //         $currentBalance = $this->calculateItemBalance($selectedItem->id);
        //     }
        // }

        // $totalIn = $movements->sum('qty_in');
        // $totalOut = $movements->sum('qty_out');
        // $netMovement = $totalIn - $totalOut;
        // $totalOperations = $movements->count();

        // return view('reports::inventory.general-inventory-movements', compact(
        //     'items',
        //     'warehouses',
        //     'notes',
        //     'selectedItem',
        //     'movements',
        //     'currentBalance',
        //     'totalIn',
        //     'totalOut',
        //     'netMovement',
        //     'totalOperations'
        // ));
        
        $itemId = request('item_id');
        $warehouseId = request('warehouse_id', 'all');
        
        return view('item-management.reports.item-movement', compact('itemId', 'warehouseId'));
    }

    // public function generalInventoryReport()
    // {
    //     $items = Item::with('units')->paginate(50);
    //     /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    //     foreach ($items as $item) {
    //         /** @var Item $item */
    //         $item->main_unit = $item->units->first();
    //     }
    //     return view('reports::inventory.general-inventory-report', compact('items'));
    // }

    // public function generalInventoryDailyMovementReport()
    // {
    //     return view('reports::inventory.general-inventory-daily-movement-report');
    // }

    // public function generalInventoryStocktakingReport()
    // {
    //     return view('reports::inventory.general-inventory-stocktaking-report');
    // }

    public function getItemsMaxMinQuantity()
    {
        $items = Item::select('id', 'name', 'code', 'min_order_quantity', 'max_order_quantity')
            ->paginate(50);

        $items->getCollection()->transform(function ($item) {
            $currentQuantity = $this->calculateCurrentQuantity($item->id);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'current_quantity' => $currentQuantity,
                'min_order_quantity' => $item->min_order_quantity,
                'max_order_quantity' => $item->max_order_quantity,
                'status' => $this->getQuantityStatus($item),
                'required_compensation' => $this->getRequiredCompensation($item)
            ];
        });

        return view('reports::inventory.items-max-min-quantity', compact('items'));
    }

    public function pricesCompareReport()
    {
        $priceData = OperationItems::where('pro_tybe', 15)
            ->with(['operhead', 'item'])
            ->select('item_id', 'item_price', 'pro_id')
            ->get();

        if ($priceData->isEmpty()) {
            return view('reports::inventory.prices-compare-report', [
                'items' => [],
                'suppliers' => [],
                'message' => 'لا توجد عروض أسعار متاحة'
            ]);
        }

        $itemsData = $priceData->groupBy('item_id')->map(function ($group) {
            $firstItem = $group->first();
            $itemModel = $firstItem->item ?? Item::find($firstItem->item_id);
            $itemName = $itemModel ? $itemModel->name : 'صنف غير محدد';

            $supplierOffers = $group->map(function ($row) {
                $supplierId = $row->operhead ? $row->operhead->acc1 : null;
                return [
                    'supplier_id' => $supplierId,
                    'price' => (float) $row->item_price,
                ];
            })->filter(function ($offer) {
                return $offer['supplier_id'] !== null && $offer['price'] > 0;
            });

            if ($supplierOffers->isEmpty()) {
                return null;
            }

            $supplierPrices = $supplierOffers->groupBy('supplier_id')->map(function ($offers) {
                return $offers->min('price');
            });

            $bestPrice = $supplierPrices->min();
            $bestSupplierId = $supplierPrices->search($bestPrice);

            return [
                'item_id' => $firstItem->item_id,
                'item_name' => $itemName,
                'suppliers' => $supplierPrices,
                'best_price' => $bestPrice,
                'best_supplier_id' => $bestSupplierId,
                'offers_count' => $supplierPrices->count()
            ];
        })->filter()->values();

        $allSupplierIds = $itemsData->flatMap(function ($item) {
            return $item['suppliers']->keys();
        })->unique();

        $suppliers = [];
        if ($allSupplierIds->isNotEmpty()) {
            $suppliersData = AccHead::whereIn('id', $allSupplierIds)->get();
            foreach ($suppliersData as $supplier) {
                $suppliers[$supplier->id] = $supplier->aname;
            }
        }

        $items = $itemsData->map(function ($item) use ($suppliers) {
            $item['best_supplier_name'] = $suppliers[$item['best_supplier_id']] ?? 'مورد غير محدد';
            return $item;
        });

        return view('reports::inventory.prices-compare-report', compact('items', 'suppliers'));
    }

    public function inventoryDiscrepancyReport()
    {
        return view('reports::inventory.inventory-discrepancy-report');
    }

    // public function checkAllItemsQuantityLimits(): JsonResponse
    // {
    //     $items = Item::whereNotNull('min_order_quantity')
    //         ->orWhereNotNull('max_order_quantity')
    //         ->get();

    //     $notificationsSent = 0;

    //     foreach ($items as $item) {
    //         $currentQuantity = $this->calculateCurrentQuantity($item->id);
    //         $status = $this->getQuantityStatus($item);

    //         if ($status !== 'within_limits') {
    //             $notificationsSent++;
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => "تم فحص {$items->count()} صنف وتم إرسال {$notificationsSent} إشعار",
    //         'items_checked' => $items->count(),
    //         'notifications_sent' => $notificationsSent
    //     ]);
    // }

    public function checkItemQuantityAfterOperation(int $itemId): bool
    {
        $item = Item::find($itemId);

        if (!$item) {
            return false;
        }

        $currentQuantity = $this->calculateCurrentQuantity($item->id);
        $status = $this->getQuantityStatus($item);

        return $status !== 'within_limits';
    }

    // public function getItemsWithQuantityIssues(): JsonResponse
    // {
    //     $items = Item::whereNotNull('min_order_quantity')
    //         ->orWhereNotNull('max_order_quantity')
    //         ->get()
    //         ->map(function ($item) {
    //             $currentQuantity = $this->calculateCurrentQuantity($item->id);
    //             $status = $this->getQuantityStatus($item);

    //             return [
    //                 'id' => $item->id,
    //                 'name' => $item->name,
    //                 'code' => $item->code,
    //                 'current_quantity' => $currentQuantity,
    //                 'min_order_quantity' => $item->min_order_quantity,
    //                 'max_order_quantity' => $item->max_order_quantity,
    //                 'status' => $status,
    //                 'required_compensation' => $this->getRequiredCompensation($item),
    //                 'issue_type' => $status === 'below_min' ? 'منخفضة' : ($status === 'above_max' ? 'زائدة' : 'طبيعية')
    //             ];
    //         })
    //         ->filter(function ($item) {
    //             return $item['status'] !== 'within_limits';
    //         })
    //         ->values();

    //     return response()->json($items);
    // }

    // public function clearAllQuantityNotifications(): JsonResponse
    // {
    //     $items = Item::whereNotNull('min_order_quantity')
    //         ->orWhereNotNull('max_order_quantity')
    //         ->get();

    //     $clearedCount = 0;

    //     foreach ($items as $item) {
    //         $this->clearQuantityNotification($item->id, 'below_min');
    //         $this->clearQuantityNotification($item->id, 'above_max');
    //         $clearedCount += 2;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => "تم مسح {$clearedCount} إشعار من الكاش",
    //         'notifications_cleared' => $clearedCount
    //     ]);
    // }

    public function getItemNotificationStatus(int $itemId): JsonResponse
    {
        $item = Item::find($itemId);

        if (!$item) {
            return response()->json(['error' => 'الصنف غير موجود'], 404);
        }

        $currentQuantity = $this->calculateCurrentQuantity($item->id);
        $status = $this->getQuantityStatus($item);

        $notificationInfo = [
            'below_min_cache' => cache()->get("item_quantity_{$itemId}_below_min"),
            'above_max_cache' => cache()->get("item_quantity_{$itemId}_above_max"),
        ];

        return response()->json([
            'item_id' => $item->id,
            'item_name' => $item->name,
            'current_quantity' => $currentQuantity,
            'min_order_quantity' => $item->min_order_quantity,
            'max_order_quantity' => $item->max_order_quantity,
            'status' => $status,
            'notification_cache' => $notificationInfo
        ]);
    }
}



