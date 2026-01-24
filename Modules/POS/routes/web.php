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

    // AJAX Routes
    Route::get('/api/search-items', [POSController::class, 'searchItems'])->name('api.search-items');
    Route::get('/api/search-barcode', [POSController::class, 'searchByBarcode'])->name('api.search-barcode');
    Route::get('/api/item/{id}', [POSController::class, 'getItemDetails'])->name('api.item-details');
    Route::get('/api/category/{categoryId}/items', [POSController::class, 'getCategoryItems'])->name('api.category-items');
    Route::post('/api/store', [POSController::class, 'store'])->name('api.store');
    Route::post('/api/sync', [POSController::class, 'syncTransactions'])->name('api.sync');

});
