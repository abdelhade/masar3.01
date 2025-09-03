<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\PublicSetting;
use App\Models\{Item, AccHead, JournalDetail, JournalHead, OperHead, OperationItems};

class InventoryStartBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض تسجيل الارصده الافتتاحيه للمخازن')->only(['index', 'show']);
        $this->middleware('can:إضافة تسجيل الارصده الافتتاحيه للمخازن')->only(['create', 'store']);
        $this->middleware('can:تعديل تسجيل الارصده الافتتاحيه للمخازن')->only(['edit', 'update']);
        $this->middleware('can:حذف تسجيل الارصده الافتتاحيه للمخازن')->only(['destroy']);
    }

    public function index()
    {
        return view('inventory-start-balance.index');
    }

    public function create(Request $request)
    {
        $stors = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1104%')
            ->select('id', 'aname')
            ->get();


        $storeId = $request->input('store_id', $stors->first()->id ?? null);

        $partners = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)

            ->where('code', 'like', '3101%')
            ->select('id', 'aname')
            ->get();

        $page = request()->get('page', 1);

        $itemList = Item::with('units')
            ->select('id', 'name')
            ->paginate(20)
            ->through(function ($item) use ($storeId) {
                $item->opening_balance = $this->calculateItemOpeningBalance($item->id, $storeId);
                return $item;
            });


        $periodStart = PublicSetting::where('key', 'start_date')->value('value') ?? now()->toDateString();

        return view('inventory-start-balance.create', get_defined_vars());
    }

    private function calculateItemOpeningBalance($itemId, $storeId = null, $partnerId = null)
    {
        $query = OperationItems::where('item_id', $itemId)
            ->where('pro_tybe', 60);

        if ($storeId) {
            $query->where('detail_store', $storeId);
        }

        return $query->sum('qty_in'); // <-- رجع المجموع بدل أول قيمة
    }

    public function updateOpeningBalance(Request $request)
    {
        $storeId = $request->input('store_id');

        $itemList = Item::with('units')
            ->get()
            ->map(function ($item) use ($storeId) {
                $openingBalance = $this->calculateItemOpeningBalance($item->id, $storeId);
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
            // 'periodStart' => 'required|date',
            'new_opening_balance' => 'required|array',
            'adjustment_qty' => 'required|array',
            'unit_ids' => 'required|array'
        ]);

        try {
            DB::beginTransaction();
            $storeId = $request->store_id;
            $partnerId = $request->partner_id;
            $periodStart =  PublicSetting::where('key', 'start_date')->value('value');
            $newOpeningBalances = $request->input('new_opening_balance');
            $adjustmentQties = $request->input('adjustment_qty');
            $unitIds = $request->input('unit_ids');

            $operHead = OperHead::updateOrCreate(
                ['pro_type' => 60, 'store_id' => $storeId],
                [
                    'pro_type' => 60,
                    'acc1' => $storeId,
                    'acc2' => $partnerId,
                    'pro_date' => $periodStart,
                    'store_id' => $storeId,
                    'pro_value' => 0,
                    'is_stock' => 1,
                    'is_journal' => 1,
                    'info' => 'رصيد افتتاحي للأصناف',
                ]
            );

            $oldTotalAmount = OperationItems::where('pro_tybe', 60)
                ->where('pro_id', $operHead->id)
                ->where('detail_store', $storeId)
                ->sum(DB::raw('qty_in * cost_price'));

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
                    if ($newBalance >= 0) {
                        OperationItems::updateOrCreate(
                            [
                                'pro_tybe' => 60,
                                'item_id' => $itemId,
                                'detail_store' => $storeId,
                                'pro_id' => $operHead->id,
                            ],
                            [
                                'pro_tybe' => 60,
                                'detail_store' => $storeId,
                                'pro_id' => $operHead->id,
                                'item_id' => $itemId,
                                'unit_id' => $unitId,
                                'unit_value' => 1.000,
                                'qty_in' => $newBalance,
                                'qty_out' => 0,
                                'fat_quantity' => null,
                                'fat_price' => null,
                                'item_price' => $unitCost,
                                'cost_price' => $unitCost,
                                'current_stock_value' => $newBalance * $unitCost,
                                'additional' => 0,
                                'detail_value' => $newBalance * $unitCost,
                            ]
                        );
                    }

                    $totalAmount += ($newBalance * $unitCost);
                    $processedItems++;
                }
            }
            $operHead->update(['pro_value' => $totalAmount]);
            $existingJournal = JournalHead::where('pro_type', 60)
                ->where('op_id', $operHead->id)
                ->first();

            if ($existingJournal) {
                $journalId = $existingJournal->journal_id;
            } else {
                $journalId = JournalHead::max('journal_id') + 1;
            }

            if ($newBalance == 0) {
                JournalDetail::where('op_id', $operHead->id)->delete();
                JournalHead::where('op_id', $operHead->id)->where('pro_type', 60)->delete();
            } else {
                JournalHead::updateOrCreate(
                    ['journal_id' => $journalId, 'pro_type' => 60],
                    [
                        'journal_id' => $journalId,
                        'total' => $totalAmount,
                        'date' => $periodStart,
                        'op_id' => $operHead->id,
                        'pro_type' => 60,
                        'op2' => $operHead->id,
                        'user' => Auth::id(),
                    ]
                );
                // مدين
                JournalDetail::updateOrCreate(
                    ['journal_id' => $journalId, 'credit' => 0,],
                    [
                        'journal_id' => $journalId,
                        'account_id' => $storeId,
                        'debit' => $totalAmount,
                        'credit' => 0,
                        'type' => 1,
                        'op_id' => $operHead->id,
                    ]
                );
                // دائن
                JournalDetail::updateOrCreate(
                    [
                        'journal_id' => $journalId,
                        'debit' => 0,
                    ],
                    [
                        'journal_id' => $journalId,
                        'account_id' => $partnerId,
                        'debit' => 0,
                        'credit' => $totalAmount,
                        'type' => 1,
                        'op_id' => $operHead->id,
                    ]
                );
            }
            $partner = AccHead::find($partnerId);
            $store = AccHead::find($storeId);

            $oldStartBalance = $partner->start_balance;

            $store->update(['start_balance' => $totalAmount]);

            if ($oldStartBalance == 0) {
                $partner->update(['start_balance' => -$totalAmount]);
            } elseif ($oldStartBalance < 0) {
                $newBalance = $oldStartBalance + $oldTotalAmount - $totalAmount;
                $partner->update(['start_balance' => $newBalance]);
            } else {
                $newBalance = $oldTotalAmount - $totalAmount - $oldStartBalance;
                $partner->update(['start_balance' => $newBalance]);
            }

            DB::commit();
            return redirect()->route('inventory-start-balance.create')
                ->with('success', "تم حفظ الرصيد الافتتاحي بنجاح. تم معالجة {$processedItems} صنف بإجمالي قيمة " . number_format($totalAmount, 2));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'حدث خطأ في حفظ البيانات: ')->withInput();
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
