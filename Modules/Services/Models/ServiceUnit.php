<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceUnit extends Model
{
    use HasFactory;

    protected $table = 'service_units';
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => false,
    ];


    /**
     * Scope a query to only include active service units.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include inactive service units.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }

    public function services(): hasMany
    {
        return $this->hasMany(Service::class, 'service_unit_id');
    }

    protected static function newFactory()
    {
        return \Modules\Services\Database\Factories\ServiceUnitFactory::new();
    }
}
