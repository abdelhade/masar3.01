<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\{
    ChanceSourceController,
    ClientContactController,
    ClientController,
    LeadController,
    LeadStatusController,
    StatisticsController,
    TaskController
};
use Modules\CRM\Livewire\LeadsBoard;

Route::resource('tasks', TaskController::class)->names('tasks');

Route::middleware(['auth', 'verified'])->prefix('crm')->group(function () {
    Route::resource('clients', ClientController::class)->names('clients');
    Route::resource('chance-sources', ChanceSourceController::class)->names('chance-sources');
    Route::resource('lead-status', LeadStatusController::class)->names('lead-status');
    Route::resource('client-contacts', ClientContactController::class)->names('client-contacts');


    Route::get('/leads', LeadsBoard::class)->name('leads.index');
    Route::get('/leads/board', [LeadController::class, 'board'])->name('leads.board');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::post('/leads/update-status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
});
