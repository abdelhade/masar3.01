<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;

class InvoiceActionsController extends Controller
{
    public function addItem(Request $request): JsonResponse
    {
        if (session_id()) session_write_close();

        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'qty' => 'nullable|numeric|min:0.1',
        ]);

        // Fetch item with price details correctly
        $item = Item::with(['prices' => function($q) {
            $q->where('item_prices.price_id', 1); 
        }])->find($request->item_id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $price = $item->prices->first()?->pivot?->price ?? 0;
        $units = $item->units;

        try {
            $rowHtml = view('invoices::components.invoices.partials.item-row', [
                'item' => $item,
                'price' => $price,
                'qty' => $request->qty ?? 1,
                'units' => $units,
                'index' => time(), // Use timestamp as temporary index to avoid collision
            ])->render();
        } catch (\Exception $e) {
            $rowHtml = "<!-- Error: " . $e->getMessage() . " -->"; 
        }

        return response()->json([
            'success' => true,
            'html' => $rowHtml,
            'price' => $price,
            'item_id' => $item->id,
            'message' => 'تمت الإضافة بنجاح'
        ]);
    }

    // إنشاء صنف جديد سريع
    public function createItem(Request $request): JsonResponse
    {
        if (session_id()) session_write_close();

        $request->validate(['name' => 'required|string|min:2']);

        $id = DB::table('items')->insertGetId([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
            'isdeleted' => 0,
            // ... Add any other necessary default values based on project requirements
        ]);

        return response()->json([
            'success' => true,
            'item_id' => $id,
            'message' => 'تم إنشاء الصنف'
        ]);
    }
}
