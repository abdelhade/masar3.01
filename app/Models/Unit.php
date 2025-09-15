<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Unit extends Model
{
    use HasFactory;
    protected $table = 'units';
    protected $guarded = ['id'];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_units', 'unit_id', 'item_id')
            ->withPivot('u_val', 'cost')
            ->withTimestamps();
    }

    /**
     * Get all of the barcodes for the Unit.
     */
    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class);
    }

    public function prices(): BelongsToMany
    {
        return $this->belongsToMany(Price::class, 'item_prices', 'unit_id', 'price_id')
            ->withPivot('price', 'discount', 'tax_rate')
            ->withTimestamps();
    }

}
