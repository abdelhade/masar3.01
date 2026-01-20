<?php

use Illuminate\Support\Facades\Route;
use Modules\Progress\Http\Controllers\DashboardController;
use Modules\Progress\Http\Controllers\{
    DailyProgressController,
    WorkItemController,
    ProjectItemController,
    ProjectTypeController,
    ProjectTemplateController,
    ProjectProgressController,
    WorkItemCategoryController,
    IssueController,
    ItemStatusController,
    ProjectController
};

Route::get('/progress/dashboard', [DashboardController::class, 'index'])->name('progress.dashboard')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    // Route::resource('projects', ProjectController::class);
    // Dashboard route moved to top
    Route::resource('item-statuses', ItemStatusController::class)->names('item-statuses');
    Route::resource('project-types', ProjectTypeController::class)->names('project.types');
    Route::post('work-items/reorder', [WorkItemController::class, 'reorder'])->name('work.items.reorder');
    Route::resource('work-items', WorkItemController::class)->names('work.items');
    Route::resource('work-item-categories', WorkItemCategoryController::class)->names('work-item-categories');
    Route::get('issues/kanban', [IssueController::class, 'kanban'])->name('issues.kanban');
    Route::post('issues/update-status', [IssueController::class, 'updateStatus'])->name('issues.updateStatus');
    Route::resource('issues', IssueController::class)->names('issues');
    Route::post('issues/{issue}/comments', [IssueController::class, 'storeComment'])->name('issues.comments.store');
    Route::delete('issues/comments/{comment}', [IssueController::class, 'destroyComment'])->name('issues.comments.destroy');
    Route::delete('issues/attachments/{attachment}', [IssueController::class, 'destroyAttachment'])->name('issues.attachments.destroy');
    Route::resource('project-templates', ProjectTemplateController::class)->names('project.template');
    // Route::resource('project-items', ProjectItemController::class)->names('project.items');
    Route::get('/projects', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'index'])->name('progress.project.index');
    Route::post('/projects/quick-store', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'quickStore'])->name('progress.project.quickStore');
    Route::get('/projects/create', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'create'])->name('progress.project.create');
    Route::post('/projects', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'store'])->name('progress.project.store');
    Route::get('/projects/{project}', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'show'])->name('progress.project.show');
    Route::get('/projects/{project}/details', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'getProjectDetails'])->name('progress.project.details');
    Route::get('/projects/{project}/edit', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'edit'])->name('progress.project.edit');
    Route::put('/projects/{project}', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'update'])->name('progress.project.update');
    Route::put('/projects/{project}/publish', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'publish'])->name('progress.project.publish');
    Route::delete('/projects/{project}', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'destroy'])->name('progress.project.destroy');
    Route::post('/projects/{project}/replicate', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'replicate'])->name('progress.project.replicate');

    Route::get('/projects/{project}/progress', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'progress'])->name('projects.progress/state');
    Route::get('/projects/{project}/subprojects', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'getSubprojects'])->name('progress.project.subprojects');
    Route::post('/projects/{project}/subprojects/update-weight', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'updateWeight'])->name('progress.project.subprojects.weight');
    Route::post('/projects/{project}/subprojects/update-all-weights', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'updateAllWeights'])->name('progress.project.subprojects.updateAll');
    Route::get('/projects/{project}/gantt', [\Modules\Progress\Http\Controllers\ProjectProgressController::class, 'gantt'])->name('projects.gantt');
    Route::get('/daily-progress', [\Modules\Progress\Http\Controllers\DailyProgressController::class, 'index'])->name('daily_progress.index');
    Route::get('/daily-progress/create', [\Modules\Progress\Http\Controllers\DailyProgressController::class, 'create'])->name('daily_progress.create');
    Route::post('/daily-progress', [\Modules\Progress\Http\Controllers\DailyProgressController::class, 'store'])->name('daily_progress.store');
    Route::get('/daily-progress/{dailyProgress}/edit', [\Modules\Progress\Http\Controllers\DailyProgressController::class, 'edit'])->name('daily_progress.edit');
    Route::put('/daily-progress/{dailyProgress}', [\Modules\Progress\Http\Controllers\DailyProgressController::class, 'update'])->name('daily_progress.update');
    Route::delete('/daily-progress/{dailyProgress}', [\Modules\Progress\Http\Controllers\DailyProgressController::class, 'destroy'])->name('daily_progress.destroy');

    Route::prefix('projects/{project}')->middleware('auth')->group(function () {
        Route::post('/items', [ProjectItemController::class, 'store'])->name('project-items.store');
        Route::put('/items/{projectItem}', [ProjectItemController::class, 'update'])->name('project-items.update');
        Route::delete('/items/{projectItem}', [ProjectItemController::class, 'destroy'])->name('project-items.destroy');
        Route::patch('/items/{projectItem}/status', [ProjectItemController::class, 'updateItemStatus'])->name('projects.items.update-status');
    });



    Route::get('/daily-progress/executed-today', [DailyProgressController::class, 'executedToday']);

    // Recycle Bin Routes
    Route::get('/recycle-bin', [\Modules\Progress\Http\Controllers\RecycleBinController::class, 'index'])->name('progress.recycle_bin.index');
    Route::post('/recycle-bin/{type}/{id}/restore', [\Modules\Progress\Http\Controllers\RecycleBinController::class, 'restore'])->name('progress.recycle_bin.restore');
    Route::delete('/recycle-bin/{type}/{id}/force-delete', [\Modules\Progress\Http\Controllers\RecycleBinController::class, 'forceDelete'])->name('progress.recycle_bin.force_delete');
});
