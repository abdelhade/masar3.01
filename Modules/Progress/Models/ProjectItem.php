<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectItem extends Model
{
    protected $fillable = [
        'project_id',
        'work_item_id',
        'total_quantity',
        'completed_quantity',
        'start_date',
        'end_date',
        'daily_quantity'
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
        return ($this->completed_quantity / $this->total_quantity) * 100;
    }
}
