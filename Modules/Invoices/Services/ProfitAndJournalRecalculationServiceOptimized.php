<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use App\Models\Item;
use App\Models\OperHead;
use App\Models\OperationItems;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * نسخة محسّنة من ProfitAndJournalRecalculationService للأداء العالي
 */
class ProfitAndJournalRecalculationServiceOptimized
{
    /**
     * إعادة حساب الربح لفاتورة واحدة
     * محسّن باستخدام single query
     */
    public function recalculateProfitForOperation(int $operationId): void
    {
        $operation = OperHead::find($operationId);
        if (!$operation || !in_array($operation->pro_type, [10, 11, 12, 13, 19, 20, 59])) {
            return;
        }

        // ✅ Update item_cost for sales-like items and calculate total invoice cost
        $totalInvoiceCost = $this->updateOperationItemsProfit($operationId, $operation);

        // ✅ Profit Calculation (As requested: difference between total value and cost)
        $profit = ($operation->pro_value ?? 0) - $totalInvoiceCost;
        
        // Handle negative profit for sales return (Type 12)
        if ($operation->pro_type == 12) {
            $profit = -$profit;
        }

        // update operhead with final values
        DB::table('operhead')
            ->where('id', $operationId)
            ->update([
                'profit' => $totalProfit ?? $profit, // fallback just in case
                'fat_cost' => $totalInvoiceCost,
            ]);

        Log::info("Recalculated profit and fat_cost for operation {$operationId}: Cost={$totalInvoiceCost}, Profit={$profit}");
    }

    /**
     * تحديث الربح في operation_items باستخدام batch update
     */
    private function updateOperationItemsProfit(int $operationId, OperHead $operation): float
    {
        // ✅ Types that should have their item_cost updated to the new average_cost (Sales-like)
        $updateCostTypes = [10, 12, 19, 59];
        $shouldUpdateCost = in_array($operation->pro_type, $updateCostTypes);

        // حساب الربح لكل صنف باستخدام single query
        $items = DB::select("
            SELECT
                oi.id,
                oi.item_id,
                oi.detail_value,
                oi.qty_in,
                oi.qty_out,
                oi.cost_price as stored_cost,
                oi.currency_rate as line_currency_rate,
                COALESCE(i.average_cost, 0) as current_average_cost,
                ? as fat_disc,
                ? as fat_total
            FROM operation_items oi
            INNER JOIN items i ON oi.item_id = i.id
            WHERE oi.pro_id = ?
                AND oi.is_stock = 1
        ", [
            $operation->fat_disc ?? 0,
            $operation->fat_total ?? 0,
            $operationId
        ]);

        if (empty($items)) {
            return 0;
        }

        // إعداد batch update
        $casesProfit = [];
        $casesCost = [];
        $paramsProfit = [];
        $paramsCost = [];
        $ids = [];
        $totalInvoiceCost = 0;

        foreach ($items as $item) {
            $discountItem = 0;
            if ($item->fat_disc > 0 && $item->fat_total > 0) {
                $discountItem = ($item->fat_disc * $item->detail_value) / $item->fat_total;
            }

            $baseQty = abs($item->qty_out - $item->qty_in);
            
            // ✅ Determine which cost to use
            $itemCost = $shouldUpdateCost ? (float)$item->current_average_cost : (float)$item->stored_cost;
            $totalInvoiceCost += ($itemCost * $baseQty);

            // Item Profit = ((Net Value * Currency Rate) - Distributed Discount converted) - (Cost * Quantity)
            // Note: In our current SaveInvoiceService, pro_value in OperHead is already in base currency (price*rate).
            // But detail_value in operation_items is in foreign currency.
            $netValueInBase = ($item->detail_value - $discountItem) * (float)($item->line_currency_rate ?? 1);
            $profit = $netValueInBase - ($itemCost * $baseQty);

            $casesProfit[] = "WHEN ? THEN ?";
            $paramsProfit[] = $item->id;
            $paramsProfit[] = $profit;

            if ($shouldUpdateCost) {
                $casesCost[] = "WHEN ? THEN ?";
                $paramsCost[] = $item->id;
                $paramsCost[] = $itemCost;
            }

            $ids[] = $item->id;
        }

        if (!empty($casesProfit)) {
            $casesProfitSql = implode(' ', $casesProfit);
            $idsPlaceholder = implode(',', array_fill(0, count($ids), '?'));

            // Prepare update SQL
            $updateSql = "UPDATE operation_items SET profit = CASE id {$casesProfitSql} END";
            $updateParams = $paramsProfit;

            if (!empty($casesCost)) {
                $casesCostSql = implode(' ', $casesCost);
                $updateSql .= ", cost_price = CASE id {$casesCostSql} END";
                $updateParams = array_merge($updateParams, $paramsCost);
            }

            $updateSql .= " WHERE id IN ({$idsPlaceholder})";
            $updateParams = array_merge($updateParams, $ids);

            DB::update($updateSql, $updateParams);
        }

        return (float) $totalInvoiceCost;
    }

    /**
     * إعادة حساب الأرباح لجميع الفواتير المتأثرة
     * محسّن باستخدام chunking
     *
     * ملاحظة مهمة: عند إضافة فاتورة مشتريات بتاريخ قديم، يجب إعادة حساب فقط الفواتير
     * التي تاريخها بعد تاريخ الفاتورة المضافة (مع مراعاة الوقت في نفس اليوم)
     * لأن الفواتير التي قبلها لا تتأثر
     */
    public function recalculateProfitsForAffectedOperations(array $itemIds, string $fromDate, ?int $currentInvoiceId = null, ?string $currentInvoiceCreatedAt = null): void
    {
        if (empty($itemIds)) {
            return;
        }

        // استخدام chunking لتجنب memory issues
        $chunks = array_chunk($itemIds, 100);

        foreach ($chunks as $chunk) {
            // إعادة حساب فقط الفواتير التي تاريخها بعد fromDate
            // أو في نفس اليوم ولكن بعد وقت إنشاء الفاتورة الحالية
            $query = OperHead::whereHas('operationItems', function ($query) use ($chunk) {
                $query->whereIn('item_id', $chunk)
                    ->where('is_stock', 1);
            })
                ->whereIn('pro_type', [10, 12, 13, 19, 59])
                ->where('isdeleted', 0);

            // إعادة حساب فقط الفواتير التي بعد fromDate
            $query->where(function ($q) use ($fromDate, $currentInvoiceId, $currentInvoiceCreatedAt) {
                // الفواتير التي تاريخها بعد fromDate
                $q->where('pro_date', '>', $fromDate);

                // أو الفواتير في نفس اليوم ولكن بعد وقت إنشاء الفاتورة الحالية
                if ($currentInvoiceCreatedAt) {
                    $q->orWhere(function ($subQ) use ($fromDate, $currentInvoiceCreatedAt) {
                        $subQ->where('pro_date', '=', $fromDate)
                            ->where('created_at', '>', $currentInvoiceCreatedAt);
                    });
                } else {
                    // إذا لم يكن لدينا created_at، نستخدم فقط pro_date
                    $q->orWhere('pro_date', '=', $fromDate);
                }
            });

            // استثناء الفاتورة الحالية إذا كانت موجودة
            if ($currentInvoiceId) {
                $query->where('id', '!=', $currentInvoiceId);
            }

            $operations = $query->pluck('id');

            foreach ($operations as $operationId) {
                $this->recalculateProfitForOperation($operationId);
            }
        }

        Log::info("Recalculated profits for operations containing affected items after date: {$fromDate}");
    }

    /**
     * إعادة حساب جميع القيود المحاسبية لفاتورة واحدة
     * يحاول تعديل القيود الموجودة بدلاً من حذفها وإنشاء جديدة
     */
    public function recalculateJournalEntriesForOperation(int $operationId): void
    {
        $operation = OperHead::find($operationId);
        if (!$operation || !in_array($operation->pro_type, [10, 12, 13, 19])) {
            return;
        }

        DB::transaction(function () use ($operation) {
            // محاولة تعديل القيود الموجودة بدلاً من حذفها
            // البحث عن القيود باستخدام op_id أو op2 (لأن قيد COGS يستخدم op2)
            $existingJournals = JournalHead::where(function ($query) use ($operation) {
                $query->where('op_id', $operation->id)
                    ->orWhere('op2', $operation->id);
            })->get();

            if ($existingJournals->isEmpty()) {
                // لا توجد قيود موجودة، إنشاء جديدة
                $this->createJournalEntriesForOperation($operation);
                return;
            }

            // فصل القيود الرئيسية عن قيود COGS
            $mainJournals = [];
            $costJournals = [];

            foreach ($existingJournals as $journalHead) {
                $isCostJournal = $journalHead->details &&
                                str_contains($journalHead->details, 'تكلفة البضاعة');

                if ($isCostJournal) {
                    $costJournals[] = $journalHead;
                } elseif (in_array($journalHead->pro_type, [10, 12, 13, 19]) &&
                         $journalHead->op_id == $operation->id) {
                    // القيد الرئيسي: pro_type في [10, 12, 13, 19] و op_id مطابق
                    $mainJournals[] = $journalHead;
                }
                // تجاهل القيود الأخرى (مثل سندات الدفع/القبض)
            }

            // تحديث القيود الرئيسية (يجب أن يكون واحد فقط لكل نوع)
            // تجميع القيود حسب pro_type
            $mainJournalsByType = [];
            foreach ($mainJournals as $journalHead) {
                $type = $journalHead->pro_type;
                if (!isset($mainJournalsByType[$type])) {
                    $mainJournalsByType[$type] = [];
                }
                $mainJournalsByType[$type][] = $journalHead;
            }

            // تحديث أول قيد من كل نوع وحذف المكررات
            foreach ($mainJournalsByType as $type => $journals) {
                if (!empty($journals)) {
                    // تحديث أول قيد
                    $this->updateMainJournalEntry($journals[0], $operation);

                    // حذف القيود المكررة (إذا كان هناك أكثر من واحد)
                    if (count($journals) > 1) {
                        for ($i = 1; $i < count($journals); $i++) {
                            $extraJournal = $journals[$i];
                            JournalDetail::where('journal_id', $extraJournal->journal_id)->delete();
                            $extraJournal->delete();
                            Log::warning("Deleted duplicate main journal entry: journal_id={$extraJournal->journal_id}, op_id={$operation->id}, pro_type={$type}");
                        }
                    }
                }
            }

            // إذا لم يكن هناك قيد رئيسي، إنشاء واحد جديد
            if (empty($mainJournals)) {
                $this->createMainJournalEntryForOperation($operation);
            }

            // تحديث قيد COGS (يجب أن يكون واحد فقط)
            if (!empty($costJournals)) {
                // تحديث أول قيد COGS
                $this->updateCostOfGoodsJournal($costJournals[0], $operation);

                // حذف قيود COGS الإضافية (إذا كان هناك أكثر من واحد)
                if (count($costJournals) > 1) {
                    for ($i = 1; $i < count($costJournals); $i++) {
                        $extraJournal = $costJournals[$i];
                        JournalDetail::where('journal_id', $extraJournal->journal_id)->delete();
                        $extraJournal->delete();
                        Log::warning("Deleted duplicate COGS journal entry: journal_id={$extraJournal->journal_id}, op_id={$operation->id}");
                    }
                }
            } else {
                // لا يوجد قيد COGS، إنشاء واحد جديد (فقط للأنواع التي تحتاج COGS)
                if (in_array($operation->pro_type, [10, 12, 19])) {
                    $this->createCostOfGoodsJournal($operation);
                }
            }
        });
    }

    /**
     * تحديث قيد محاسبي موجود
     *
     * ملاحظة: هذه الدالة تستخدم فقط للقيود الرئيسية (المبيعات/المشتريات)
     * قيود COGS يتم التعامل معها بشكل منفصل في recalculateJournalEntriesForOperation
     */
    private function updateJournalEntry(JournalHead $journalHead, OperHead $operation): void
    {
        // تحديد نوع القيد
        // القيد الرئيسي: pro_type في [10, 12, 13, 19] و details لا يحتوي على "تكلفة البضاعة"
        // ملاحظة: details قد يكون فارغاً أو null، وهذا طبيعي للقيد الرئيسي
        $isCostJournal = $journalHead->details &&
                        str_contains($journalHead->details, 'تكلفة البضاعة');

        $isMainJournal = in_array($journalHead->pro_type, [10, 12, 13, 19]) &&
                        !$isCostJournal &&
                        $journalHead->op_id == $operation->id; // التأكد من أن القيد مرتبط بنفس العملية

        if ($isMainJournal) {
            // تحديث القيد الرئيسي (المبيعات/المشتريات)
            $this->updateMainJournalEntry($journalHead, $operation);
        } else {
            // قيد غير معروف أو غير مرتبط بالعملية، حذفه وإعادة إنشاء
            $journalIds = [$journalHead->journal_id];
            JournalDetail::whereIn('journal_id', $journalIds)->delete();
            JournalHead::whereIn('journal_id', $journalIds)->delete();
            $this->createJournalEntriesForOperation($operation);
        }
    }

    /**
     * تحديث القيد الرئيسي (المبيعات/المشتريات)
     */
    private function updateMainJournalEntry(JournalHead $journalHead, OperHead $operation): void
    {
        $totalAfterAdditional = $operation->pro_value ?? 0;
        $additionalValue = $operation->fat_plus ?? 0;
        $debit = $credit = null;

        switch ($operation->pro_type) {
            case 10:
                $debit = $operation->acc1;
                $credit = 47;
                break;
            case 12:
                $debit = 48;
                $credit = $operation->acc1;
                break;
            case 13:
                $debit = $operation->acc1;
                $credit = $operation->acc2;
                break;
            case 19:
                $debit = $operation->acc1;
                $credit = $operation->acc2;
                break;
        }

        if (!$debit || !$credit) {
            return;
        }

        // تحديث رأس القيد
        $journalHead->update([
            'total' => $totalAfterAdditional,
            'date' => $operation->pro_date,
        ]);

        // تحديث تفاصيل القيد
        $details = JournalDetail::where('journal_id', $journalHead->journal_id)->get();

        foreach ($details as $detail) {
            if ($detail->account_id == $debit) {
                $detail->update(['debit' => $totalAfterAdditional, 'credit' => 0]);
            } elseif ($detail->account_id == $credit) {
                $detail->update(['debit' => 0, 'credit' => $totalAfterAdditional - $additionalValue]);
            } elseif ($detail->account_id == 69 && $additionalValue > 0) {
                $detail->update(['debit' => 0, 'credit' => $additionalValue]);
            } elseif ($detail->account_id == 69 && $additionalValue == 0) {
                $detail->delete();
            }
        }

        // إضافة قيد الإضافات إذا لم يكن موجوداً
        if ($additionalValue > 0 && !$details->where('account_id', 69)->first()) {
            JournalDetail::create([
                'journal_id' => $journalHead->journal_id,
                'account_id' => 69,
                'debit' => 0,
                'credit' => $additionalValue,
                'type' => 1,
                'info' => 'إضافات - ' . ($operation->info ?? ''),
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ]);
        }
    }

    /**
     * تحديث قيد تكلفة البضاعة المباعة
     */
    private function updateCostOfGoodsJournal(JournalHead $journalHead, OperHead $operation): void
    {
        $profit = $operation->profit ?? 0;
        $totalAfterAdditional = $operation->pro_value ?? 0;
        $additionalValue = $operation->fat_plus ?? 0;
        $costAllSales = $operation->fat_cost ?? ($totalAfterAdditional - $profit - $additionalValue);

        if ($costAllSales <= 0) {
            // حذف القيد إذا كانت التكلفة صفر أو سالبة
            JournalDetail::where('journal_id', $journalHead->journal_id)->delete();
            $journalHead->delete();
            return;
        }

        // تحديث رأس القيد
        $journalHead->update([
            'total' => $costAllSales,
            'date' => $operation->pro_date,
        ]);

        // تحديث تفاصيل القيد
        $details = JournalDetail::where('journal_id', $journalHead->journal_id)->get();

        $hasCostAccount = false;
        $hasInventoryAccount = false;

        foreach ($details as $detail) {
            if ($detail->account_id == 16) {
                // حساب تكلفة البضاعة المباعة (مدين)
                $detail->update(['debit' => $costAllSales, 'credit' => 0]);
                $hasCostAccount = true;
            } elseif ($detail->account_id == $operation->acc2) {
                // حساب المخزن (دائن)
                $detail->update(['debit' => 0, 'credit' => $costAllSales]);
                $hasInventoryAccount = true;
            }
        }

        // إضافة الحسابات المفقودة إذا لم تكن موجودة
        if (!$hasCostAccount) {
            JournalDetail::create([
                'journal_id' => $journalHead->journal_id,
                'account_id' => 16,
                'debit' => $costAllSales,
                'credit' => 0,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ]);
        }

        if (!$hasInventoryAccount && $operation->acc2) {
            JournalDetail::create([
                'journal_id' => $journalHead->journal_id,
                'account_id' => $operation->acc2,
                'debit' => 0,
                'credit' => $costAllSales,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ]);
        }
    }

    /**
     * إنشاء القيد الرئيسي فقط (بدون COGS)
     */
    private function createMainJournalEntryForOperation(OperHead $operation): void
    {
        $journalId = JournalHead::max('journal_id') + 1;
        $debit = $credit = null;

        switch ($operation->pro_type) {
            case 10:
                $debit = $operation->acc1;
                $credit = 47;
                break;
            case 12:
                $debit = 48;
                $credit = $operation->acc1;
                break;
            case 13:
                $debit = $operation->acc1;
                $credit = $operation->acc2;
                break;
            case 19:
                $debit = $operation->acc1;
                $credit = $operation->acc2;
                break;
        }

        if (!$debit || !$credit) {
            return;
        }

        $totalAfterAdditional = $operation->pro_value ?? 0;
        $additionalValue = $operation->fat_plus ?? 0;

        // إنشاء رأس القيد
        JournalHead::create([
            'journal_id' => $journalId,
            'total' => $totalAfterAdditional,
            'op2' => $operation->id,
            'op_id' => $operation->id,
            'pro_type' => $operation->pro_type,
            'date' => $operation->pro_date,
            'details' => $operation->info ?? '',
            'user' => $operation->user ?? Auth::id(),
            'branch_id' => $operation->branch_id,
        ]);

        // إنشاء تفاصيل القيد
        $details = [
            [
                'journal_id' => $journalId,
                'account_id' => $debit,
                'debit' => $totalAfterAdditional,
                'credit' => 0,
                'type' => 1,
                'info' => $operation->info ?? '',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ],
            [
                'journal_id' => $journalId,
                'account_id' => $credit,
                'debit' => 0,
                'credit' => $totalAfterAdditional - $additionalValue,
                'type' => 1,
                'info' => $operation->info ?? '',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ],
        ];

        if ($additionalValue > 0) {
            $details[] = [
                'journal_id' => $journalId,
                'account_id' => 69,
                'debit' => 0,
                'credit' => $additionalValue,
                'type' => 1,
                'info' => 'إضافات - ' . ($operation->info ?? ''),
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ];
        }

        JournalDetail::insert($details);
    }

    /**
     * إنشاء القيود المحاسبية لفاتورة
     */
    private function createJournalEntriesForOperation(OperHead $operation): void
    {
        $journalId = JournalHead::max('journal_id') + 1;
        $debit = $credit = null;

        switch ($operation->pro_type) {
            case 10:
                $debit = $operation->acc1;
                $credit = 47;
                break;
            case 12:
                $debit = 48;
                $credit = $operation->acc1;
                break;
            case 13:
                $debit = $operation->acc1;
                $credit = $operation->acc2;
                break;
            case 19:
                $debit = $operation->acc1;
                $credit = $operation->acc2;
                break;
        }

        if (!$debit || !$credit) {
            return;
        }

        $totalAfterAdditional = $operation->pro_value ?? 0;
        $additionalValue = $operation->fat_plus ?? 0;

        // إنشاء رأس القيد
        JournalHead::create([
            'journal_id' => $journalId,
            'total' => $totalAfterAdditional,
            'op2' => $operation->id,
            'op_id' => $operation->id,
            'pro_type' => $operation->pro_type,
            'date' => $operation->pro_date,
            'details' => $operation->info ?? '',
            'user' => $operation->user ?? Auth::id(),
            'branch_id' => $operation->branch_id,
        ]);

        // إنشاء تفاصيل القيد باستخدام bulk insert
        $details = [
            [
                'journal_id' => $journalId,
                'account_id' => $debit,
                'debit' => $totalAfterAdditional,
                'credit' => 0,
                'type' => 1,
                'info' => $operation->info ?? '',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ],
            [
                'journal_id' => $journalId,
                'account_id' => $credit,
                'debit' => 0,
                'credit' => $totalAfterAdditional - $additionalValue,
                'type' => 1,
                'info' => $operation->info ?? '',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ],
        ];

        if ($additionalValue > 0) {
            $details[] = [
                'journal_id' => $journalId,
                'account_id' => 69,
                'debit' => 0,
                'credit' => $additionalValue,
                'type' => 1,
                'info' => 'إضافات - ' . ($operation->info ?? ''),
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ];
        }

        JournalDetail::insert($details);

        // قيد تكلفة البضاعة المباعة
        if (in_array($operation->pro_type, [10, 12, 19])) {
            $this->createCostOfGoodsJournal($operation);
        }
    }

    /**
     * إنشاء قيد تكلفة البضاعة المباعة
     */
    private function createCostOfGoodsJournal(OperHead $operation): void
    {
        $costJournalId = JournalHead::max('journal_id') + 1;
        $profit = $operation->profit ?? 0;
        $totalAfterAdditional = $operation->pro_value ?? 0;
        $additionalValue = $operation->fat_plus ?? 0;
        $costAllSales = $operation->fat_cost ?? ($totalAfterAdditional - $profit - $additionalValue);

        if ($costAllSales <= 0) {
            return;
        }

        JournalHead::create([
            'journal_id' => $costJournalId,
            'total' => $costAllSales,
            'op2' => $operation->id,
            'op_id' => $operation->id,
            'pro_type' => $operation->pro_type,
            'date' => $operation->pro_date,
            'details' => 'قيد تكلفة البضاعة - ' . ($operation->info ?? ''),
            'user' => $operation->user ?? Auth::id(),
            'branch_id' => $operation->branch_id,
        ]);

        JournalDetail::insert([
            [
                'journal_id' => $costJournalId,
                'account_id' => 16,
                'debit' => $costAllSales,
                'credit' => 0,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ],
            [
                'journal_id' => $costJournalId,
                'account_id' => $operation->acc2,
                'debit' => 0,
                'credit' => $costAllSales,
                'type' => 1,
                'info' => 'قيد تكلفة البضاعة',
                'op_id' => $operation->id,
                'isdeleted' => 0,
                'branch_id' => $operation->branch_id,
            ],
        ]);
    }

    /**
     * إعادة حساب كل شيء للفواتير المتأثرة
     *
     * ملاحظة مهمة: عند إضافة فاتورة مشتريات بتاريخ قديم، يجب إعادة حساب فقط الفواتير
     * التي تاريخها بعد تاريخ الفاتورة المضافة (مع مراعاة الوقت في نفس اليوم)
     * لأن الفواتير التي قبلها لا تتأثر
     *
     * @param array $itemIds الأصناف المتأثرة
     * @param string $fromDate تاريخ الفاتورة المضافة/المعدلة/المحذوفة
     * @param int|null $currentInvoiceId ID الفاتورة الحالية (للتأكد من عدم إعادة حسابها)
     * @param string|null $currentInvoiceCreatedAt وقت إنشاء الفاتورة الحالية (لمقارنة الفواتير في نفس اليوم)
     */
    public function recalculateAllAffectedOperations(array $itemIds, string $fromDate, ?int $currentInvoiceId = null, ?string $currentInvoiceCreatedAt = null): void
    {
        // إعادة حساب الأرباح (فقط للفواتير التي بعد fromDate)
        $this->recalculateProfitsForAffectedOperations($itemIds, $fromDate, $currentInvoiceId, $currentInvoiceCreatedAt);

        // إعادة حساب القيود المحاسبية (فقط للفواتير التي بعد fromDate)
        $chunks = array_chunk($itemIds, 100);

        foreach ($chunks as $chunk) {
            // إعادة حساب فقط الفواتير التي تاريخها بعد fromDate
            $query = OperHead::whereHas('operationItems', function ($query) use ($chunk) {
                $query->whereIn('item_id', $chunk)
                    ->where('is_stock', 1);
            })
                ->whereIn('pro_type', [10, 12, 13, 19])
                ->where('isdeleted', 0);

            // إعادة حساب فقط الفواتير التي بعد fromDate
            $query->where(function ($q) use ($fromDate, $currentInvoiceId, $currentInvoiceCreatedAt) {
                // الفواتير التي تاريخها بعد fromDate
                $q->where('pro_date', '>', $fromDate);

                // أو الفواتير في نفس اليوم ولكن بعد وقت إنشاء الفاتورة الحالية
                if ($currentInvoiceCreatedAt) {
                    $q->orWhere(function ($subQ) use ($fromDate, $currentInvoiceCreatedAt) {
                        $subQ->where('pro_date', '=', $fromDate)
                            ->where('created_at', '>', $currentInvoiceCreatedAt);
                    });
                } else {
                    // إذا لم يكن لدينا created_at، نستخدم فقط pro_date
                    $q->orWhere('pro_date', '=', $fromDate);
                }
            });

            // استثناء الفاتورة الحالية إذا كانت موجودة
            if ($currentInvoiceId) {
                $query->where('id', '!=', $currentInvoiceId);
            }

            $operations = $query->pluck('id');

            foreach ($operations as $operationId) {
                $this->recalculateJournalEntriesForOperation($operationId);
            }
        }

        Log::info("Recalculated all affected operations for items: " . count($itemIds) . " after date: {$fromDate}");
    }
}

