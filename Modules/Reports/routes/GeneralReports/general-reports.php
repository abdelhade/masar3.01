<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ReportController;
use Modules\Reports\Http\Controllers\GeneralReportController;
use Modules\Reports\Http\Controllers\ItemReportController;
use Modules\Reports\Http\Controllers\InventoryReportController;

Route::middleware(['auth'])->group(function () {
    // محلل العمل اليومي
    Route::get('reports/overall', [GeneralReportController::class, 'overall'])
        ->name('reports.overall')->middleware('permission:view Daily Activity Analyzer');

    // اليومية العامة
    Route::get('reports/journal-summery', [GeneralReportController::class, 'journalSummery'])
        ->name('reports.journal-summery')->middleware('permission:view General Journal');

    // كشف حساب عام - تفاصيل اليومية
    Route::get('reports/general-journal-details', [GeneralReportController::class, 'generalJournalDetails'])
        ->name('reports.general-journal-details')->middleware('permission:view General Account Statement');

    // محلل النشاط اليومي
    Route::get('/reports/daily-activity-analyzer', [GeneralReportController::class, 'dailyActivityAnalyzer'])
        ->name('reports.daily-activity-analyzer')->middleware('permission:view Daily Activity Analyzer');

    Route::middleware(['auth', 'permission:view Items Report'])->group(function () {
        // قائمة الأصناف بالأرصدة
        Route::get('items', [ItemController::class, 'index'])->name('items.index');

        // تقرير حركة الصنف
        Route::get('item-movement/{itemId?}/{warehouseId?}', [ItemController::class, 'itemMovementReport'])->name('item-movement');

        // تقرير الأصناف (الحد الأقصى والأدنى)
        // ملاحظة: تأكد من تطابق اسم الراوت مع الموجود في البلايد
        Route::get('reports/items-max-min-quantity', [ReportController::class, 'getItemsMaxMinQuantity'])
            ->name('reports.get-items-max-min-quantity');

        // تقرير الأصناف غير النشطة
        Route::get('reports/items/inactive', [ItemReportController::class, 'inactiveItemsReport'])
            ->name('reports.items.inactive');

        // تقرير الأصناف حسب المخزن
        Route::get('reports/items/with-stores', [ItemReportController::class, 'itemsWithStoresReport'])
            ->name('reports.items.with-stores');

        // مراقبة المخزون (Inventory Monitoring)
        Route::get('reports/inventory-discrepancy', [InventoryReportController::class, 'inventoryDiscrepancyReport'])
            ->name('reports.inventory-discrepancy-report');
    });
});
