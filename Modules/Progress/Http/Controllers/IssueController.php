<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\Issue;
use Modules\Progress\Models\IssueComment;
use Modules\Progress\Models\IssueAttachment;
use Modules\Progress\Models\ProjectProgress as Project;
use App\Models\User;
use Modules\Progress\Models\Employee;
use Modules\Progress\Http\Requests\StoreIssueRequest;
use Modules\Progress\Http\Requests\UpdateIssueRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * IssueController
 * 
 * Handles all issue management operations including CRUD, Kanban board, and statistics
 */
class IssueController extends Controller
{
    /**
     * Constructor - Set up middleware for authorization
     */
    public function __construct()
    {
        $this->middleware('can:view progress-issues')->only(['index', 'kanban', 'statistics']);
        $this->middleware('can:create progress-issues')->only(['create', 'store']);
        $this->middleware('can:edit progress-issues')->only(['edit', 'update']);
        $this->middleware('can:delete progress-issues')->only('destroy');
        $this->middleware('can:view progress-issues')->only('show');
    }

    /**
     * Display a listing of issues with filters and pagination
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Issue::with(['project', 'reporter', 'assignedUser', 'comments', 'attachments']);

        // Filter by user's accessible projects (non-draft only)
        $userProjects = $this->getUserProjects($user);
        if ($userProjects->isNotEmpty()) {
            $query->whereIn('project_id', $userProjects->pluck('id'));
        } else {
            // If user has no projects, show no issues
            $query->whereRaw('1 = 0');
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('module')) {
            $query->where('module', 'like', '%' . $request->module . '%');
        }

        if ($request->filled('deadline_from')) {
            $query->where('due_date', '>=', $request->deadline_from);
        }

        if ($request->filled('deadline_to')) {
            $query->where('due_date', '<=', $request->deadline_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('module', 'like', '%' . $search . '%');
            });
        }

        // Get filter options - Filter projects based on user role and exclude drafts
        $user = Auth::user();
        $projects = $this->getUserProjects($user);

        // Get statistics filtered by user's projects
        $statistics = $this->getStatistics($user);
        $users = User::orderBy('name')->get();
        $modules = Issue::whereNotNull('module')
            ->distinct()
            ->pluck('module')
            ->filter()
            ->sort()
            ->values();

        // Paginate results
        $issues = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('progress::issues.index', compact('issues', 'statistics', 'projects', 'users', 'modules'));
    }

    /**
     * Display Kanban board view
     */
    public function kanban(Request $request)
    {
        $user = Auth::user();
        $query = Issue::with(['project', 'reporter', 'assignedUser']);

        // Filter by user's accessible projects (non-draft only)
        $userProjects = $this->getUserProjects($user);
        if ($userProjects->isNotEmpty()) {
            $query->whereIn('project_id', $userProjects->pluck('id'));
        } else {
            // If user has no projects, show no issues
            $query->whereRaw('1 = 0');
        }

        // Apply filters (same as index)
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Group issues by status
        $issuesByStatus = [
            'New' => (clone $query)->where('status', 'New')->orderBy('priority', 'desc')->orderBy('created_at', 'desc')->get(),
            'In Progress' => (clone $query)->where('status', 'In Progress')->orderBy('priority', 'desc')->orderBy('updated_at', 'desc')->get(),
            'Testing' => (clone $query)->where('status', 'Testing')->orderBy('priority', 'desc')->orderBy('updated_at', 'desc')->get(),
            'Closed' => (clone $query)->where('status', 'Closed')->orderBy('updated_at', 'desc')->get(),
        ];

        // Get filter options - Filter projects based on user role and exclude drafts
        $user = Auth::user();
        $projects = $this->getUserProjects($user);
        $users = User::orderBy('name')->get();

        return view('progress::issues.kanban', compact('issuesByStatus', 'projects', 'users'));
    }

    /**
     * Get statistics for dashboard
     */
    public function statistics()
    {
        $statistics = $this->getStatistics();
        
        return response()->json($statistics);
    }

    /**
     * Show the form for creating a new issue
     */
    public function create()
    {
        $user = Auth::user();
        $projects = $this->getUserProjects($user);
        $users = User::orderBy('name')->get();

        return view('progress::issues.create', compact('projects', 'users'));
    }

    /**
     * Store a newly created issue
     */
    public function store(StoreIssueRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['reporter_id'] = Auth::id();
            $validated['status'] = $validated['status'] ?? 'New';

            $issue = Issue::create($validated);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                $this->storeAttachments($issue, $request->file('attachments'));
            }

            return redirect()
                ->route('progress.issues.show', $issue)
                ->with('success', __('general.issue_created_successfully'));

        } catch (\Exception $e) {
            Log::error('Error creating issue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', __('general.error_creating_issue') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified issue
     */
    public function show(Issue $issue)
    {
        $issue->load([
            'project',
            'reporter',
            'assignedUser',
            'comments.user',
            'attachments.user'
        ]);

        return view('progress::issues.show', compact('issue'));
    }

    /**
     * Show the form for editing the specified issue
     */
    public function edit(Issue $issue)
    {
        $user = Auth::user();
        $projects = $this->getUserProjects($user);
        $users = User::orderBy('name')->get();

        return view('progress::issues.edit', compact('issue', 'projects', 'users'));
    }

    /**
     * Update the specified issue
     */
    public function update(UpdateIssueRequest $request, Issue $issue)
    {
        try {
            $validated = $request->validated();

            $issue->update($validated);

            // Handle new file attachments
            if ($request->hasFile('attachments')) {
                $this->storeAttachments($issue, $request->file('attachments'));
            }

            return redirect()
                ->route('progress.issues.show', $issue)
                ->with('success', __('general.issue_updated_successfully'));

        } catch (\Exception $e) {
            Log::error('Error updating issue', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', __('general.error_updating_issue') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified issue
     */
    public function destroy(Issue $issue)
    {
        try {
            // Delete all attachments
            foreach ($issue->attachments as $attachment) {
                if (Storage::exists($attachment->file_path)) {
                    Storage::delete($attachment->file_path);
                }
            }

            $issue->delete();

            return redirect()
                ->route('progress.issues.index')
                ->with('success', __('general.issue_deleted_successfully'));

        } catch (\Exception $e) {
            Log::error('Error deleting issue', [
                'issue_id' => $issue->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', __('general.error_deleting_issue') . ': ' . $e->getMessage());
        }
    }

    /**
     * Add a comment to an issue
     */
    public function addComment(Request $request, Issue $issue)
    {
        $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        $comment = IssueComment::create([
            'issue_id' => $issue->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return back()->with('success', __('general.comment_added_successfully'));
    }

    /**
     * Delete a comment
     */
    public function deleteComment(IssueComment $comment)
    {
        // Only allow comment owner or issue reporter to delete
        if ($comment->user_id !== Auth::id() && $comment->issue->reporter_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        return back()->with('success', __('general.comment_deleted_successfully'));
    }

    /**
     * Download an attachment
     */
    public function downloadAttachment(IssueAttachment $attachment)
    {
        // Check if user has access to the issue
        $user = Auth::user();
        $issue = $attachment->issue;
        
        // Check if user can access this issue's project
        $userProjects = $this->getUserProjects($user);
        if (!$userProjects->contains('id', $issue->project_id)) {
            abort(403, 'Unauthorized');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment(IssueAttachment $attachment)
    {
        // Only allow attachment owner or issue reporter to delete
        if ($attachment->user_id !== Auth::id() && $attachment->issue->reporter_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }

        $attachment->delete();

        return back()->with('success', __('general.attachment_deleted_successfully'));
    }

    /**
     * Update issue status (for Kanban board drag & drop)
     */
    public function updateStatus(Request $request, Issue $issue)
    {
        $request->validate([
            'status' => 'required|in:New,In Progress,Testing,Closed',
        ]);

        $issue->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => __('general.status_updated_successfully')
        ]);
    }

    /**
     * Store file attachments for an issue
     */
    private function storeAttachments(Issue $issue, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('issues/attachments', 'public');
            
            IssueAttachment::create([
                'issue_id' => $issue->id,
                'user_id' => Auth::id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Get statistics for issues filtered by user's accessible projects
     */
    private function getStatistics(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        
        // Get user's accessible projects (non-draft only)
        $userProjects = $this->getUserProjects($user);
        $projectIds = $userProjects->pluck('id')->toArray();
        
        // Helper function to build base query
        $getBaseQuery = function() use ($projectIds) {
            $query = Issue::whereHas('project', function ($q) {
                $q->where('is_draft', false);
            });
            
            if (!empty($projectIds)) {
                $query->whereIn('project_id', $projectIds);
            } else {
                $query->whereRaw('1 = 0');
            }
            
            return $query;
        };
        
        return [
            'total_open' => (clone $getBaseQuery())->open()->count(),
            'total_closed' => (clone $getBaseQuery())->byStatus('Closed')->count(),
            'by_status' => [
                'New' => (clone $getBaseQuery())->byStatus('New')->count(),
                'In Progress' => (clone $getBaseQuery())->byStatus('In Progress')->count(),
                'Testing' => (clone $getBaseQuery())->byStatus('Testing')->count(),
                'Closed' => (clone $getBaseQuery())->byStatus('Closed')->count(),
            ],
            'by_priority' => [
                'Low' => (clone $getBaseQuery())->byPriority('Low')->count(),
                'Medium' => (clone $getBaseQuery())->byPriority('Medium')->count(),
                'High' => (clone $getBaseQuery())->byPriority('High')->count(),
                'Urgent' => (clone $getBaseQuery())->byPriority('Urgent')->count(),
            ],
            'overdue' => (clone $getBaseQuery())
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->where('status', '!=', 'Closed')
                ->count(),
            'by_project' => (clone $getBaseQuery())
                ->selectRaw('project_id, COUNT(*) as count')
                ->groupBy('project_id')
                ->with('project:id,name')
                ->get()
                ->map(function($item) {
                    return [
                        'project_name' => $item->project->name ?? 'N/A',
                        'count' => $item->count
                    ];
                }),
        ];
    }

    /**
     * Get projects based on user role and permissions
     * - Excludes draft projects
     * - Returns only user's projects for employees
     * - Returns all non-draft projects for admin/manager
     */
    private function getUserProjects(User $user)
    {
        // Start with non-draft projects only
        $query = Project::where('is_draft', false)->orderBy('name');

        // If user is not admin or manager, filter by user's projects
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            // Get user's employee record
            $employee = Employee::where('user_id', $user->id)->first();
            
            if ($employee) {
                // Get only projects assigned to this employee
                $query->whereHas('employees', function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id);
                });
            } else {
                // If no employee record, return empty collection
                return collect([]);
            }
        }

        return $query->get();
    }
}
