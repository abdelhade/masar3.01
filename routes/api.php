<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemSearchController;
use App\Http\Controllers\Api\InvoiceItemController;

// ✅ تم نقل البحث إلى Livewire method (searchItems) - أسرع وأبسط
Route::middleware(['web', 'auth'])->group(function () {
    // ✅ Route::get('/items/search', ...) - تم استبداله بـ Livewire method searchItems()
    Route::get('/items/{id}/details', [ItemSearchController::class, 'getItemDetails'])->name('api.items.details');
});

// Invoice items API - يستخدم web middleware للحفاظ على session auth
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/invoice/items/get-item', [InvoiceItemController::class, 'getItemForInvoice'])->name('api.invoice.items.get-item');
    Route::get('/invoice/items/{id}/details', [InvoiceItemController::class, 'getItemDetails'])->name('api.invoice.items.details');

    // ✅ New Client-Side Search API
    // Route::get('/items/lite', [App\Http\Controllers\Api\ItemsApiController::class, 'lite'])->name('api.items.lite');
    Route::get('/items/{id}/lite-details', [App\Http\Controllers\Api\ItemsApiController::class, 'details'])->name('api.items.lite-details');

    // Theme Switcher API
    Route::post('/set-theme', function () {
        $theme = request()->input('theme', 'default');
        session(['theme' => $theme]);
        return response()->json(['success' => true, 'theme' => $theme]);
    })->name('api.set-theme');
});
