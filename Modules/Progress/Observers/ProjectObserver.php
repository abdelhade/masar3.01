<?php

namespace Modules\Progress\Observers;

use Modules\Progress\Models\ProjectProgress as Project;
use Modules\Progress\Services\ActivityLogService;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        ActivityLogService::created($project, [
            'name' => $project->name,
            'client_id' => $project->client_id,
            'status' => $project->status,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
        ], 'projects');
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        $changes = $project->getChanges();
        $original = $project->getOriginal();
        
        $properties = [];
        foreach ($changes as $field => $newValue) {
            if ($field !== 'updated_at') {
                $properties[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue
                ];
            }
        }
        
        if (!empty($properties)) {
            ActivityLogService::updated($project, $properties, 'projects');
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        ActivityLogService::deleted($project, [
            'name' => $project->name,
            'client_id' => $project->client_id,
            'status' => $project->status,
        ], 'projects');
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        ActivityLogService::event(
            "Restored Project: {$project->name}",
            $project,
            'restored',
            ['name' => $project->name],
            'projects'
        );
    }

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        ActivityLogService::event(
            "Force Deleted Project: {$project->name}",
            $project,
            'force_deleted',
            ['name' => $project->name],
            'projects'
        );
    }
}
