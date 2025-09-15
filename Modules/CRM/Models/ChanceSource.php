<?php

namespace Modules\CRM\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class ChanceSource extends Model
{
    protected $fillable = [
        'title',
        'branch_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'source_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
