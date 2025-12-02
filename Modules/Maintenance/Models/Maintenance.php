<?php

namespace Modules\Maintenance\Models;

use App\Models\OperHead;
use Modules\Branches\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Modules\Maintenance\Enums\MaintenanceStatus;


class Maintenance extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => MaintenanceStatus::class,
        'date' => 'date',
        'accural_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function operHead()
    {
        return $this->hasOne(OperHead::class, 'op2');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function periodicSchedule()
    {
        return $this->belongsTo(PeriodicMaintenanceSchedule::class, 'periodic_schedule_id');
    }
}
