<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Modules\Accounts\Models\AccHead;
use Modules\Settings\Models\Currency;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'operhead';
    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function scopeReceipts($query)
    {
        return $query->whereIn('pro_type', [3, 4, 5, 6]);
    }

    public function type()
    {
        return $this->belongsTo(\App\Models\ProType::class, 'pro_type');
    }

    public function account1()
    {
        return $this->belongsTo(AccHead::class, 'acc1');
    }

    public function account2()
    {
        return $this->belongsTo(AccHead::class, 'acc2');
    }

    public function emp1()
    {
        return $this->belongsTo(AccHead::class, 'emp_id');
    }

    public function emp2()
    {
        return $this->belongsTo(AccHead::class, 'emp2_id');
    }

    public function user_name()
    {
        return $this->belongsTo(User::class, 'user'); // لو عمود foreign key اسمه user
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
