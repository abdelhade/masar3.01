<?php

namespace App\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class ProType extends Model
{
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
