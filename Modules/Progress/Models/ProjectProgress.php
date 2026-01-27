<?php

namespace Modules\Progress\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounts\Models\AccHead;
use Modules\HR\Models\Employee;

class ProjectProgress extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    protected $guarded = ['id'];

    protected $casts = [
        'settings' => 'array',
        'holidays' => 'array', // Assuming holidays is also stored as string list but maybe useful as array access if changed later, but safe to add settings cast.
        // Actually holidays is string like "5,6" in controller, so explicit cast might break if not handled carefully.
        // Let's stick to just settings for now to minimize risk.
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

    public function account()
    {
        return $this->belongsTo(AccHead::class, 'account_id');
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'project_user', 'project_id', 'user_id')->withTimestamps();
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

    public function getCompletionPercentageAttribute()
    {
        $filled = 0;
        $total = 6;

        if (! empty($this->name)) {
            $filled++;
        }
        if (! empty($this->client_id)) {
            $filled++;
        }
        if (! empty($this->project_type_id)) {
            $filled++;
        }
        if (! empty($this->start_date)) {
            $filled++;
        }
        if (! empty($this->working_zone)) {
            $filled++;
        }

        // Check items count (use attribute if eager loaded, otherwise query)
        if ($this->getAttribute('items_count') !== null) {
            if ($this->items_count > 0) {
                $filled++;
            }
        } else {
            if ($this->items()->count() > 0) {
                $filled++;
            }
        }

        return round(($filled / $total) * 100);
    }
}
