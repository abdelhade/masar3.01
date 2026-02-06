<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\Activity;
use Modules\Progress\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function __construct(){
        $this->middleware('can:view activity-logs')->only(['index', 'show', 'userActivities', 'subjectActivities', 'getActivities']);
    }
    /**
     * Display a listing of the activities.
     */
    public function index(Request $request)
    {
        $query = Activity::query();

        // Filter by log name
        if ($request->filled('log_name')) {
            $query->inLog($request->log_name);
        }

        // Filter by event
        if ($request->filled('event')) {
            $query->forEvent($request->event);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->causedBy(\App\Models\User::find($request->user_id));
        }

        // Filter by subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->with(['causer', 'subject'])
                           ->orderBy('created_at', 'desc')
                           ->paginate(20);

        // Get available log names for filter
        $logNames = Activity::distinct()->pluck('log_name')->filter()->values();

        // Get available events for filter
        $events = Activity::distinct()->pluck('event')->filter()->values();

        // Get available subject types for filter
        $subjectTypes = Activity::distinct()->pluck('subject_type')->filter()->values();

        return view('progress::activity-logs.index', compact('activities', 'logNames', 'events', 'subjectTypes'));
    }

    /**
     * Display activities for a specific user.
     */
    public function userActivities(Request $request, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $user = \App\Models\User::findOrFail($userId);

        $activities = ActivityLogService::getUserActivities($user, $request->log_name);

        return view('progress::activity-logs.user-activities', compact('activities', 'user'));
    }

    /**
     * Display activities for a specific subject.
     */
    public function subjectActivities(Request $request, $subjectType, $subjectId)
    {
        $subject = $subjectType::findOrFail($subjectId);
        $activities = ActivityLogService::getSubjectActivities($subject, $request->log_name);

        return view('progress::activity-logs.subject-activities', compact('activities', 'subject'));
    }

    /**
     * Display a specific activity.
     */
    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);

        return view('progress::activity-logs.show', compact('activity'));
    }

    /**
     * Get activities as JSON for AJAX requests.
     */
    public function getActivities(Request $request)
    {
        $activities = ActivityLogService::getAllActivities(
            $request->log_name,
            $request->event,
            $request->limit ?? 50
        );

        return response()->json([
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'causer' => $activity->causer ? $activity->causer->name : 'System',
                    'subject' => $activity->subject ? class_basename($activity->subject) : null,
                    'created_at' => $activity->created_at->diffForHumans(),
                    'properties' => $activity->properties,
                ];
            })
        ]);
    }
}
