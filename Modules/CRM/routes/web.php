<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;

use Modules\CRM\Http\Controllers\{
    ActivityController,
    CampaignController,
    CampaignTrackingController,
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
    Route::resource('clients', ClientController::class)->names('clients'); // Already has constructor checks
    Route::resource('chance-sources', ChanceSourceController::class)->names('chance-sources')->middleware('can:view Chance Sources');
    Route::resource('lead-status', LeadStatusController::class)->names('lead-status')->middleware('can:view Lead Statuses');
    Route::resource('client-contacts', ClientContactController::class)->names('client-contacts')->middleware('can:view Client Contacts');
    Route::resource('activities', ActivityController::class)->names('activities')->middleware('can:view Activities');
    Route::resource('client-categories', ClientCategoryController::class)->names('client.categories')->middleware('can:view Client Categories');
    Route::resource('client-types', ClientTypeController::class)->names('client-types')->middleware('can:view Client Types');

    Route::resource('tasks', TaskController::class)->names('tasks')->middleware('can:view Tasks');
    Route::resource('tasks-types', TaskTypeController::class)->names('tasks.types')->middleware('can:view Task Types');

    Route::get('/leads', LeadsBoard::class)->name('leads.index')->middleware('can:view Leads');
    Route::get('/leads/board', [LeadController::class, 'board'])->name('leads.board')->middleware('can:view Leads');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store')->middleware('can:create Leads');
    Route::post('/leads/update-status', [LeadController::class, 'updateStatus'])->name('leads.update-status')->middleware('can:edit Leads');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy')->middleware('can:delete Leads');

    Route::resource('tickets', TicketController::class)->names('tickets')->middleware('can:view Tickets');
    Route::post('/tickets/{ticket}/comment', [TicketController::class, 'addComment'])->name('tickets.addComment')->middleware('can:edit Tickets');
    Route::post('/tickets/update-status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus')->middleware('can:edit Tickets');

    Route::resource('returns', ReturnController::class)->names('returns')->middleware('can:view Returns');
    Route::post('/returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve')->middleware('can:edit Returns');
    Route::post('/returns/{return}/reject', [ReturnController::class, 'reject'])->name('returns.reject')->middleware('can:edit Returns');

    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index')->middleware('can:view CRM Statistics');

    // Campaigns Routes
    Route::resource('campaigns', CampaignController::class)->names('campaigns'); // Has constructor checks
    Route::post('/campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send')->middleware('can:edit Campaigns');
    Route::post('/campaigns/preview', [CampaignController::class, 'preview'])->name('campaigns.preview')->middleware('can:create Campaigns');
    
    // Campaign Tracking Routes (Public - No Auth)
});

// Campaign Tracking Routes (Public - No Auth Required)
Route::prefix('track')->group(function () {
    Route::get('/open/{trackingCode}', [CampaignTrackingController::class, 'trackOpen'])->name('campaigns.track.open');
    Route::get('/click/{trackingCode}', [CampaignTrackingController::class, 'trackClick'])->name('campaigns.track.click');
});
