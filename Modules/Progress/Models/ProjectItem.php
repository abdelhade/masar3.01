<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectItem extends Model
{
    protected $fillable = [
        'project_id',
        'project_template_id',
        'work_item_id',
        'item_label',
        'total_quantity',
        'completed_quantity',
        'remaining_quantity',
        'start_date',
        'end_date',
        'planned_end_date',
        'daily_quantity',
        'estimated_daily_qty',
        'duration',
        'shift',
        'lag',
        'notes',
        'subproject_name',
        'item_order',
        'predecessor',
        'dependency_type',
        'is_measurable',
        'item_status_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectTemplate()
    {
        return $this->belongsTo(ProjectTemplate::class);
    }

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class)->withTrashed();
    }

    public function subproject()
    {
        return $this->belongsTo(Subproject::class, 'subproject_name', 'name');
    }

    /**
     * Get item status
     */
    public function itemStatus()
    {
        return $this->belongsTo(ItemStatus::class);
    }

    public function dailyProgresses()
    {
        return $this->hasMany(DailyProgress::class);
    }

    public function dailyProgress()
    {
        return $this->hasMany(DailyProgress::class);
    }

    /**
     * Get predecessor item
     */
    public function predecessorItem()
    {
        return $this->belongsTo(ProjectItem::class, 'predecessor', 'id');
    }

    /**
     * Get predecessor work item (through predecessorItem relationship)
     * Uses hasOneThrough to get WorkItem through the predecessor ProjectItem
     */
    public function predecessorWorkItem()
    {
        return $this->hasOneThrough(
            WorkItem::class,
            ProjectItem::class,
            'id', // Foreign key on intermediate table (project_items)
            'id', // Foreign key on final table (work_items)
            'predecessor', // Local key on this table (current item's predecessor id)
            'work_item_id' // Local key on intermediate table (predecessor item's work_item_id)
        )->withTrashed();
    }

    /**
     * Get successor items
     */
    public function successorItems()
    {
        return $this->hasMany(ProjectItem::class, 'predecessor', 'id');
    }

    /**
     * Calculate completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->total_quantity == 0) {
            return 0;
        }
        return ($this->completed_quantity / $this->total_quantity) * 100;
    }

    /**
     * Check if item has notes
     */
    public function getHasNotesAttribute()
    {
        return !empty($this->notes);
    }

    /**
     * Calculate actual duration based on start and end dates
     */
    public function getActualDurationAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->start_date);
        $end = \Carbon\Carbon::parse($this->end_date);

        return $start->diffInDays($end) + 1; // Include both start and end days
    }

    /**
     * Calculate duration variance
     */
    public function getDurationVarianceAttribute()
    {
        return $this->actual_duration - $this->duration;
    }

    /**
     * Scope for items with predecessors
     */
    public function scopeWithPredecessors($query)
    {
        return $query->whereNotNull('predecessor');
    }

    /**
     * Scope for items without predecessors
     */
    public function scopeWithoutPredecessors($query)
    {
        return $query->whereNull('predecessor');
    }

    /**
     * Scope for items with lag
     */
    public function scopeWithLag($query)
    {
        return $query->where('lag', '!=', 0);
    }

    /**
     * Scope for items with notes
     */
    public function scopeWithNotes($query)
    {
        return $query->whereNotNull('notes');
    }

    /**
     * Scope for project items only (not template items)
     */
    public function scopeForProjects($query)
    {
        return $query->whereNotNull('project_id');
    }

    /**
     * Scope for template items only
     */
    public function scopeForTemplates($query)
    {
        return $query->whereNotNull('project_template_id');
    }

    /**
     * Duplicate the item
     */
    public function duplicate($newProjectId = null)
    {
        $newItem = $this->replicate();
        $newItem->project_id = $newProjectId ?: $this->project_id;
        $newItem->completed_quantity = 0;
        $newItem->remaining_quantity = $this->total_quantity;
        $newItem->item_order = $this->item_order + 1;
        $newItem->save();

        return $newItem;
    }
}
