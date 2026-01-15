<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class WorkItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'unit',
        'description',
        'category_id',
        'order'
    ];

    public function category()
    {
        return $this->belongsTo(WorkItemCategory::class, 'category_id');
    }

    public function projectItems(): HasMany
    {
        return $this->hasMany(ProjectItem::class);
    }

    // العلاقة مع TemplateItem (بنود التيمبليت)
    public function templateItems(): HasMany
    {
        return $this->hasMany(TemplateItem::class);
    }
}
