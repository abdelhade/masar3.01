<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subproject extends Model
{
    protected $fillable = [
        'project_id',
        'project_template_id', // ✅ للقوالب
        'name',
        'start_date',
        'end_date',
        'total_quantity',
        'unit',
        'description',
        'weight',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_quantity' => 'decimal:2',
    ];

    /**
     * Get the project that owns the subproject.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the project template that owns the subproject.
     */
    public function projectTemplate(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class);
    }

    /**
     * Get the project items for the subproject (only project items, not template items).
     */
    public function projectItems(): HasMany
    {
        return $this->hasMany(ProjectItem::class, 'subproject_name', 'name')
                    ->whereNotNull('project_id');
    }

    /**
     * Calculate totals from project items.
     */
    public function calculateTotals(): void
    {
        $items = $this->projectItems;
        
        $this->total_quantity = $items->sum('total_quantity');
        $this->start_date = $items->min('start_date');
        $this->end_date = $items->max('end_date');
        
        $this->save();
    }
}
