<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\ClientController;
use Modules\CRM\Http\Controllers\LeadController;
use Modules\CRM\Http\Controllers\LeadStatusController;
use Modules\CRM\Http\Controllers\ChanceSourceController;
use Modules\CRM\Http\Controllers\ClientContactController;


Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('clients', ClientController::class)->names('crm.clients');
    // Route::apiResource('leads', LeadController::class)->names('crm.leads');
    // Route::apiResource('lead-status', LeadStatusController::class)->names('crm.lead-status');
    // Route::apiResource('chances', ChanceSourceController::class)->names('crm.chances');
    // Route::apiResource('client-contacts', ClientContactController::class)->names('crm.client-contacts');
});
