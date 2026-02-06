<?php

namespace Modules\Progress\Repositories;

use Modules\Progress\Models\Project;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\WorkItem;

class ProjectItemRepository
{
    public function createItem(Project $project, array $itemData): ProjectItem
    {
        // Remove project_template_id if it exists (only for project items, not template items)
        if (isset($itemData['project_template_id'])) {
            unset($itemData['project_template_id']);
        }
        
        // Set default start_date and end_date if not provided
        if (empty($itemData['start_date'])) {
            $itemData['start_date'] = now()->format('Y-m-d');
        }
        if (empty($itemData['end_date'])) {
            $itemData['end_date'] = now()->addDays(30)->format('Y-m-d');
        }
        
        return $project->items()->create($itemData);
    }

    public function deleteAllItems(Project $project): void
    {
        $project->items()->delete();
    }

    public function updateItem(int $itemId, array $itemData): ProjectItem
    {
        $item = ProjectItem::findOrFail($itemId);
        
        // Remove project_template_id if it exists (only for project items, not template items)
        // Only remove if this is a project item (has project_id)
        if ($item->project_id && isset($itemData['project_template_id'])) {
            unset($itemData['project_template_id']);
        }
        
        $item->update($itemData);
        return $item->fresh();
    }

    public function deleteItem(int $itemId): void
    {
        ProjectItem::where('id', $itemId)->delete();
    }

    public function updateItemPredecessor(int $itemId, ?int $predecessorId): void
    {
        ProjectItem::where('id', $itemId)
            ->update(['predecessor' => $predecessorId]);
    }

    public function getWorkItemById(int $workItemId): ?WorkItem
    {
        return WorkItem::find($workItemId);
    }
}
