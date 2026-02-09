<?php

namespace Modules\Progress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

/**
 * Issue Model
 * 
 * Represents an issue/ticket in the project management system
 */
class Issue extends Model
{
    use SoftDeletes;

    // Priority constants
    const PRIORITY_LOW = 'Low';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_HIGH = 'High';
    const PRIORITY_URGENT = 'Urgent';

    // Status constants
    const STATUS_NEW = 'New';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_TESTING = 'Testing';
    const STATUS_CLOSED = 'Closed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'priority',
        'status',
        'reporter_id',
        'assigned_to',
        'module',
        'reproduce_steps',
        'due_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the project that owns the issue.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProgress::class, 'project_id');
    }

    /**
     * Get the user who reported the issue.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user assigned to the issue.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all comments for the issue.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(IssueComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get all attachments for the issue.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(IssueAttachment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Scope to filter issues by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter issues by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter issues by project.
     */
    public function scopeByProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope to filter issues by assigned user.
     */
    public function scopeByAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to filter issues by module.
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter issues with due date approaching.
     */
    public function scopeDeadlineApproaching($query, int $days = 7)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>=', now())
            ->where('status', '!=', self::STATUS_CLOSED);
    }

    /**
     * Scope to filter open issues (not closed).
     */
    public function scopeOpen($query)
    {
        return $query->where('status', '!=', self::STATUS_CLOSED);
    }

    /**
     * Check if issue is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status !== self::STATUS_CLOSED;
    }

    /**
     * Get priority badge color class.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'info',
            self::PRIORITY_MEDIUM => 'warning',
            self::PRIORITY_HIGH => 'danger',
            self::PRIORITY_URGENT => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get status badge color class.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'primary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_TESTING => 'info',
            self::STATUS_CLOSED => 'success',
            default => 'secondary',
        };
    }
}
