<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

/**
 * خدمة تستخدم Stored Procedures لإعادة حساب average_cost
 * أسرع بكثير للبيانات الكبيرة جداً (ملايين الصفوف)
 */
class AverageCostRecalculationServiceStoredProcedure
{
    /**
     * إعادة حساب متوسط التكلفة لصنف معين باستخدام Stored Procedure
     */
    public function recalculateAverageCostForItem(int $itemId, ?string $fromDate = null): void
    {
        $item = Item::find($itemId);
        if (!$item) {
            return;
        }

        try {
            DB::statement('CALL sp_recalculate_average_cost(?, ?)', [
                $itemId,
                $fromDate
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * إعادة حساب متوسط التكلفة لعدة أصناف باستخدام Stored Procedure
     */
    public function recalculateAverageCostForItems(array $itemIds, ?string $fromDate = null): void
    {
        if (empty($itemIds)) {
            return;
        }

        // تحويل array إلى comma-separated string
        $itemIdsString = implode(',', $itemIds);

        try {
            DB::statement('CALL sp_recalculate_average_cost_batch(?, ?)', [
                $itemIdsString,
                $fromDate
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * إعادة حساب متوسط التكلفة بعد تعديل/حذف فاتورة
     */
    public function recalculateFromOperationWithItems(array $itemIds, string $fromDate, bool $isDelete = false): void
    {
        if (empty($itemIds)) {
            return;
        }

        // عند الحذف، نحسب من جميع الفواتير غير المحذوفة (نمرر null بدلاً من fromDate)
        if ($isDelete) {
            $fromDate = null;
        }

        $this->recalculateAverageCostForItems($itemIds, $fromDate);
    }

    /**
     * إعادة حساب متوسط التكلفة لجميع الأصناف (للاستخدام في الصيانة)
     * يستخدم pagination لتجنب memory issues
     */
    public function recalculateAllItems(?string $fromDate = null, int $chunkSize = 500): void
    {
        $totalItems = Item::count();
        $processed = 0;


        Item::chunk($chunkSize, function ($items) use ($fromDate, &$processed, $totalItems) {
            $itemIds = $items->pluck('id')->toArray();
            $this->recalculateAverageCostForItems($itemIds, $fromDate);
            $processed += count($itemIds);
        });
    }
}
