<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_template_id',
        'work_item_id',
        'default_quantity',
        'estimated_daily_qty',
        'duration',
        'predecessor',
        'dependency_type',
        'lag',
        'notes',
        'item_order',
        'subproject_name', // ✅ مثل ProjectItem
    ];

    protected $casts = [
        'default_quantity' => 'decimal:2',
   'estimated_daily_qty' => 'decimal:2',
        'duration' => 'integer',
        'lag' => 'integer',

    ];

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function template()
    {
        return $this->belongsTo(ProjectTemplate::class);
    }
    public function predecessorItem()
    {
        // Since predecessor is stored as work_item_id, we need to find the template item with that work_item_id within the same template
        return $this->hasOne(TemplateItem::class, 'work_item_id', 'predecessor')
                    ->where('project_template_id', $this->project_template_id);
    }
    
    public function predecessorWorkItem()
    {
        return $this->belongsTo(WorkItem::class, 'predecessor');
    }

    /**
     * Get subproject relationship (like ProjectItem)
     */
    public function subproject()
    {
        return $this->belongsTo(Subproject::class, 'subproject_name', 'name')
            ->where('project_template_id', $this->project_template_id);
    }

    /**
     * Get template items for the subproject
     */
    public function templateItems(): HasMany
    {
        return $this->hasMany(TemplateItem::class, 'subproject_name', 'name')
            ->where('project_template_id', $this->project_template_id);
    }
}
