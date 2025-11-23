<?php

namespace Modules\MyResources\Models;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Modules\MyResources\Enums\ResourceAssignmentStatus;
use Modules\MyResources\Enums\AssignmentType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAssignment extends Model
{
    protected $fillable = [
        'resource_id',
        'project_id',
        'assigned_by',
        'start_date',
        'end_date',
        'actual_end_date',
        'status',
        'assignment_type',
        'daily_cost',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_end_date' => 'date',
        'daily_cost' => 'decimal:2',
        'status' => ResourceAssignmentStatus::class,
        'assignment_type' => AssignmentType::class,
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', ResourceAssignmentStatus::ACTIVE);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', ResourceAssignmentStatus::SCHEDULED);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForResource($query, int $resourceId)
    {
        return $query->where('resource_id', $resourceId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }
}

