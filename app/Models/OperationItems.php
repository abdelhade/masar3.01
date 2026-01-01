<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class OperationItems extends Model
{
    protected $table = 'operation_items';

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function operhead()
    {
        return $this->belongsTo(OperHead::class, 'pro_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id'); // مهم: تأكد من اسم العمود
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function fat_unit()
    {
        return $this->belongsTo(Unit::class, 'fat_unit_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
