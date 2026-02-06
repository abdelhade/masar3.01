<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounts\Models\AccHead;

class JournalDetail extends Model
{
    protected $table = 'journal_details';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function accHead()
    {
        return $this->belongsTo(AccHead::class, 'account_id');
    }
    public function head()
    {
        return $this->belongsTo(JournalHead::class, 'journal_id', 'journal_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center');
    }

    public function operHead()
    {
        return $this->belongsTo(OperHead::class, 'op_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
