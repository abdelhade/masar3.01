<?php

namespace Modules\MyResources\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceStatusHistory extends Model
{
    protected $fillable = [
        'resource_id',
        'old_status_id',
        'new_status_id',
        'changed_by',
        'reason',
        'notes',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function oldStatus(): BelongsTo
    {
        return $this->belongsTo(ResourceStatus::class, 'old_status_id');
    }

    public function newStatus(): BelongsTo
    {
        return $this->belongsTo(ResourceStatus::class, 'new_status_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

