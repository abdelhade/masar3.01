<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTemplate extends Model
{

    protected $fillable = ['name', 'description'];

    public function items(): HasMany
    {
        return $this->hasMany(TemplateItem::class, 'project_template_id');
    }

    public function workItems()
    {
        return $this->hasManyThrough(
            WorkItem::class,      // الجدول النهائي
            TemplateItem::class,  // الجدول الوسيط
            'project_template_id', // المفتاح في جدول TemplateItem
            'id',                 // المفتاح الأساسي في جدول WorkItem
            'id',                 // المفتاح الأساسي في جدول ProjectTemplate
            'work_item_id'        // المفتاح الخارجي في جدول TemplateItem
        );
    }
}
