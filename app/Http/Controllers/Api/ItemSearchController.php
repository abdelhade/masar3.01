<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Enums\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSearchController extends Controller
{
    public function search(Request $request)
    {
        $searchTerm = trim($request->get('term', ''));
        $type = $request->get('type');
        $branchId = $request->get('branch_id');
        $selectedPriceId = $request->get('price_type', 1); // ده الـ price_id مش price_type_id

        if (empty($searchTerm)) {
            return response()->json([]);
        }

        // ✅ البحث البسيط
        $query = Item::select('items.id', 'items.name', 'items.code')
            ->where(function ($q) use ($searchTerm) {
                $q->where('items.name', 'like', $searchTerm . '%')
                    ->orWhere('items.code', 'like', $searchTerm . '%');
            });

        // تطبيق الفلاتر
        if (in_array($type, [11, 13, 15, 17])) {
            $query->where('items.type', ItemType::Inventory->value);
        } elseif ($type == 24) {
            $query->where('items.type', ItemType::Service->value);
        }

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('items.branch_id', $branchId)
                    ->orWhereNull('items.branch_id');
            });
        }

        $items = $query->limit(10)->get();

        // البحث في الباركود إذا لم توجد نتائج
        if ($items->isEmpty()) {
            $barcodeQuery = Item::select('items.id', 'items.name', 'items.code')
                ->join('barcodes', 'items.id', '=', 'barcodes.item_id')
                ->where('barcodes.barcode', 'like', $searchTerm . '%');

            if (in_array($type, [11, 13, 15, 17])) {
                $barcodeQuery->where('items.type', ItemType::Inventory->value);
            } elseif ($type == 24) {
                $barcodeQuery->where('items.type', ItemType::Service->value);
            }

            if ($branchId) {
                $barcodeQuery->where(function ($q) use ($branchId) {
                    $q->where('items.branch_id', $branchId)
                        ->orWhereNull('items.branch_id');
                });
            }

            $items = $barcodeQuery->limit(10)->distinct()->get();
        }

        // ✅ جلب البيانات الإضافية لكل item
        $formatted = $items->map(function ($item) use ($selectedPriceId) {
            // جلب الوحدات
            $units = DB::table('item_units')
                ->join('units', 'item_units.unit_id', '=', 'units.id')
                ->where('item_units.item_id', $item->id)
                ->select('units.id', 'units.name', 'item_units.u_val as uval')
                ->orderBy('item_units.u_val', 'asc')
                ->get();

            $firstUnit = $units->first();

            // ✅ جلب السعر من item_prices
            $priceData = DB::table('item_prices')
                ->where('item_id', $item->id)
                ->where('price_id', $selectedPriceId) // ✅ استخدم price_id
                ->where('unit_id', $firstUnit?->id ?? 1)
                ->first();

            $price = $priceData->price ?? 0;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'unit_id' => $firstUnit?->id,
                'unit_name' => $firstUnit?->name,
                'price' => $price,
                'units' => $units->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'name' => $unit->name,
                        'uval' => $unit->uval ?? 1
                    ];
                })->toArray()
            ];
        });

        return response()->json($formatted);
    }
}
