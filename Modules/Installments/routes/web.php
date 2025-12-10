<?php

use Illuminate\Support\Facades\Route;
use Modules\Installments\Http\Controllers\InstallmentController;

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/installments/plans', [InstallmentController::class, 'index'])
        ->name('installments.plans.index')
        ->middleware('can:view Installment Plans');

    Route::get('/installments/plans/create', [InstallmentController::class, 'create'])
        ->name('installments.plans.create')
        ->middleware('can:create Installment Plans');

    Route::get('/installments/plans/{plan}', [InstallmentController::class, 'show'])
        ->name('installments.plans.show')
        ->middleware('can:view Installment Plans');

    Route::get('/installments/payments/overdue', [InstallmentController::class, 'overduePayments'])
        ->name('installments.payments.overdue')
        ->middleware('can:view Overdue Installments');
});
