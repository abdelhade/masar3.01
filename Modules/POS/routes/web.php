<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\app\Http\Controllers\POSController;

/*
|--------------------------------------------------------------------------
| POS Module Routes
|--------------------------------------------------------------------------
|
| نظام نقاط البيع - المسارات الخاصة بوحدة POS
| جميع المسارات محمية بـ middleware للمصادقة والصلاحيات
|
*/

Route::middleware(['auth', 'verified', \Modules\POS\app\Http\Middleware\SafeSearchMiddleware::class])->prefix('pos')->name('pos.')->group(function () {

    // الصفحة الرئيسية لنظام POS
    Route::get('/', [POSController::class, 'index'])
        ->name('index')
        ->middleware('can:view POS System');

    // إنشاء معاملة POS جديدة
    Route::get('/create', [POSController::class, 'create'])
        ->name('create')
        ->middleware('can:create POS Transaction');

    // عرض معاملة POS محددة
    Route::get('/show/{id}', [POSController::class, 'show'])
        ->name('show')
        ->middleware('can:view POS Transaction');

    // تحرير معاملة POS
    Route::get('/edit/{id}', [POSController::class, 'edit'])
        ->name('edit')
        ->middleware('can:edit POS Transaction');

    // تحديث معاملة POS
    Route::put('/update/{id}', [POSController::class, 'update'])
        ->name('update')
        ->middleware('can:edit POS Transaction');

    // طباعة فاتورة POS
    Route::get('/print/{operation_id}', [POSController::class, 'print'])
        ->name('print')
        ->middleware('can:print POS Transaction');

    // حذف معاملة POS
    Route::delete('/delete/{id}', [POSController::class, 'destroy'])
        ->name('destroy')
        ->middleware('can:delete POS Transaction');

    // تقارير POS
    Route::get('/reports', [POSController::class, 'reports'])
        ->name('reports')
        ->middleware('can:view POS Reports');

    // إعدادات الكاشير
    Route::get('/settings', [POSController::class, 'settings'])
        ->name('settings')
        ->middleware('can:view POS System');

    // شاشة فحص السعر بالباركود
    Route::get('/price-check', [POSController::class, 'priceCheck'])
        ->name('price-check')
        ->middleware('can:view POS System');

    // AJAX Routes
    Route::get('/api/search-items', [POSController::class, 'searchItems'])->name('api.search-items');
    Route::get('/api/search-barcode', [POSController::class, 'searchByBarcode'])->name('api.search-barcode');
    Route::get('/api/price-check/{barcode}', [POSController::class, 'getPriceByBarcode'])->name('api.price-check');
    Route::get('/api/item/{id}', [POSController::class, 'getItemDetails'])->name('api.item-details');
    Route::get('/api/category/{categoryId}/items', [POSController::class, 'getCategoryItems'])->name('api.category-items');
    Route::get('/api/customer/{id}/balance', [POSController::class, 'getCustomerBalance'])->name('api.customer-balance');
    Route::get('/api/recent-transactions', [POSController::class, 'getRecentTransactions'])->name('api.recent-transactions');
    Route::post('/api/store', [POSController::class, 'store'])->name('api.store');
    Route::post('/api/sync', [POSController::class, 'syncTransactions'])->name('api.sync');
    Route::post('/api/settings/update', [POSController::class, 'updateSettings'])->name('api.settings.update');
    Route::get('/api/settings/scale', [POSController::class, 'getScaleSettings'])->name('api.settings.scale');

    // Hold/Suspend Order Routes
    Route::post('/api/hold-order', [POSController::class, 'holdOrder'])->name('api.hold-order');
    Route::get('/api/held-orders', [POSController::class, 'getHeldOrders'])->name('api.held-orders');
    Route::get('/api/recall-order/{id}', [POSController::class, 'recallOrder'])->name('api.recall-order');
    Route::post('/api/complete-held-order/{id}', [POSController::class, 'completeHeldOrder'])->name('api.complete-held-order');
    Route::delete('/api/delete-held-order/{id}', [POSController::class, 'deleteHeldOrder'])->name('api.delete-held-order');

    // Petty Cash (المصروفات النثرية)
    Route::post('/api/petty-cash', [POSController::class, 'pettyCash'])->name('api.petty-cash');

    // Return Invoice
    Route::get('/api/invoice/{proId}', [POSController::class, 'getInvoice'])->name('api.invoice');
    Route::post('/api/return-invoice', [POSController::class, 'returnInvoice'])->name('api.return-invoice');

});
