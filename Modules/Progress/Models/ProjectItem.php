<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectItem extends Model
{
    protected $fillable = [
        'project_id',
        'project_template_id',
        'work_item_id',
        'subproject_id',
        'total_quantity',
        'completed_quantity',
        'start_date',
        'end_date',
        'daily_quantity',
        'estimated_daily_qty',
        'subproject_name',
        'notes',
        'is_measurable',
        'duration',
        'predecessor',
        'dependency_type',
        'lag',
        'item_order',
        'item_status_id'
    ];

    public function project()
    {
        return $this->belongsTo(ProjectProgress::class);
    }

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function dailyProgress()
    {
        return $this->hasMany(DailyProgress::class);
    }

    public function getCompletionPercentageAttribute()
    {
        return $this->total_quantity > 0 
            ? ($this->completed_quantity / $this->total_quantity) * 100 
            : 0;
    }

    public function predecessorItem()
    {
        return $this->belongsTo(ProjectItem::class, 'predecessor');
    }

    public function status()
    {
        return $this->belongsTo(ItemStatus::class, 'item_status_id');
    }
}
