<?php
namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'start_date',
        'end_date',
        'status',
        'working_zone',
        'project_type_id',
        'working_days',
        'daily_work_hours',
        'weekly_holidays',
        'is_draft',
        'is_progress',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_progress' => 'boolean',
        'is_draft' => 'boolean',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_project', 'project_id', 'employee_id');
    }

    public function projectItems()
    {
        return $this->hasMany(ProjectItem::class)->orderBy('item_order');
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
        return $this->hasMany(ProjectItem::class)->orderBy('item_order');
    }

    /**
     * Get all issues for the project
     */
    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function subprojects()
    {
        return $this->hasMany(Subproject::class);
    }

    public function dailyProgress()
    {
        return $this->hasManyThrough(DailyProgress::class, ProjectItem::class);
    }

    /**
     * Get items grouped by category
     */
    public function getItemsGroupedByCategory()
    {
        return $this->items()
            ->with('workItem.category')
            ->get()
            ->groupBy(function($item) {
                return $item->workItem->category->name ?? __('general.uncategorized');
            });
    }

    /**
     * Calculate total duration of all items
     */
    public function getTotalDurationAttribute()
    {
        return $this->items()->sum('duration');
    }

    /**
     * Calculate total lag of all items
     */
    public function getTotalLagAttribute()
    {
        return $this->items()->sum('lag');
    }

    /**
     * Check if project has any items with notes
     */
    public function getHasNotesAttribute()
    {
        return $this->items()->whereNotNull('notes')->exists();
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
            
            // Handle items without subproject if there's remaining weight?
            // For now, we strictly follow strict subproject weights if they exist.
            // If total weight < 100, the progress might be lower than expected, which is correct for weighted systems.
            
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

    /**
     * Scope for active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for draft projects
     */
    public function scopeDraft($query)
    {
        return $query->where('is_draft', true);
    }

    /**
     * Scope for published projects
     */
    public function scopePublished($query)
    {
        return $query->where('is_draft', false);
    }

    /**
     * Reorder items based on provided order
     */
    public function reorderItems($itemOrders)
    {
        foreach ($itemOrders as $order => $itemId) {
            $this->items()->where('id', $itemId)->update(['item_order' => $order]);
        }
    }
}
