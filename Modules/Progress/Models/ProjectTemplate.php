<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'project_type_id',
        'weekly_holidays'
    ];

    protected $casts = [
        'weekly_holidays' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProjectItem::class, 'project_template_id')->orderBy('item_order');
    }

    public function subprojects(): HasMany
    {
        return $this->hasMany(Subproject::class, 'project_template_id');
    }

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class, 'project_type_id');
    }

    public function workItems()
    {
        return $this->hasManyThrough(
            WorkItem::class,      // الجدول النهائي
            ProjectItem::class,   // الجدول الوسيط
            'project_template_id', // المفتاح في جدول ProjectItem
            'id',                 // المفتاح الأساسي في جدول WorkItem
            'id',                 // المفتاح الأساسي في جدول ProjectTemplate
            'work_item_id'        // المفتاح الخارجي في جدول ProjectItem
        );
    }
}
