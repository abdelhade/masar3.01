<?php

declare(strict_types=1);

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\POS\Models\KitchenPrinterStation;

class Category extends Model
{
    protected $fillable = ['name'];

    public function publicSettings(): HasMany
    {
        return $this->hasMany(PublicSetting::class);
    }

    /**
     * Get the printer stations associated with this category.
     */
    public function printerStations(): BelongsToMany
    {
        return $this->belongsToMany(
            KitchenPrinterStation::class,
            'category_printer_station',
            'category_id',
            'printer_station_id'
        );
    }
}
