<?php

namespace Modules\Progress\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class DailyProgress extends Model
{
    protected $fillable = [
        'project_id',
        'project_item_id',
        'employee_id',
        'progress_date',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'progress_date' => 'date',
    ];

    protected $appends = ['completion_percentage'];

    public function project()
    {
        return $this->belongsTo(ProjectProgress::class);
    }

    public function projectItem()
    {
        return $this->belongsTo(ProjectItem::class, 'project_item_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->projectItem && $this->projectItem->total_quantity > 0) {
            return round(($this->projectItem->completed_quantity / $this->projectItem->total_quantity) * 100, 2);
        }
        return 0;
    }
}
