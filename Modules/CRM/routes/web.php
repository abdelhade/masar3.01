<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;

use Modules\CRM\Http\Controllers\{
    ActivityController,
    ChanceSourceController,
    ClientCategoryController,
    ClientContactController,
    ClientTypeController,
    // ClientController,
    LeadController,
    LeadStatusController,
    ReturnController,
    StatisticsController,
    TaskController,
    TaskTypeController,
    TicketController
};
use Modules\CRM\Livewire\LeadsBoard;

Route::middleware(['auth', 'verified'])->prefix('crm')->group(function () {
    Route::resource('clients', ClientController::class)->names('crm.clients');
    Route::resource('chance-sources', ChanceSourceController::class)->names('chance-sources');
    Route::resource('lead-status', LeadStatusController::class)->names('lead-status');
    Route::resource('client-contacts', ClientContactController::class)->names('client-contacts');
    Route::resource('activities', ActivityController::class)->names('activities');
    Route::resource('client-categories', ClientCategoryController::class)->names('client.categories');
    Route::resource('client-types', ClientTypeController::class)->names('client-types');

    Route::resource('tasks', TaskController::class)->names('tasks');
    Route::resource('tasks-types', TaskTypeController::class)->names('tasks.types');

    Route::get('/leads', LeadsBoard::class)->name('leads.index');
    Route::get('/leads/board', [LeadController::class, 'board'])->name('leads.board');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::post('/leads/update-status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    Route::resource('tickets', TicketController::class)->names('tickets');
    Route::post('/tickets/{ticket}/comment', [TicketController::class, 'addComment'])->name('tickets.addComment');
    Route::post('/tickets/update-status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');

    Route::resource('returns', ReturnController::class)->names('returns');
    Route::post('/returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
    Route::post('/returns/{return}/reject', [ReturnController::class, 'reject'])->name('returns.reject');

    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
});
