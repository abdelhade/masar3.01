<?php

namespace App\Http\Controllers;

use App\Models\{Item, AccHead, OperHead, OperationItems};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Settings\Models\PublicSetting;

class InventoryStartBalanceController extends Controller
{
    public function index()
    {
        return view('inventory-start-balance.index');
    }

    public function create()
    {
        $periodStart = cache()->remember('period_start', 60 * 60, function () {
            return PublicSetting::where('key', 'start_date')->value('value');
        });

        $stors = cache()->remember('stors', 60 * 60, function () {
            return AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '123%')
                ->select('id', 'aname')
                ->get();
        });

        $partners = cache()->remember('partners', 60 * 60, function () {
            return AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '2211%')
                ->select('id', 'aname')
                ->get();
        });
        $itemList = Item::with('units')
            ->get()
            ->map(function ($item) {
                $openingBalance = $this->calculateItemOpeningBalance($item->id);
                $item->opening_balance = $openingBalance;

                return $item;
            });

        return view('inventory-start-balance.create', get_defined_vars());
    }

    private function calculateItemOpeningBalance($itemId, $storeId = null, $partnerId = null)
    {
        $query = OperationItems::where('item_id', $itemId);
        if ($storeId) {
            $query->where('detail_store', $storeId);
        }
        if ($partnerId) {
            // تحتاج لتحديد كيفية ربط الشريك بالعمليات
            // $query->where('partner_id', $partnerId);
        }

        $totalIn = $query->sum('qty_in');
        $totalOut = $query->sum('qty_out');

        return $totalIn - $totalOut;
    }

    public function updateOpeningBalance(Request $request)
    {
        $storeId = $request->input('store_id');
        $partnerId = $request->input('partner_id');
        $periodStart = $request->input('periodStart');
        $itemList = Item::with('units')
            ->get()
            ->map(function ($item) use ($storeId, $partnerId) {
                $openingBalance = $this->calculateItemOpeningBalance($item->id, $storeId, $partnerId);
                $item->opening_balance = $openingBalance;

                return $item;
            });
        return response()->json([
            'success' => true,
            'itemList' => $itemList
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'nullable|exists:acc_head,id',
            'partner_id' => 'nullable|exists:acc_head,id',
            'periodStart' => 'required|date',
            'new_opening_balance' => 'required|array',
            'adjustment_qty' => 'required|array',
            'unit_ids' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            $storeId = $request->store_id;
            $partnerId = $request->partner_id;
            $periodStart = $request->periodStart;
            $newOpeningBalances = $request->input('new_opening_balance');
            $adjustmentQties = $request->input('adjustment_qty');
            $unitIds = $request->input('unit_ids');

            $operHead = OperHead::create([
                'date' => $periodStart,
                'doc_no' => 'OPENING-' . date('YmdHis'),
                'doc_type' => 'opening_balance',
                'store_id' => $storeId,
                'partner_id' => $partnerId,
                'total_amount' => 0,
                'notes' => 'رصيد افتتاحي للأصناف',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $totalAmount = 0;
            $processedItems = 0;

            foreach ($newOpeningBalances as $itemId => $newBalance) {
                $adjustmentQty = $adjustmentQties[$itemId] ?? 0;
                $unitId = $unitIds[$itemId] ?? null;

                $newBalance = (float) $newBalance;
                $adjustmentQty = (float) $adjustmentQty;

                if ($adjustmentQty != 0 && $unitId) {
                    $item = Item::find($itemId);
                    $unitCost = 0;

                    if ($item) {
                        $unit = $item->units()->where('unit_id', $unitId)->first();
                        $unitCost = $unit ? $unit->pivot->cost : 0;
                    }
                    OperationItems::create([
                        'pro_tybe' => 1,
                        'detail_store' => $storeId,
                        'pro_id' => $operHead->id,
                        'item_id' => $itemId,
                        'unit_id' => $unitId,
                        'unit_value' => 1.000,
                        'qty_in' => $adjustmentQty > 0 ? $adjustmentQty : 0,
                        'qty_out' => $adjustmentQty < 0 ? abs($adjustmentQty) : 0,
                        'fat_quantity' => null,
                        'fat_price' => null,
                        'item_price' => $unitCost,
                        'cost_price' => $unitCost,
                        'current_stock_value' => $adjustmentQty * $unitCost,
                        'item_discount' => null,
                        'additional' => null,
                        'detail_value' => $adjustmentQty * $unitCost,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $totalAmount += ($adjustmentQty * $unitCost);
                    $processedItems++;
                }
            }
            $operHead->update(['total_amount' => $totalAmount]);
            PublicSetting::updateOrCreate(
                ['key' => 'start_date'],
                ['value' => $periodStart]
            );

            DB::commit();
            return redirect()
                ->route('inventory-start-balance.create')
                ->with('success', "تم حفظ الرصيد الافتتاحي بنجاح. تم معالجة {$processedItems} صنف بإجمالي قيمة " . number_format($totalAmount, 2));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ في حفظ البيانات: ')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
