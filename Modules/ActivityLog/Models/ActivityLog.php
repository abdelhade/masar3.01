<?php

declare(strict_types=1);

namespace Modules\ActivityLog\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class ActivityLog extends SpatieActivity
{
    protected $table = 'activity_log';
}
