<?php

use Illuminate\Support\Facades\Route;
use Modules\Zatca\Http\Controllers\ZatcaController;

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('zatcas', ZatcaController::class)->names('zatca');
    Route::prefix('zatca')->group(function () {
        // اختبار الاتصال
        Route::get('/test-connection', [ZatcaController::class, 'testConnection']);

        // إنشاء فاتورة تجريبية
        Route::post('/create-test-invoice', [ZatcaController::class, 'createTestInvoice']);

        // العمليات على الفواتير
        Route::post('/generate-xml', [ZatcaController::class, 'generateXML']);
        Route::post('/generate-qr', [ZatcaController::class, 'generateQR']);
        Route::post('/submit-invoice', [ZatcaController::class, 'submitInvoice']);

        // معلومات الفاتورة
        Route::get('/invoice-status/{invoice_id}', [ZatcaController::class, 'getInvoiceStatus']);

        // العملية الكاملة
        Route::post('/full-process', [ZatcaController::class, 'fullProcess']);
    });
// });
