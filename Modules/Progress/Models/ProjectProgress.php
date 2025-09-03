<?php

namespace Modules\Progress\Models;

use App\Models\Client;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class ProjectProgress extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'start_date',
        'end_date',
        'status',
        'working_zone',
        'project_type_id',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_project', 'project_id', 'employee_id');
    }

    public function type()
    {
        return $this->belongsTo(ProjectType::class, 'project_type_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(ProjectItem::class, 'project_id');
    }

    public function dailyProgress()
    {
        return $this->hasManyThrough(
            DailyProgress::class,   // الجدول النهائي
            ProjectItem::class,     // الجدول الوسيط
            'project_id',           // المفتاح الأجنبي في جدول project_items اللي بيربط بـ projects
            'project_item_id',      // المفتاح الأجنبي في جدول daily_progress اللي بيربط بـ project_items
            'id',                   // المفتاح الأساسي في جدول projects
            'id'                    // المفتاح الأساسي في جدول project_items
        );
    }
}
