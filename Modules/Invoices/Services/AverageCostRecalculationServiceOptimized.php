<?php

declare(strict_types=1);

namespace Modules\Invoices\Services;

use App\Models\Item;
use RuntimeException;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use App\Services\Monitoring\RecalculationPerformanceMonitor;
use Modules\Invoices\Services\Validation\RecalculationInputValidator;

/**
 * Optimized average cost recalculation service for high performance.
 *
 * This service uses SQL aggregations and batch updates instead of PHP loops
 * for significantly better performance. It's designed for small to medium
 * datasets (<1000 items) and uses the following optimizations:
 * - Single SQL query per item using aggregations
 * - Batch processing for multiple items (100 items per batch)
 * - Direct database updates without loading models
 * - Comprehensive error handling and logging
 * - Performance monitoring integration
 *
 * Formula: average_cost = SUM(detail_value) / SUM(qty_in - qty_out)
 *
 * @example Basic usage:
 * $monitor = new RecalculationPerformanceMonitor();
 * $service = new AverageCostRecalculationServiceOptimized($monitor);
 * $service->recalculateAverageCostForItem(123, '2024-01-01');
 * @example Batch processing:
 * $service->recalculateAverageCostForItems([1, 2, 3], '2024-01-01');
 * @example Delete scenario (recalculate from all operations):
 * $service->recalculateAverageCostForItem(123, null, true);
 */
class AverageCostRecalculationServiceOptimized
{
    /**
     * Performance monitor for tracking operation metrics.
     */
    private RecalculationPerformanceMonitor $monitor;

    /**
     * Create a new service instance.
     *
     * @param  RecalculationPerformanceMonitor  $monitor  Performance monitor instance
     */
    public function __construct(RecalculationPerformanceMonitor $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Recalculate average cost for a single item.
     *
     * Uses SQL aggregation to calculate average cost from all stock operations.
     * When triggered by delete, recalculates from ALL non-deleted operations
     * (ignores fromDate) because deletion affects all subsequent operations.
     *
     * Formula: average_cost = SUM(detail_value) / SUM(qty_in - qty_out)
     *
     * Filters:
     * - is_stock = 1 (only stock operations)
     * - pro_type IN (11, 12, 20, 59) (purchase, sales, opening, manufacturing)
     * - isdeleted = 0 (only non-deleted operations)
     * - pro_date >= fromDate (when not delete operation)
     *
     * @param  int  $itemId  Item ID to recalculate
     * @param  string|null  $fromDate  Start date (Y-m-d format), null for all operations
     * @param  bool  $isDelete  True if triggered by delete operation (ignores fromDate)
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     *
     * @example
     * // Recalculate from specific date
     * $service->recalculateAverageCostForItem(123, '2024-01-01');
     * @example
     * // Recalculate from all operations (delete scenario)
     * $service->recalculateAverageCostForItem(123, null, true);
     */
    public function recalculateAverageCostForItem(int $itemId, ?string $fromDate = null, bool $isDelete = false): void
    {
        $operationId = $this->monitor->start('single_recalculation', [
            'item_id' => $itemId,
            'from_date' => $fromDate,
            'is_delete' => $isDelete,
        ]);

        try {
            // Validate inputs
            RecalculationInputValidator::validateItemIds([$itemId]);
            RecalculationInputValidator::validateDate($fromDate);
            $isDelete = RecalculationInputValidator::validateBoolean($isDelete);
        } catch (InvalidArgumentException $e) {
            $this->monitor->end($operationId, ['success' => false, 'error' => 'validation_failed']);
            throw $e;
        }

        try {
            $item = Item::find($itemId);
            if (! $item) {
                $this->monitor->end($operationId, ['success' => true, 'items_processed' => 0, 'reason' => 'item_not_found']);
                return;
            }

            // عند الحذف، نحسب من جميع الفواتير غير المحذوفة (لا من fromDate فقط)
            // لأن الحذف يؤثر على كل الفواتير التالية
            if ($isDelete) {
                $fromDate = null;
            }

            // استخدام raw SQL للحصول على النتيجة النهائية مباشرة
            $sql = '
                SELECT
                    SUM(oi.qty_in - oi.qty_out) as total_qty,
                    SUM(oi.detail_value) as total_value
                FROM operation_items oi
                INNER JOIN operhead oh ON oi.pro_id = oh.id
                WHERE oi.item_id = ?
                    AND oi.is_stock = 1
                    AND oh.pro_type IN (11, 12, 20, 59)
                    AND oh.isdeleted = 0
            ';

            $params = [$itemId];

            // عند التعديل فقط، نحسب من fromDate
            // عند الحذف، نحسب من جميع الفواتير (fromDate = null)
            if ($fromDate && ! $isDelete) {
                $sql .= ' AND oh.pro_date >= ?';
                $params[] = $fromDate;
            }

            $result = DB::selectOne($sql, $params);

            $totalQty = (float) ($result->total_qty ?? 0);
            $totalValue = (float) ($result->total_value ?? 0);

            // تحديث واحد فقط للصنف
            $newAverage = $totalQty > 0 ? ($totalValue / $totalQty) : 0;

            DB::table('items')
                ->where('id', $itemId)
                ->update(['average_cost' => $newAverage]);


            $this->monitor->end($operationId, [
                'success' => true,
                'items_processed' => 1,
                'new_average_cost' => $newAverage,
                'total_qty' => $totalQty,
                'total_value' => $totalValue,
            ]);
        } catch (\Exception $e) {
            $this->monitor->end($operationId, ['success' => false, 'error' => 'exception', 'error_message' => $e->getMessage()]);
            throw new RuntimeException("Failed to recalculate average cost for item {$itemId}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Recalculate average cost for multiple items in batches.
     *
     * Processes items in batches of 100 to reduce database load.
     * Uses the same calculation logic as single item recalculation
     * but optimized for batch processing.
     *
     * @param  array  $itemIds  Array of item IDs to recalculate
     * @param  string|null  $fromDate  Start date (Y-m-d format), null for all operations
     * @param  bool  $isDelete  True if triggered by delete operation (ignores fromDate)
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     *
     * @example
     * $service->recalculateAverageCostForItems([1, 2, 3, 4, 5], '2024-01-01');
     */
    public function recalculateAverageCostForItems(array $itemIds, ?string $fromDate = null, bool $isDelete = false): void
    {
        if (empty($itemIds)) {
            return;
        }

        $operationId = $this->monitor->start('batch_recalculation', [
            'item_count' => count($itemIds),
            'from_date' => $fromDate,
            'is_delete' => $isDelete,
        ]);

        try {
            // Validate inputs
            RecalculationInputValidator::validateItemIds($itemIds);
            RecalculationInputValidator::validateDate($fromDate);
            $isDelete = RecalculationInputValidator::validateBoolean($isDelete);
        } catch (InvalidArgumentException $e) {
            $this->monitor->end($operationId, ['success' => false, 'error' => 'validation_failed']);
            throw $e;
        }

        try {
            // عند الحذف، نحسب من جميع الفواتير غير المحذوفة
            if ($isDelete) {
                $fromDate = null;
            }

            // معالجة الأصناف في batches لتقليل الضغط على قاعدة البيانات
            $chunks = array_chunk($itemIds, 100); // معالجة 100 صنف في كل مرة

            foreach ($chunks as $chunk) {
                $this->recalculateBatch($chunk, $fromDate, $isDelete);
            }

            $this->monitor->end($operationId, [
                'success' => true,
                'items_processed' => count($itemIds),
                'batch_count' => count($chunks),
            ]);
        } catch (\Exception $e) {
            $this->monitor->end($operationId, ['success' => false, 'error' => 'exception', 'error_message' => $e->getMessage()]);
            throw new RuntimeException('Failed to recalculate average cost for items: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * إعادة حساب batch من الأصناف باستخدام single query
     */
    private function recalculateBatch(array $itemIds, ?string $fromDate = null, bool $isDelete = false): void
    {
        // عند الحذف، نحسب من جميع الفواتير غير المحذوفة
        if ($isDelete) {
            $fromDate = null;
        }

        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));

        $sql = "
            SELECT
                oi.item_id,
                SUM(oi.qty_in - oi.qty_out) as total_qty,
                SUM(oi.detail_value) as total_value
            FROM operation_items oi
            INNER JOIN operhead oh ON oi.pro_id = oh.id
            WHERE oi.item_id IN ({$placeholders})
                AND oi.is_stock = 1
                AND oh.pro_type IN (11, 12, 20, 59)
                AND oh.isdeleted = 0
        ";

        $params = $itemIds;

        // عند التعديل فقط، نحسب من fromDate
        if ($fromDate && ! $isDelete) {
            $sql .= ' AND oh.pro_date >= ?';
            $params[] = $fromDate;
        }

        $sql .= ' GROUP BY oi.item_id';

        $results = DB::select($sql, $params);

        // إعداد بيانات للـ batch update
        $updates = [];
        foreach ($results as $result) {
            $itemId = (int) $result->item_id;
            $totalQty = (float) $result->total_qty;
            $totalValue = (float) $result->total_value;
            $newAverage = $totalQty > 0 ? ($totalValue / $totalQty) : 0;

            $updates[] = [
                'id' => $itemId,
                'average_cost' => $newAverage,
            ];
        }

        // معالجة الأصناف التي لم تظهر في النتائج (لا توجد فواتير لها)
        $processedIds = array_column($updates, 'id');
        $missingIds = array_diff($itemIds, $processedIds);

        foreach ($missingIds as $itemId) {
            $updates[] = [
                'id' => $itemId,
                'average_cost' => 0,
            ];
        }

        // Batch update باستخدام CASE statement (أسرع من multiple updates)
        if (! empty($updates)) {
            $this->batchUpdateAverageCost($updates);
        }
    }

    /**
     * Batch update للأصناف باستخدام CASE statement
     * أسرع بكثير من multiple UPDATE statements
     */
    private function batchUpdateAverageCost(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        // تقسيم إلى batches صغيرة لتجنب query كبيرة جداً
        $chunks = array_chunk($updates, 50);

        foreach ($chunks as $chunk) {
            $ids = [];
            $cases = [];
            $params = [];

            foreach ($chunk as $update) {
                $ids[] = $update['id'];
                $cases[] = 'WHEN ? THEN ?';
                $params[] = $update['id'];
                $params[] = $update['average_cost'];
            }

            $idsPlaceholder = implode(',', array_fill(0, count($ids), '?'));
            $casesSql = implode(' ', $cases);

            $sql = "
                UPDATE items
                SET average_cost = CASE id
                    {$casesSql}
                END
                WHERE id IN ({$idsPlaceholder})
            ";

            $params = array_merge($params, $ids);

            DB::update($sql, $params);
        }
    }

    /**
     * إعادة حساب متوسط التكلفة بعد تعديل/حذف فاتورة
     * نسخة محسّنة تستخدم batch processing
     *
     * @param  array  $itemIds  Array of item IDs affected
     * @param  string  $fromDate  Operation date (Y-m-d format)
     * @param  bool  $isDelete  True if operation was deleted
     *
     * @throws InvalidArgumentException if parameters are invalid
     * @throws RuntimeException if recalculation fails
     */
    public function recalculateFromOperationWithItems(array $itemIds, string $fromDate, bool $isDelete = false): void
    {
        if (empty($itemIds)) {
            return;
        }

        $operationId = $this->monitor->start('operation_recalculation', [
            'item_count' => count($itemIds),
            'from_date' => $fromDate,
            'is_delete' => $isDelete,
        ]);

        try {
            // Validate inputs
            RecalculationInputValidator::validateItemIds($itemIds);
            RecalculationInputValidator::validateDate($fromDate);
            $isDelete = RecalculationInputValidator::validateBoolean($isDelete);
        } catch (InvalidArgumentException $e) {
            $this->monitor->end($operationId, ['success' => false, 'error' => 'validation_failed']);
            throw $e;
        }

        try {
            // استخدام batch processing
            $this->recalculateAverageCostForItems($itemIds, $fromDate, $isDelete);


            $this->monitor->end($operationId, [
                'success' => true,
                'items_processed' => count($itemIds),
            ]);
        } catch (\Exception $e) {
            $this->monitor->end($operationId, ['success' => false, 'error' => 'exception', 'error_message' => $e->getMessage()]);
            throw new RuntimeException('Failed to recalculate from operation: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * إعادة حساب متوسط التكلفة لجميع الأصناف (للاستخدام في الصيانة)
     * يستخدم pagination لتجنب memory issues
     *
     * @param  string|null  $fromDate  Start date (Y-m-d format), null for all operations
     * @param  int  $chunkSize  Number of items to process per batch
     *
     * @throws RuntimeException if recalculation fails
     */
    public function recalculateAllItems(?string $fromDate = null, int $chunkSize = 500): void
    {
        $operationId = $this->monitor->start('full_recalculation', [
            'from_date' => $fromDate,
            'chunk_size' => $chunkSize,
        ]);

        try {
            // Validate inputs
            RecalculationInputValidator::validateDate($fromDate);
        } catch (InvalidArgumentException $e) {

            $this->monitor->end($operationId, ['success' => false, 'error' => 'validation_failed']);
            throw $e;
        }

        try {
            $totalItems = Item::count();
            $processed = 0;

            Item::chunk($chunkSize, function ($items) use ($fromDate, &$processed, $totalItems) {
                $itemIds = $items->pluck('id')->toArray();
                $this->recalculateAverageCostForItems($itemIds, $fromDate);
                $processed += count($itemIds);
            });


            $this->monitor->end($operationId, [
                'success' => true,
                'items_processed' => $totalItems,
                'total_items' => $totalItems,
            ]);
        } catch (\Exception $e) {

            $this->monitor->end($operationId, ['success' => false, 'error' => 'exception', 'error_message' => $e->getMessage()]);
            throw new RuntimeException('Failed to recalculate all items: ' . $e->getMessage(), 0, $e);
        }
    }
}
