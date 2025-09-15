<?php

namespace Modules\CRM\Models;

use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    protected $table = 'lead_statuses';

    protected $fillable = [
        'name',
        'color',
        'order_column',
        'branch_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public static function ordered()
    {
        return self::orderBy('order_column')->get();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'status_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
