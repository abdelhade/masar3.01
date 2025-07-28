<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;       
use App\Http\Controllers\ChequeController;
Route::resource('cheques', ChequeController::class)->names('cheques');
