<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $guarded = [];
    public function operHead()
    {
        return $this->belongsTo(OperHead::class, 'cost_center');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
