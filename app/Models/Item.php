<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ItemType;


class Item extends Model
{
    use HasFactory;
    protected $table = 'items';
    protected $guarded = ['id'];
    protected $casts = [
        'type' => ItemType::class,
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    // }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'item_units', 'item_id', 'unit_id')
            ->withPivot('u_val', 'cost')
            ->withTimestamps();
    }

    public function prices(): BelongsToMany
    {
        return $this->belongsToMany(Price::class, 'item_prices', 'item_id', 'price_id')
            ->withPivot('unit_id', 'price', 'discount', 'tax_rate')
            ->withTimestamps();
    }

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'item_notes', 'item_id', 'note_id')
            ->withPivot('note_detail_name')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('isdeleted', 0);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }


    // public function getCurrentQuantityAttribute()
    // {
    //     // حساب إجمالي الكميات الداخلة (qty_in) والخارجة (qty_out)
    //     $totalIn = OperationItems::where('item_id', $this->id)
    //         ->where('isdeleted', 0)
    //         ->sum('qty_in');

    //     $totalOut = OperationItems::where('item_id', $this->id)
    //         ->where('isdeleted', 0)
    //         ->sum('qty_out');

    //     return $totalIn - $totalOut;
    // }
}
