<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\Progress\Models\ProjectProgress as Project;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'position',
        'phone',
        'email',
        'user_id'
    ];


public function projects()
{
    return $this->belongsToMany(Project::class, 'employee_project', 'employee_id', 'project_id');
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyProgress()
    {
        return $this->hasMany(DailyProgress::class);
    }
}
