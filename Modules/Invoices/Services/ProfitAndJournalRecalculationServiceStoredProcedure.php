<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use App\Models\OperHead;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * خدمة تستخدم Stored Procedures لإعادة حساب الأرباح
 * أسرع بكثير للبيانات الكبيرة
 */
class ProfitAndJournalRecalculationServiceStoredProcedure
{
    /**
     * إعادة حساب الربح لفاتورة واحدة باستخدام Stored Procedure
     */
    public function recalculateProfitForOperation(int $operationId): void
    {
        $operation = OperHead::find($operationId);
        if (!$operation || !in_array($operation->pro_type, [10, 12, 13, 19, 59])) {
            return;
        }

        try {
            DB::statement('CALL sp_recalculate_profit(?)', [$operationId]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * إعادة حساب الأرباح لجميع الفواتير المتأثرة باستخدام Stored Procedure
     */
    public function recalculateProfitsForAffectedOperations(array $itemIds, string $fromDate): void
    {
        if (empty($itemIds)) {
            return;
        }

        $itemIdsString = implode(',', $itemIds);

        try {
            DB::statement('CALL sp_recalculate_profits_batch(?, ?)', [
                $itemIdsString,
                $fromDate
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * إعادة حساب جميع القيود المحاسبية لفاتورة واحدة
     * ملاحظة: القيود المحاسبية معقدة، نستخدم PHP لها
     */
    public function recalculateJournalEntriesForOperation(int $operationId): void
    {
        $operation = OperHead::with('operationItems')->find($operationId);
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
                } elseif (
                    in_array($journalHead->pro_type, [10, 12, 13, 19]) &&
                    $journalHead->op_id == $operation->id
                ) {
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
        $isMainJournal = in_array($journalHead->pro_type, [10, 12, 13, 19]) &&
            $journalHead->details &&
            !str_contains($journalHead->details, 'تكلفة البضاعة');

        if ($isMainJournal) {
            // تحديث القيد الرئيسي (المبيعات/المشتريات)
            $this->updateMainJournalEntry($journalHead, $operation);
        } else {
            // قيد غير معروف، إعادة إنشاء
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
        $costAllSales = $totalAfterAdditional - $profit - $additionalValue;

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
        $costAllSales = $totalAfterAdditional - $profit - $additionalValue;

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
     * @param array $itemIds الأصناف المتأثرة
     * @param string $fromDate تاريخ الفاتورة المضافة/المعدلة/المحذوفة
     * @param int|null $currentInvoiceId ID الفاتورة الحالية (للتأكد من عدم إعادة حسابها)
     * @param string|null $currentInvoiceCreatedAt وقت إنشاء الفاتورة الحالية (لمقارنة الفواتير في نفس اليوم)
     */
    public function recalculateAllAffectedOperations(array $itemIds, string $fromDate, ?int $currentInvoiceId = null, ?string $currentInvoiceCreatedAt = null): void
    {
        if (empty($itemIds)) {
            return;
        }

        // استخدام Stored Procedure الشاملة
        $itemIdsString = implode(',', $itemIds);

        try {
            DB::statement('CALL sp_recalculate_all_after_operation(?, ?)', [
                $itemIdsString,
                $fromDate
            ]);

            // إعادة حساب القيود المحاسبية (نستخدم PHP لأنها معقدة)
            // فقط للفواتير التي بعد fromDate
            $chunks = array_chunk($itemIds, 100);

            foreach ($chunks as $chunk) {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
