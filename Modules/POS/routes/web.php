<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Http\Controllers\POSController;

/*
|--------------------------------------------------------------------------
| POS Module Routes
|--------------------------------------------------------------------------
|
| نظام نقاط البيع - المسارات الخاصة بوحدة POS
| جميع المسارات محمية بـ middleware للمصادقة والصلاحيات
|
*/

Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    
    // الصفحة الرئيسية لنظام POS
    Route::get('/', [POSController::class, 'index'])->name('index');
    
    // إنشاء معاملة POS جديدة
    Route::get('/create', [POSController::class, 'create'])->name('create');
    
    // عرض معاملة POS محددة
    Route::get('/show/{id}', [POSController::class, 'show'])->name('show');
    
    // طباعة فاتورة POS
    Route::get('/print/{operation_id}', [POSController::class, 'print'])->name('print');
    
    // حذف معاملة POS
    Route::delete('/delete/{id}', [POSController::class, 'destroy'])->name('destroy');
    
    // تقارير POS
    Route::get('/reports', [POSController::class, 'reports'])->name('reports');
    
});
