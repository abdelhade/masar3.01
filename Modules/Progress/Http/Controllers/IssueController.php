<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Progress\Models\Issue;
use Modules\Progress\Models\IssueAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use Modules\Progress\Models\ProjectProgress;

class IssueController extends Controller
{
    // 
       public function __construct()
    {
        
        $this->middleware('can:view progress-issues')->only(['index' ,'show' ,'kanban']);
        $this->middleware('can:create progress-issues')->only('store' );
        $this->middleware('can:edit progress-issues')->only(['edit' ,'update']);
        $this->middleware('can:delete progress-issues')->only(['destroy' ,'destroyAttachment']);

    }       
    public function index(Request $request)
    {
        $query = Issue::query()->with(['project', 'assignee', 'reporter']);

        // Filtering
        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority') && $request->priority !== 'All') {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('project_id') && $request->project_id !== 'All') {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('assigned_to') && $request->assigned_to !== 'All') {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }
        if ($request->filled('module')) {
            $query->where('module', 'like', '%' . $request->module . '%');
        }
        // Date Range
        if ($request->filled('deadline_from')) {
            $query->whereDate('deadline', '>=', $request->deadline_from);
        }
        if ($request->filled('deadline_to')) {
            $query->whereDate('deadline', '<=', $request->deadline_to);
        }

        $issues = $query->latest()->paginate(20);

        // Stats
        $stats = [
            'open' => Issue::where('status', 'New')->count(), // Assuming 'New' is Open
            'closed' => Issue::where('status', 'Closed')->count(),
            'overdue' => Issue::where('deadline', '<', now())->where('status', '!=', 'Closed')->count(),
            'in_progress' => Issue::where('status', 'In Progress')->count(),
        ];

        $users = User::all();
        $projects = ProjectProgress::all();
        
        return view('progress::issues.index', compact('issues', 'stats', 'users', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'title' => 'required|string|max:255',
            'priority' => 'required',
            'status' => 'required',
            'attachments.*' => 'nullable|file|max:10240' // 10MB Max
        ]);

        $issue = Issue::create($request->except('attachments') + ['reporter_id' => auth()->id()]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('issues/attachments', 'public');
                $issue->attachments()->create([
                    'user_id' => auth()->id(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        Alert::toast(__('general.created_successfully'), 'success');
        return redirect()->back();
    }

    public function show(Issue $issue)
    {
        $issue->load(['project', 'assignee', 'reporter', 'attachments']);
        return view('progress::issues.show', compact('issue'));
    }

    public function edit(Issue $issue)
    {
        $users = User::all();
        $projects = ProjectProgress::all();
        return view('progress::issues.edit', compact('issue', 'users', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $issue = Issue::findOrFail($id);
        
        $issue->update($request->except('attachments'));

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('issues/attachments', 'public');
                $issue->attachments()->create([
                    'user_id' => auth()->id(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        Alert::toast(__('general.updated_successfully'), 'success');
        return redirect()->route('issues.index');
    }

    public function destroyAttachment(\Modules\Progress\Models\IssueAttachment $attachment)
    {
        if(Storage::disk('public')->exists($attachment->file_path)){
            Storage::disk('public')->delete($attachment->file_path);
        }
        $attachment->delete();
        Alert::toast(__('general.deleted_successfully'), 'success');
        return redirect()->back();
    }

    public function destroy(Issue $issue)
    {
        $issue->delete();
        Alert::toast(__('general.deleted_successfully'), 'success');
        return redirect()->back();
    }

    public function storeComment(Request $request, Issue $issue)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        $issue->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment
        ]);

        Alert::toast(__('general.created_successfully'), 'success');
        return redirect()->back();
    }

    public function destroyComment(\Modules\Progress\Models\IssueComment $comment)
    {
        if($comment->user_id == auth()->id() || auth()->user()->hasRole('admin')) {
             $comment->delete();
             Alert::toast(__('general.deleted_successfully'), 'success');
        } else {
             Alert::toast('Unauthorized', 'error');
        }
        
        return redirect()->back();
    }
    public function kanban(Request $request)
    {
        $query = Issue::query()->with(['project', 'assignee', 'reporter']);

        // Filtering
        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority') && $request->priority !== 'All') {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('project_id') && $request->project_id !== 'All') {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('assigned_to') && $request->assigned_to !== 'All') {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }
        if ($request->filled('module')) {
            $query->where('module', 'like', '%' . $request->module . '%');
        }
        // Date Range
        if ($request->filled('deadline_from')) {
            $query->whereDate('deadline', '>=', $request->deadline_from);
        }
        if ($request->filled('deadline_to')) {
            $query->whereDate('deadline', '<=', $request->deadline_to);
        }
        
        $issues = $query->latest()->get();
        
        $users = User::all();
        $projects = ProjectProgress::all();

        return view('progress::issues.kanban', compact('issues', 'users', 'projects'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'issue_id' => 'required|exists:issues,id',
            'status' => 'required|in:New,In Progress,Testing,Closed'
        ]);

        $issue = Issue::find($request->issue_id);
        $issue->status = $request->status;
        $issue->save();

        return response()->json(['success' => true]);
    }
}
