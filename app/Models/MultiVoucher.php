<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiVoucher extends Model
{
    protected $table = 'operhead';
    protected $guarded = [];
    public function scopeReceipts($query)
    {
        return $query->whereIn('pro_type', [32, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55]);
    }

    public function type()
    {
        return $this->belongsTo(ProType::class, 'pro_type');
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
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user');
    }
    public function journalHead()
    {
        return $this->hasOne(JournalHead::class, 'op_id', 'id');
    }
}
