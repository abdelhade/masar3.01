<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\NonConformanceReport;
use Modules\Quality\Models\QualityInspection;
use App\Models\Item;
use App\Models\User;

class NonConformanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = NonConformanceReport::with(['item', 'detectedBy', 'assignedTo'])
            ->orderBy('detected_date', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ncr_number', 'like', "%{$search}%")
                  ->orWhere('problem_description', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        $ncrs = $query->paginate(20);
        
        // Statistics for filters
        $stats = [
            'total' => NonConformanceReport::count(),
            'open' => NonConformanceReport::where('status', 'open')->count(),
            'critical' => NonConformanceReport::where('severity', 'critical')->count(),
            'overdue' => NonConformanceReport::overdue()->count(),
        ];

        return view('quality::ncr.index', compact('ncrs', 'stats'));
    }

    public function create()
    {
        $items = Item::where('isdeleted', 0)->get();
        $inspections = QualityInspection::where('result', 'fail')
            ->whereDoesntHave('nonConformanceReport')
            ->with('item')
            ->get();
        $users = User::all();

        return view('quality::ncr.create', compact('items', 'inspections', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'inspection_id' => 'nullable|exists:quality_inspections,id',
            'batch_number' => 'nullable|string',
            'affected_quantity' => 'required|numeric|min:0',
            'source' => 'required',
            'detected_date' => 'required|date',
            'problem_description' => 'required|string',
            'severity' => 'required|in:critical,major,minor',
            'estimated_cost' => 'nullable|numeric|min:0',
            'immediate_action' => 'nullable|string',
            'disposition' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
            'target_closure_date' => 'nullable|date',
            'attachments' => 'nullable|array',
        ]);

        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['detected_by'] = auth()->id();
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'open';

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ncr-attachments', 'public');
                $attachments[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
            $validated['attachments'] = $attachments;
        }

        $ncr = NonConformanceReport::create($validated);

        return redirect()->route('quality.ncr.show', $ncr)
            ->with('success', 'تم إنشاء تقرير عدم المطابقة بنجاح');
    }

    public function show(NonConformanceReport $ncr)
    {
        $ncr->load([
            'item',
            'inspection',
            'detectedBy',
            'assignedTo',
            'closedBy',
            'correctiveActions'
        ]);

        return view('quality::ncr.show', compact('ncr'));
    }

    public function edit(NonConformanceReport $ncr)
    {
        $items = Item::where('isdeleted', 0)->get();
        $users = User::all();

        return view('quality::ncr.edit', compact('ncr', 'items', 'users'));
    }

    public function update(Request $request, NonConformanceReport $ncr)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'batch_number' => 'nullable|string',
            'affected_quantity' => 'required|numeric|min:0',
            'source' => 'required',
            'detected_date' => 'required|date',
            'problem_description' => 'required|string',
            'severity' => 'required|in:critical,major,minor',
            'estimated_cost' => 'nullable|numeric|min:0',
            'immediate_action' => 'nullable|string',
            'disposition' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
            'target_closure_date' => 'nullable|date',
        ]);

        $validated['updated_by'] = auth()->id();
        $ncr->update($validated);

        return redirect()->route('quality.ncr.show', $ncr)
            ->with('success', 'تم تحديث التقرير بنجاح');
    }

    public function close(Request $request, NonConformanceReport $ncr)
    {
        $request->validate([
            'closure_notes' => 'required|string',
            'actual_closure_date' => 'required|date',
        ]);

        $ncr->update([
            'status' => 'closed',
            'closed_by' => auth()->id(),
            'closure_notes' => $request->closure_notes,
            'actual_closure_date' => $request->actual_closure_date,
        ]);

        return redirect()->route('quality.ncr.show', $ncr)
            ->with('success', 'تم إغلاق التقرير بنجاح');
    }

    public function destroy(NonConformanceReport $ncr)
    {
        $ncr->delete();

        return redirect()->route('quality.ncr.index')
            ->with('success', 'تم حذف التقرير بنجاح');
    }
}

