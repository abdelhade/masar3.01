<?php

namespace Modules\MyResources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceStatus extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ResourceStatusHistory::class, 'new_status_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name_ar');
    }
}

