<?php

declare(strict_types=1);

namespace Modules\Authorization\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use LogsActivity;

    protected $fillable = ['name', 'guard_name', 'category', 'option_type', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'category', 'description'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "تم {$eventName} الصلاحية");
    }
}
