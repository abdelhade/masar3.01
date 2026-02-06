<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'project_type_id',
        'working_days',
        'daily_work_hours',
        'weekly_holidays',
        'working_zone',
    ];

    protected $casts = [
        'working_days' => 'integer',
        'daily_work_hours' => 'integer',
    ];

    /**
     * Get template items from project_items table (using project_template_id)
     */
    public function items()
    {
        return $this->hasMany(ProjectItem::class, 'project_template_id')->orderBy('item_order');
    }

    /**
     * Legacy: Get template items from template_items table (if needed)
     */
    public function templateItems()
    {
        return $this->hasMany(TemplateItem::class)->orderBy('item_order');
    }

    /**
     * Alias for items() - Get template items from project_items table
     */
    public function projectItems()
    {
        return $this->items();
    }

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class);
    }

    /**
     * Get subprojects relationship (like Project)
     */
    public function subprojects()
    {
        return $this->hasMany(Subproject::class, 'project_template_id');
    }
}
