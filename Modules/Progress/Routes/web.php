<?php

use Illuminate\Support\Facades\Route;
use Modules\Progress\Http\Controllers\ProjectController;
use Modules\Progress\Http\Controllers\DailyProgressController;
use Modules\Progress\Http\Controllers\WorkItemController;
use Modules\Progress\Http\Controllers\ClientController;
use Modules\Progress\Http\Controllers\EmployeeController;
use Modules\Progress\Http\Controllers\ProjectItemController;
use Modules\Progress\Http\Controllers\CategoryController;
use Modules\Progress\Http\Controllers\LocalizationController;
use Modules\Progress\Http\Controllers\ProjectTemplateController;
use Modules\Progress\Http\Controllers\ProjectTypeController;
use Modules\Progress\Http\Controllers\ItemStatusController;
use Modules\Progress\Http\Controllers\IssueController;
use Modules\Progress\Http\Controllers\DashboardController;
use Modules\Progress\Http\Controllers\RecycleBinController;
use Modules\Progress\Http\Controllers\DataExportController;
use Modules\Progress\Http\Controllers\ReportController;
use Modules\Progress\Http\Controllers\ActivityLogController;
use Modules\Progress\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| Progress Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('progress')->name('progress.')->group(function () {
    
    // Test route
    Route::get('/test', function() {
        return 'Progress module is working!';
    })->name('test');
    
    // Dashboard
    Route::get('/dashboard', function() {
        return view('progress::dashboard-simple');
    })->name('dashboard');
    
    Route::get('/dashboard-full', [DashboardController::class, 'index'])->name('dashboard.full');
    
    // Projects Routes
    Route::pattern('project_template', '[0-9]+');
    
    Route::get('/projects/drafts', [ProjectController::class, 'drafts'])->name('projects.drafts');
    Route::post('/projects/{project}/publish', [ProjectController::class, 'publish'])->name('projects.publish');
    Route::get('/projects/{project}/gantt', [ProjectController::class, 'ganttChart'])->name('projects.gantt');
    Route::get('/projects/{project}/gantt-data', [ProjectController::class, 'ganttData'])->name('projects.gantt.data');
    Route::get('/projects/{project}/items-data', [ProjectController::class, 'getItemsData'])->name('projects.items.data');
    Route::get('/projects/{project}/progress', [ProjectController::class, 'progress'])->name('projects.progress');
    Route::get('/projects/{project}/progress/export', [ProjectController::class, 'export'])->name('projects.progress.export');
    Route::post('/projects/{project}/copy', [ProjectController::class, 'copy'])->name('projects.copy');
    Route::post('/projects/{project}/save-as-template', [ProjectController::class, 'saveAsTemplate'])->name('projects.save-as-template');
    Route::get('/projects/{project}/dashboard', [ProjectController::class, 'dashboard'])->name('projects.dashboard');
    Route::get('/projects/{project}/dashboard/print', [ProjectController::class, 'dashboardPrint'])->name('projects.dashboard.print');
    
    Route::resource('projects', ProjectController::class);
    
    // Project Items Routes
    Route::prefix('projects/{project}')->group(function () {
        Route::post('/items', [ProjectItemController::class, 'store'])->name('project-items.store');
        Route::put('/items/{projectItem}', [ProjectItemController::class, 'update'])->name('project-items.update');
        Route::delete('/items/{projectItem}', [ProjectItemController::class, 'destroy'])->name('project-items.destroy');
        Route::post('/subprojects/update-all-weights', [ProjectController::class, 'updateAllSubprojectsWeight'])->name('projects.update-all-subprojects-weight');
        Route::patch('/items/{projectItem}/status', [ProjectController::class, 'updateItemStatus'])->name('projects.items.update-status');
    });
    
    // Project Templates Routes
    Route::match(['POST', 'PUT'], '/project-templates/store-from-form', [ProjectTemplateController::class, 'storeFromForm'])->name('project-templates.store-from-form');
    Route::post('/project-templates/store-from-project', [ProjectTemplateController::class, 'storeFromProject'])->name('project-templates.store-from-project');
    Route::get('project-templates/{project_template}/items', [ProjectTemplateController::class, 'items'])->name('project-templates.items');
    Route::get('project-templates/{project_template}/data', [ProjectTemplateController::class, 'getTemplateData'])->name('project-templates.data');
    Route::get('project-templates/{project_template}/debug-predecessors', [ProjectTemplateController::class, 'debugPredecessors'])->name('project-templates.debug-predecessors');
    Route::post('/project-templates/{project_template}/reorder-items', [ProjectTemplateController::class, 'reorderItems'])->name('project-templates.reorder-items');
    Route::resource('project-templates', ProjectTemplateController::class);
    
    // Project Types
    Route::resource('project_types', ProjectTypeController::class);
    
    // Item Statuses
    Route::resource('item-statuses', ItemStatusController::class);
    
    // Issues Routes
    Route::prefix('issues')->name('issues.')->group(function () {
        Route::get('/', [IssueController::class, 'index'])->name('index');
        Route::get('/kanban', [IssueController::class, 'kanban'])->name('kanban');
        Route::get('/statistics', [IssueController::class, 'statistics'])->name('statistics');
        Route::get('/create', [IssueController::class, 'create'])->name('create');
        Route::post('/', [IssueController::class, 'store'])->name('store');
        Route::get('/{issue}', [IssueController::class, 'show'])->name('show');
        Route::get('/{issue}/edit', [IssueController::class, 'edit'])->name('edit');
        Route::put('/{issue}', [IssueController::class, 'update'])->name('update');
        Route::delete('/{issue}', [IssueController::class, 'destroy'])->name('destroy');
        
        // Comments
        Route::post('/{issue}/comments', [IssueController::class, 'addComment'])->name('comments.store');
        Route::delete('/comments/{comment}', [IssueController::class, 'deleteComment'])->name('comments.destroy');
        
        // Attachments
        Route::get('/attachments/{attachment}/download', [IssueController::class, 'downloadAttachment'])->name('attachments.download');
        Route::delete('/attachments/{attachment}', [IssueController::class, 'deleteAttachment'])->name('attachments.destroy');
        
        // Status update (for Kanban)
        Route::patch('/{issue}/status', [IssueController::class, 'updateStatus'])->name('update-status');
    });
    
    // Clients Routes
    Route::resource('clients', ClientController::class)->except(['show']);
    
    // Employees Routes
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::get('/employees/{id}/permissions', [EmployeeController::class, 'editPermissions'])->name('employees.permissions');
    Route::post('/employees/{id}/permissions', [EmployeeController::class, 'updatePermissions'])->name('employees.updatePermissions');
    
    // Work Items Routes
    Route::resource('work-items', WorkItemController::class)->except(['show']);
    Route::post('/work-items/reorder', [WorkItemController::class, 'reorder'])->name('work-items.reorder');
    Route::get('/work-items/search-ajax', [WorkItemController::class, 'search'])->name('work-items.search');
    
    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);
    
    // Daily Progress Routes
    Route::resource('daily-progress', DailyProgressController::class)->except(['show']);
    
    // Reports Routes
    Route::get('/progress-report', [ReportController::class, 'progressReport'])->name('progress.report');
    
    // Export Routes
    Route::get('/export', function () {
        return view('progress::export');
    })->name('export.page');
    Route::get('/export-data', [DataExportController::class, 'exportAllData'])->name('export.data');
    Route::get('/export-sql', [DataExportController::class, 'exportSqlDump'])->name('export.sql');
    
    // Recycle Bin Routes
    Route::prefix('recycle-bin')->name('recycle-bin.')->group(function () {
        Route::get('/', [RecycleBinController::class, 'index'])->name('index');
        Route::get('/restore/{type}/{id}', [RecycleBinController::class, 'restore'])->name('restore');
        Route::get('/force-delete/{type}/{id}', [RecycleBinController::class, 'forceDelete'])->name('force-delete');
        Route::delete('/{id}/permanent-delete', [RecycleBinController::class, 'permanentDelete'])->name('permanent-delete');
    });
    
    // Activity Log Routes
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
        Route::get('/user/{userId}/activities', [ActivityLogController::class, 'userActivities'])->name('user-activities');
        Route::get('/subject/{subjectType}/{subjectId}/activities', [ActivityLogController::class, 'subjectActivities'])->name('subject-activities');
        Route::get('/api/activities', [ActivityLogController::class, 'getActivities'])->name('api.activities');
    });
    
    // Backup & Restore Routes
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::get('/export', [BackupController::class, 'export'])->name('export');
        Route::post('/import', [BackupController::class, 'import'])->name('import');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::delete('/delete/{filename}', [BackupController::class, 'destroy'])->name('destroy');
    });
    
    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/project-items/{project}', [ProjectItemController::class, 'getByProject']);
        Route::get('/daily-progress/executed-today', [DailyProgressController::class, 'executedToday']);
        Route::get('/projects/{project}/subprojects', [ProjectController::class, 'getSubprojects']);
        Route::post('/projects/{project}/subprojects/{subproject}/update-weight', [ProjectController::class, 'updateSubprojectWeight'])->name('projects.subprojects.update-weight');
        Route::post('/projects/{project}/subprojects/update-all-weights', [ProjectController::class, 'updateAllSubprojectsWeight'])->name('projects.subprojects.update-all-weights');
    });
});
