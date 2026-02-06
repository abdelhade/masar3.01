<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'unit',
        'description',
        'category_id',
        'total_quantity',
        'expected_quantity_per_day',
        'duration',
        'predecessor_id',
        'lag',
        'shift',
    ];

    public function category()
    {
        return $this->belongsTo(WorkItemCategory::class, 'category_id');
    }

    // العلاقة مع البند السابق
    public function predecessorItem()
    {
        return $this->belongsTo(WorkItem::class, 'predecessor_id', 'id');
    }

    // العلاقة مع البنود التالية
    public function successorItems()
    {
        return $this->hasMany(WorkItem::class, 'predecessor_id', 'id');
    }

    // العلاقة مع ProjectItem (بنود المشروع فقط - ليس template items)
    public function projectItems(): HasMany
    {
        return $this->hasMany(ProjectItem::class)->whereNotNull('project_id');
    }

    // العلاقة مع TemplateItem (بنود التيمبليت)
    public function templateItems(): HasMany
    {
        return $this->hasMany(TemplateItem::class);
    }

}
