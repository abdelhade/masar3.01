<?php

return [
    'name' => 'Progress',
    
    /*
    |--------------------------------------------------------------------------
    | Project Settings
    |--------------------------------------------------------------------------
    */
    'default_working_days' => 6,
    'default_daily_work_hours' => 8,
    'default_weekly_holidays' => [5], // Friday
    
    /*
    |--------------------------------------------------------------------------
    | Progress Tracking Settings
    |--------------------------------------------------------------------------
    */
    'enable_subprojects' => true,
    'enable_weighted_progress' => true,
    'enable_gantt_chart' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Issue Tracking Settings
    |--------------------------------------------------------------------------
    */
    'issue_priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ],
    
    'issue_statuses' => [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    */
    'export_formats' => ['xlsx', 'csv', 'pdf'],
    'backup_path' => storage_path('app/backups/progress'),
];
