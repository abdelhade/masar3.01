<?php

namespace Modules\CRM\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'crm_return_items';

    protected $fillable = [
        'return_id',
        'item_id',
        'quantity',
        'unit_price',
        'total_price',
        'item_condition',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($returnItem) {
            $returnItem->total_price = $returnItem->quantity * $returnItem->unit_price;
        });
    }

    public function returnOrder()
    {
        return $this->belongsTo(ReturnOrder::class, 'return_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
