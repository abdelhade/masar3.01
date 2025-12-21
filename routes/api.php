<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemSearchController;
use App\Http\Controllers\Api\InvoiceItemController;

// Items search API - يستخدم web middleware للحفاظ على session auth
// ✅ تم نقل البحث إلى Livewire method (searchItems) - أسرع وأبسط
Route::middleware(['web', 'auth'])->group(function () {
    // ✅ Route::get('/items/search', ...) - تم استبداله بـ Livewire method searchItems()
    Route::get('/items/{id}/details', [ItemSearchController::class, 'getItemDetails'])->name('api.items.details');
});

// Invoice items API - يستخدم web middleware للحفاظ على session auth
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/invoice/items/get-item', [InvoiceItemController::class, 'getItemForInvoice'])->name('api.invoice.items.get-item');
    Route::get('/invoice/items/{id}/details', [InvoiceItemController::class, 'getItemDetails'])->name('api.invoice.items.details');
});
