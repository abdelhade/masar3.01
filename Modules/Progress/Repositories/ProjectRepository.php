<?php

namespace Modules\Progress\Repositories;

use Modules\Progress\Models\ProjectProgress as Project;
use Illuminate\Support\Collection;

class ProjectRepository
{
    public function findById(int $id): ?Project
    {
        return Project::find($id);
    }

    public function findByIdWithRelations(int $id, array $relations = []): ?Project
    {
        return Project::with($relations)->find($id);
    }

    public function getAllActive(): Collection
    {
        $query = Project::where('is_draft', false);
        
        // Log the query
        \Log::info('getAllActive Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
        
        $projects = $query->where('is_progress', 1)
            ->with('client')
            ->withCount('items')
            ->latest()
            ->get();
            
        \Log::info('getAllActive Result', [
            'count' => $projects->count(),
            'ids' => $projects->pluck('id')->toArray()
        ]);
        
        return $projects;
    }

    public function getAllDrafts(): Collection
    {
        return Project::where('is_draft', true)
            ->with('client')
            ->withCount('items')
            ->latest()
            ->get();
    }

    public function getByUserId(int $userId, bool $isDraft = false): Collection
    {
        // البحث عن المشاريع المرتبطة بالموظف الذي user_id = $userId
        return Project::whereHas('employees', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('is_draft', $isDraft)
            ->where('is_progress', 1)
            ->with('client')
            ->withCount('items')
            ->latest()
            ->get();
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    public function syncEmployees(Project $project, array $employeeIds): void
    {
        $project->employees()->sync($employeeIds);
    }

    public function getProjectWithProgress(Project $project): Project
    {
        return $project->load([
            'client',
            'items.workItem.category',
            'items' => fn($q) => $q->withSum('dailyProgress', 'quantity'),
            'items.dailyProgress' => fn($q) => $q->orderBy('progress_date', 'desc')->limit(5)->with('employee'),
            'dailyProgress.employee'
        ]);
    }
}
