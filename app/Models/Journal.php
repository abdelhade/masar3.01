<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Models\AccHead;

class Journal extends Model
{
    protected $table = 'operhead';

    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function scopeReceipts($query)
    {
        return $query->whereIn('pro_type', [8, 7]);
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
    public function user_id()
    {
        return $this->belongsTo(User::class, 'user'); // تأكد من اسم العمود في جدول transfers
    }
}
