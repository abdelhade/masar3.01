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
        'estimated_daily_qty',
        'shift',
        'order',
        'item_status_id',
    ];

    public function category()
    {
        return $this->belongsTo(WorkItemCategory::class, 'category_id');
    }

    public function predecessorItem()
    {
        return $this->belongsTo(WorkItem::class, 'predecessor_id', 'id');
    }

    public function successorItems()
    {
        return $this->hasMany(WorkItem::class, 'predecessor_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(ItemStatus::class, 'item_status_id');
    }

    public function projectItems(): HasMany
    {
        return $this->hasMany(ProjectItem::class)->whereNotNull('project_id');
    }

    public function templateItems(): HasMany
    {
        return $this->hasMany(TemplateItem::class);
    }

}
