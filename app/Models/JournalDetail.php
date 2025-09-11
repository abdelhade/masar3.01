<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalDetail extends Model
{
    protected $table = 'journal_details';

    protected $guarded = [];

    public $timestamps = false;

    public function accHead()
    {
        return $this->belongsTo(AccHead::class, 'account_id');
    }
    public function head()
    {
        return $this->belongsTo(JournalHead::class,'journal_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center');
    }

    public function operHead()
    {
        return $this->belongsTo(OperHead::class, 'op_id');
    }
}
