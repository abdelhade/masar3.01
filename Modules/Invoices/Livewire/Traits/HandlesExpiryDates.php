<?php

namespace Modules\Invoices\Livewire\Traits;

use App\Models\OperationItems;
use Carbon\Carbon;

trait HandlesExpiryDates
{
    public $expiryDateMode = 'disabled'; // Options: 'disabled', 'nearest_first', 'show_all'
    public $availableBatches = []; // لتخزين الدفعات المتاحة لكل صنف

    protected function loadExpirySettings()
    {
        // ✅ قراءة الإعدادات الثلاثة
        $disabled = setting('expiry_mode_disabled', '0') == '1';
        $nearestFirst = setting('expiry_mode_nearest_first', '1') == '1';  // افتراضياً مُفعَّل
        $showAll = setting('expiry_mode_show_all', '0') == '1';

        // ✅ تحديد الوضع حسب الأولوية
        if ($disabled) {
            // لو مُعطَّل: مش بيشتغل أصلاً
            $this->expiryDateMode = 'disabled';
        } elseif ($showAll) {
            // لو "اختيار يدوي" مُفعَّل: له الأولوية
            $this->expiryDateMode = 'show_all';
        } elseif ($nearestFirst) {
            // لو "تلقائي FIFO" مُفعَّل
            $this->expiryDateMode = 'nearest_first';
        } else {
            // لو كلهم معطلين: يبقى معطل
            $this->expiryDateMode = 'disabled';
        }
    }
    /**
     * جلب جميع الدفعات المتاحة لصنف معين مع تواريخ الصلاحية
     * مع الترتيب حسب FIFO (الأقرب في الصلاحية أولاً)
     */
    public function getItemBatches($itemId, $storeId)
    {
        if ($this->expiryDateMode === 'disabled') {
            return collect();
        }

        return OperationItems::where('item_id', $itemId)
            ->where('detail_store', $storeId)
            ->where('is_stock', 1) // فقط حركات المخزون
            ->whereNotNull('batch_number')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now()) // فقط الصلاحيات السارية
            ->selectRaw('
                batch_number,
                expiry_date,
                SUM(qty_in - qty_out) as available_quantity
            ')
            ->groupBy('batch_number', 'expiry_date')
            ->having('available_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc') // ترتيب FIFO
            ->get()
            ->map(function ($batch) {
                return [
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date,
                    'available_quantity' => (float) $batch->available_quantity,
                    'expiry_date_formatted' => Carbon::parse($batch->expiry_date)->format('Y-m-d'),
                    'display_text' => "دفعة: {$batch->batch_number} | صلاحية: " .
                        Carbon::parse($batch->expiry_date)->format('Y-m-d') .
                        " | متوفر: " . number_format($batch->available_quantity, 2)
                ];
            });
    }

    /**
     * تعيين تاريخ الصلاحية ورقم الدفعة تلقائياً عند إضافة صنف
     */
    public function autoAssignExpiryData($itemId, $storeId, $index)
    {
        if ($this->expiryDateMode === 'disabled') {
            return;
        }

        // جلب الدفعات المتاحة
        $batches = $this->getItemBatches($itemId, $storeId);

        if ($batches->isEmpty()) {
            // لا توجد دفعات متاحة - السماح بإضافة الصنف بدون صلاحية
            $this->invoiceItems[$index]['batch_number'] = null;
            $this->invoiceItems[$index]['expiry_date'] = null;
            return;
        }

        // حفظ الدفعات المتاحة للعرض في الواجهة
        $this->availableBatches[$itemId] = $batches->toArray();

        if ($this->expiryDateMode === 'nearest_first') {
            // تلقائياً استخدام أقرب صلاحية (FIFO)
            $nearestBatch = $batches->first();
            $this->invoiceItems[$index]['batch_number'] = $nearestBatch['batch_number'];
            $this->invoiceItems[$index]['expiry_date'] = $nearestBatch['expiry_date'];
            $this->invoiceItems[$index]['max_quantity'] = $nearestBatch['available_quantity'];
            $this->invoiceItems[$index]['show_batch_selector'] = false;
        } elseif ($this->expiryDateMode === 'show_all') {
            // عرض جميع الدفعات للمستخدم ليختار
            $this->invoiceItems[$index]['batch_number'] = null;
            $this->invoiceItems[$index]['expiry_date'] = null;
            $this->invoiceItems[$index]['show_batch_selector'] = true;
            $this->invoiceItems[$index]['max_quantity'] = null;
        }
    }

    /**
     * اختيار دفعة معينة بواسطة المستخدم (في وضع show_all)
     */
    public function selectBatch($index, $batchData)
    {
        if (!isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'];
        $batches = collect($this->availableBatches[$itemId] ?? []);

        $selectedBatch = $batches->firstWhere('batch_number', $batchData);

        if ($selectedBatch) {
            $this->invoiceItems[$index]['batch_number'] = $selectedBatch['batch_number'];
            $this->invoiceItems[$index]['expiry_date'] = $selectedBatch['expiry_date'];
            $this->invoiceItems[$index]['max_quantity'] = $selectedBatch['available_quantity'];
            $this->invoiceItems[$index]['show_batch_selector'] = false;
        }
    }

    /**
     * التحقق من الكمية المتاحة في الدفعة المحددة
     * ✅ تشتغل فقط في فواتير البيع
     */
    public function validateBatchQuantity($index)
    {
        // ✅ تشتغل فقط مع الفواتير الصادرة
        $outgoingInvoices = [10, 12, 14, 16, 19, 22];

        if (!in_array($this->type, $outgoingInvoices)) {
            return true; // ❌ لا تشتغل في فواتير الشراء
        }

        if ($this->expiryDateMode === 'disabled') {
            return true;
        }

        if (!isset($this->invoiceItems[$index])) {
            return true;
        }

        $item = $this->invoiceItems[$index];

        // إذا لم يتم تحديد دفعة بعد
        if (empty($item['batch_number'])) {
            return true;
        }

        $maxQuantity = $item['max_quantity'] ?? PHP_INT_MAX;
        $requestedQuantity = (float) ($item['quantity'] ?? 0);

        if ($requestedQuantity > $maxQuantity) {
            $this->dispatch(
                'error',
                title: 'تحذير!',
                text: "الكمية المطلوبة ({$requestedQuantity}) أكبر من المتوفر في الدفعة ({$maxQuantity}). يرجى تقليل الكمية أو تقسيمها على دفعات.",
                icon: 'warning'
            );

            // إعادة الكمية للحد الأقصى المتاح
            $this->invoiceItems[$index]['quantity'] = $maxQuantity;
            $this->recalculateSubValues();
            $this->calculateTotals();

            return false;
        }

        return true;
    }


    /**
     * تقسيم الكمية تلقائياً على عدة دفعات (في وضع nearest_first)
     * يتم استدعاؤها عند إدخال كمية أكبر من المتوفر في الدفعة الأولى
     */
    public function autoSplitQuantityAcrossBatches($itemId, $storeId, $requestedQuantity, $startIndex)
    {
        if ($this->expiryDateMode !== 'nearest_first') {
            return false;
        }

        $batches = $this->getItemBatches($itemId, $storeId);

        if ($batches->isEmpty()) {
            return false;
        }

        $totalAvailable = $batches->sum('available_quantity');

        if ($requestedQuantity > $totalAvailable) {
            $this->dispatch(
                'warning',
                title: 'تنبيه!',
                text: "الكمية المطلوبة ({$requestedQuantity}) أكبر من المتوفر ({$totalAvailable}). تم إضافة الكمية المتاحة فقط.",
                icon: 'warning'
            );

            $requestedQuantity = $totalAvailable;
        }

        $remainingQuantity = $requestedQuantity;
        $currentIndex = $startIndex;
        $originalItem = $this->invoiceItems[$startIndex];

        foreach ($batches as $batch) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $quantityFromThisBatch = min($remainingQuantity, $batch['available_quantity']);

            if ($currentIndex == $startIndex) {
                // تحديث الصف الحالي
                $this->invoiceItems[$currentIndex]['quantity'] = $quantityFromThisBatch;
                $this->invoiceItems[$currentIndex]['batch_number'] = $batch['batch_number'];
                $this->invoiceItems[$currentIndex]['expiry_date'] = $batch['expiry_date'];
                $this->invoiceItems[$currentIndex]['max_quantity'] = $batch['available_quantity'];
            } else {
                // إضافة صف جديد للدفعة التالية
                $this->invoiceItems[] = array_merge(
                    $originalItem,
                    [
                        'quantity' => $quantityFromThisBatch,
                        'batch_number' => $batch['batch_number'],
                        'expiry_date' => $batch['expiry_date'],
                        'max_quantity' => $batch['available_quantity'],
                        'show_batch_selector' => false,
                    ]
                );
            }

            $remainingQuantity -= $quantityFromThisBatch;
            $currentIndex++;
        }

        $this->recalculateSubValues();
        $this->calculateTotals();

        return true;
    }

    /**
     * تحديث بيانات الدفعة عند تغيير المخزن
     * ✅ تشتغل فقط في فواتير البيع
     */
    public function refreshBatchesForStore($index)
    {
        // ✅ تشتغل فقط مع الفواتير الصادرة
        $outgoingInvoices = [10, 12, 14, 16, 19, 22];

        if (!in_array($this->type, $outgoingInvoices)) {
            return; // ❌ لا تشتغل في فواتير الشراء
        }

        if ($this->expiryDateMode === 'disabled') {
            return;
        }

        if (!isset($this->invoiceItems[$index])) {
            return;
        }

        $itemId = $this->invoiceItems[$index]['item_id'] ?? null;
        $storeId = $this->acc2_id;

        if (!$itemId || !$storeId) {
            return;
        }

        // إعادة تحميل الدفعات
        $this->autoAssignExpiryData($itemId, $storeId, $index);
    }
}
