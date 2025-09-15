<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class JournalHead extends Model
{
    protected $table = 'journal_heads';
    protected $guarded = [];
    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    // علاقة 1 إلى متعدد مع تفاصيل اليومية
    public function dets()
    {
        return $this->hasMany(JournalDetail::class, 'journal_id', 'journal_id')->orderBy('type');
    }

    public function oper()
    {
        return $this->hasOne(OperHead::class, 'id', 'op_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
