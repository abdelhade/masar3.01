<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;       
use App\Http\Controllers\MagicalController;
use App\Http\Controllers\MagicFormController;
Route::resource('magicals', MagicalController::class)->names('magicals');
Route::resource('magical-forms', MagicFormController::class)->names('magical-forms');
