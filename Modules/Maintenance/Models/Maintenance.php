<?php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Maintenance\Enums\MaintenanceStatus;


class Maintenance extends Model
{
    protected $fillable = [
        'client_name',
        'client_phone',
        'item_name',
        'item_number',
        'service_type_id',
        'status',
    ];

    protected $casts = [
        'status' => MaintenanceStatus::class,
    ];

    public function type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}
