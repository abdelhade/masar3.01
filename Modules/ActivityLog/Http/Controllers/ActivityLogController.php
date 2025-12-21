<?php

declare(strict_types=1);

namespace Modules\ActivityLog\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ActivityLog\Models\ActivityLog;

class ActivityLogController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:view activity-logs')->only(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                ->where('causer_type', User::class);
        }

        // Filter by subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by description
        if ($request->filled('description')) {
            $query->where('description', 'like', '%'.$request->description.'%');
        }

        $activities = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();

        // Get unique subject types for filter
        $subjectTypes = ActivityLog::distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(fn ($type) => class_basename($type))
            ->unique()
            ->sort()
            ->values();

        // Get unique events for filter
        $events = ActivityLog::distinct()
            ->whereNotNull('event')
            ->pluck('event')
            ->unique()
            ->sort()
            ->values();

        return view('activitylog::index', compact('activities', 'users', 'subjectTypes', 'events'));
    }

    /**
     * Show the specified resource.
     */
    public function show(int $id)
    {
        $activity = ActivityLog::with(['causer', 'subject'])->findOrFail($id);

        return view('activitylog::show', compact('activity'));
    }
}
