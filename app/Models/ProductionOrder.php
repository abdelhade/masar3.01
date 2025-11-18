<?php

namespace App\Models;

use App\Models\Item;
use Modules\Branches\Models\Branch;
use Modules\Accounts\Models\AccHead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $table = 'production_orders';
    protected $guarded = ['id'];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'production_order_items', 'production_order_id', 'item_id')
            ->withPivot('quantity', 'note')->withTimestamps();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(AccHead::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function productionInvoice(): HasOne
    {
        return $this->hasOne(OperHead::class, 'production_order_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
