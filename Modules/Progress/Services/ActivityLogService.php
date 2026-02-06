<?php

namespace Modules\Progress\Services;

use Modules\Progress\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log an activity.
     */
    public static function log(
        string $description,
        ?Model $subject = null,
        ?string $event = null,
        ?array $properties = null,
        ?string $logName = null
    ): Activity {
        return Activity::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id' => Auth::id(),
            'properties' => $properties,
            'event' => $event,
        ]);
    }

    /**
     * Log a created event.
     */
    public static function created(Model $subject, ?array $properties = null, ?string $logName = null): Activity
    {
        $className = class_basename($subject);
        return self::log(
            "Created {$className}",
            $subject,
            'created',
            $properties,
            $logName
        );
    }

    /**
     * Log an updated event.
     */
    public static function updated(Model $subject, ?array $properties = null, ?string $logName = null): Activity
    {
        $className = class_basename($subject);
        return self::log(
            "Updated {$className}",
            $subject,
            'updated',
            $properties,
            $logName
        );
    }

    /**
     * Log a deleted event.
     */
    public static function deleted(Model $subject, ?array $properties = null, ?string $logName = null): Activity
    {
        $className = class_basename($subject);
        return self::log(
            "Deleted {$className}",
            $subject,
            'deleted',
            $properties,
            $logName
        );
    }

    /**
     * Log a custom event.
     */
    public static function event(
        string $description,
        ?Model $subject = null,
        string $event = 'custom',
        ?array $properties = null,
        ?string $logName = null
    ): Activity {
        return self::log($description, $subject, $event, $properties, $logName);
    }

    /**
     * Get activities for a specific subject.
     */
    public static function getSubjectActivities(Model $subject, ?string $logName = null)
    {
        $query = Activity::forSubject($subject);
        
        if ($logName) {
            $query->inLog($logName);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Get activities caused by a specific user.
     */
    public static function getUserActivities(Model $user, ?string $logName = null)
    {
        $query = Activity::causedBy($user);
        
        if ($logName) {
            $query->inLog($logName);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Get all activities with optional filtering.
     */
    public static function getAllActivities(
        ?string $logName = null,
        ?string $event = null,
        ?int $limit = null
    ) {
        $query = Activity::query();
        
        if ($logName) {
            $query->inLog($logName);
        }
        
        if ($event) {
            $query->forEvent($event);
        }
        
        $query->orderBy('created_at', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
}
