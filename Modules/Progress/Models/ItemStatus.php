<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemStatus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'color',
        'icon',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get all project items with this status
     */
    public function projectItems()
    {
        return $this->hasMany(ProjectItem::class);
    }

    /**
     * Scope for active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered statuses
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}

