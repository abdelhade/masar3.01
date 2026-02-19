<?php

declare(strict_types=1);

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Settings\Models\Category;

class KitchenPrinterStation extends Model
{
    protected $fillable = [
        'name',
        'printer_name',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the categories associated with this printer station.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_printer_station',
            'printer_station_id',
            'category_id'
        );
    }

    /**
     * Get all print jobs for this printer station.
     */
    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class, 'printer_station_id');
    }
}
