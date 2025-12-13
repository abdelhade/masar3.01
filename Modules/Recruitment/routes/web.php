<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Recruitment\Http\Controllers\ContractController;
use Modules\Recruitment\Http\Controllers\ContractTypeController;
use Modules\Recruitment\Http\Controllers\CvController;
use Modules\Recruitment\Http\Controllers\InterviewController;
use Modules\Recruitment\Http\Controllers\JobPostingController;
use Modules\Recruitment\Http\Controllers\OnboardingController;
use Modules\Recruitment\Http\Controllers\RecruitmentDashboardController;
use Modules\Recruitment\Http\Controllers\TerminationController;

Route::middleware(['auth', 'verified'])->prefix('recruitment')->name('recruitment.')->group(function () {
    Route::get('/dashboard', [RecruitmentDashboardController::class, 'index'])->name('dashboard');

    Route::resource('contract-types', ContractTypeController::class)->names('contract-types')->only('index');
    Route::resource('cvs', CvController::class)->names('cvs')->only(['index', 'show']);
    Route::resource('contracts', ContractController::class)->names('contracts')->only(['index', 'show']);
    Route::resource('job-postings', JobPostingController::class)->names('job-postings')->only(['index', 'show']);
    Route::resource('interviews', InterviewController::class)->names('interviews')->only(['index', 'show']);
    Route::get('interviews/calendar', [InterviewController::class, 'calendar'])->name('interviews.calendar');
    Route::resource('terminations', TerminationController::class)->names('terminations')->only(['index', 'show']);
    Route::resource('onboardings', OnboardingController::class)->names('onboardings')->only(['index', 'show']);
});
