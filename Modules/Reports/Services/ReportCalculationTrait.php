<?php

declare(strict_types=1);

namespace Modules\Reports\Services;

use App\Models\Item;
use App\Models\OperationItems;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

trait ReportCalculationTrait
{
    /**
     * حساب رصيد الصنف من جميع المستودعات
     */
    protected function calculateItemBalance(int $itemId): float
    {
        return OperationItems::where('item_id', $itemId)
            ->where('isdeleted', 0)
            ->selectRaw('SUM(qty_in) - SUM(qty_out) as balance')
            ->value('balance') ?? 0.0;
    }

    /**
     * حساب رصيد الصنف من مستودع معين
     */
    protected function calculateItemBalanceByWarehouse(int $itemId, int $warehouseId): float
    {
        return OperationItems::where('item_id', $itemId)
            ->where('detail_store', $warehouseId)
            ->where('isdeleted', 0)
            ->selectRaw('SUM(qty_in) - SUM(qty_out) as balance')
            ->value('balance') ?? 0.0;
    }

    /**
     * حساب رصيد الحساب حتى تاريخ معين
     */
    protected function calculateAccountBalance(int $accountId, ?string $asOfDate = null): float
    {
        $query = JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0);

        if ($asOfDate) {
            // فلترة حسب تاريخ القيد أو تاريخ العملية
            $query->where(function ($q) use ($asOfDate) {
                $q->whereDate('crtime', '<=', $asOfDate)
                  ->orWhereHas('head.oper', function ($subQ) use ($asOfDate) {
                      $subQ->whereDate('pro_date', '<=', $asOfDate);
                  });
            });
        }

        $totalDebit = (clone $query)->sum('debit') ?? 0.0;
        $totalCredit = (clone $query)->sum('credit') ?? 0.0;

        return $totalDebit - $totalCredit;
    }

    /**
     * حساب الكمية الحالية للصنف (نفس calculateItemBalance)
     */
    protected function calculateCurrentQuantity(int $itemId): float
    {
        return $this->calculateItemBalance($itemId);
    }

    /**
     * تحديد حالة الكمية (أقل من الحد الأدنى، أعلى من الحد الأقصى، ضمن الحدود)
     */
    protected function getQuantityStatus(Item $item): string
    {
        $currentQuantity = $this->calculateCurrentQuantity($item->id);
        $minQuantity = $item->min_order_quantity ?? 0;
        $maxQuantity = $item->max_order_quantity ?? 999999;

        if ($currentQuantity < $minQuantity) {
            return 'below_min';
        }

        if ($currentQuantity > $maxQuantity) {
            return 'above_max';
        }

        return 'within_limits';
    }

    /**
     * حساب المطلوب تعويضه للوصول للحد الأدنى
     */
    protected function getRequiredCompensation(Item $item): float
    {
        $currentQuantity = $this->calculateCurrentQuantity($item->id);
        $minQuantity = $item->min_order_quantity ?? 0;
        $maxQuantity = $item->max_order_quantity ?? 999999;

        if ($currentQuantity < $minQuantity) {
            return $minQuantity - $currentQuantity;
        }

        if ($currentQuantity > $maxQuantity) {
            return $currentQuantity - $maxQuantity;
        }

        return 0.0;
    }

    /**
     * إرسال إشعار بخصوص الكمية (يتم حفظه في الكاش)
     */
    protected function sendQuantityNotification(int $itemId, string $type, string $message): void
    {
        $cacheKey = "item_quantity_{$itemId}_{$type}";
        Cache::put($cacheKey, $message, now()->addDays(7));
    }

    /**
     * مسح إشعار الكمية من الكاش
     */
    protected function clearQuantityNotification(int $itemId, string $type): void
    {
        $cacheKey = "item_quantity_{$itemId}_{$type}";
        Cache::forget($cacheKey);
    }

    /**
     * الحصول على الحد الأدنى للتغيير المطلوب لإرسال إشعار
     */
    protected function getMinChangeThreshold(Item $item): float
    {
        $minQuantity = $item->min_order_quantity ?? 0;
        return $minQuantity * 0.1; // 10% من الحد الأدنى
    }

    /**
     * حساب رصيد مركز التكلفة حتى تاريخ معين
     */
    protected function calculateCostCenterBalance(int $costCenterId, ?string $asOfDate = null): float
    {
        $query = JournalDetail::where('cost_center', $costCenterId)
            ->where('isdeleted', 0);

        if ($asOfDate) {
            $query->whereDate('crtime', '<=', $asOfDate);
        }

        $totalDebit = $query->sum('debit') ?? 0.0;
        $totalCredit = $query->sum('credit') ?? 0.0;

        return $totalDebit - $totalCredit;
    }

    /**
     * حساب مصروفات مركز التكلفة حتى تاريخ معين
     */
    protected function calculateCostCenterExpenses(int $costCenterId, ?string $asOfDate = null): float
    {
        $query = JournalDetail::where('cost_center', $costCenterId)
            ->where('isdeleted', 0);

        if ($asOfDate) {
            $query->whereDate('crtime', '<=', $asOfDate);
        }

        return $query->sum('debit') ?? 0.0;
    }

    /**
     * حساب إيرادات مركز التكلفة حتى تاريخ معين
     */
    protected function calculateCostCenterRevenues(int $costCenterId, ?string $asOfDate = null): float
    {
        $query = JournalDetail::where('cost_center', $costCenterId)
            ->where('isdeleted', 0);

        if ($asOfDate) {
            $query->whereDate('crtime', '<=', $asOfDate);
        }

        return $query->sum('credit') ?? 0.0;
    }
}
