<?php

namespace Modules\Progress\Models;

use Modules\Progress\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounts\Models\AccHead;
use Modules\HR\Models\Employee;

class ProjectProgress extends Model
{
    use SoftDeletes;

    protected $table = 'projects';

    protected static function booted(): void
    {
        static::addGlobalScope('progressOnly', function ($query) {
            $query->where('is_progress', 1);
        });
    }

    protected $guarded = ['id'];

    protected $casts = [
        'settings' => 'array',
        'is_progress' => 'boolean',
        'is_draft' => 'boolean',
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

    public function subprojects()
    {
        return $this->hasMany(Subproject::class, 'project_id');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'project_id');
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

    /**
     * Calculate overall project progress percentage
     * يحسب نسبة الإنجاز الإجمالية للمشروع بناءً على completed_quantity
     * ✅ يحسب فقط من البنود القابلة للقياس (is_measurable = true)
     * يتعامل بشكل صحيح مع البنود المكررة (كل بند له project_item_id منفصل)
     */
    public function getOverallProgressAttribute()
    {
        // ✅ فلترة البنود القابلة للقياس فقط
        $measurableItems = $this->items->filter(function ($item) {
            return $item->is_measurable ?? false;
        });

        // التحقق من وجود مشاريع فرعية بأوزان
        $subprojects = $this->subprojects;
        $hasWeightedSubprojects = $subprojects->where('weight', '>', 0)->isNotEmpty();

        if ($hasWeightedSubprojects) {
            $overallProgress = 0;
            $totalWeight = 0;

            foreach ($subprojects as $subproject) {
                // Get items for this subproject
                $subItems = $measurableItems->filter(function ($item) use ($subproject) {
                    return $item->subproject_name === $subproject->name;
                });

                if ($subItems->isEmpty()) {
                    continue;
                }

                $subTotalQty = $subItems->sum('total_quantity');
                $subCompletedQty = $subItems->sum('completed_quantity');

                $subProgress = $subTotalQty > 0 
                    ? ($subCompletedQty / $subTotalQty) * 100 
                    : 0;

                $overallProgress += $subProgress * ($subproject->weight / 100);
                $totalWeight += $subproject->weight;
            }
            
            return round($overallProgress, 1);
        }

        // Fallback to simple calculation (Classic Mode)
        $totalQuantity = $measurableItems->sum('total_quantity');
        $completedQuantity = $measurableItems->sum('completed_quantity');

        if ($totalQuantity <= 0) {
            return 0;
        }

        return round(($completedQuantity / $totalQuantity) * 100, 1);
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
