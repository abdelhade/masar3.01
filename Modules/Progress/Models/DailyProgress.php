<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyProgress extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

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
        return $this->belongsTo(Project::class)->withDefault([
            'name' => 'مشروع غير متوفر'
        ]);
    }

    public function projectItem()
    {
        return $this->belongsTo(ProjectItem::class, 'project_item_id')->withDefault([
            'name' => 'بند غير متوفر',
            'total_quantity' => 0,
            'completed_quantity' => 0
        ]);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withDefault([
            'name' => 'موظف غير معروف'
        ]);
    }

    // ✅ هنا هنحسب نسبة الإنجاز للبند المرتبط بالـ DailyProgress
    public function getCompletionPercentageAttribute()
    {
        if ($this->projectItem && $this->projectItem->total_quantity > 0) {
            return round(($this->projectItem->completed_quantity / $this->projectItem->total_quantity) * 100, 2);
        }
        return 0;
    }
}
