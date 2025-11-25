<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\CorrectiveAction;
use Modules\Quality\Models\NonConformanceReport;
use App\Models\User;

class CorrectiveActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view capa')->only(['index' , 'show']);
        $this->middleware('can:create capa')->only(['create', 'store']);
        $this->middleware('can:edit capa')->only(['edit', 'update']);
        $this->middleware('can:delete capa')->only(['destroy']);
    }
    public function index()
    {
        $capas = CorrectiveAction::with(['nonConformanceReport.item', 'responsiblePerson'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => CorrectiveAction::count(),
            'in_progress' => CorrectiveAction::where('status', 'in_progress')->count(),
            'completed' => CorrectiveAction::where('status', 'completed')->count(),
            'overdue' => CorrectiveAction::overdue()->count(),
        ];

        return view('quality::capa.index', compact('capas', 'stats'));
    }

    public function create()
    {
        $ncrs = NonConformanceReport::where('status', '!=', 'closed')
            ->with('item')
            ->get();
        $users = User::all();

        return view('quality::capa.create', compact('ncrs', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ncr_id' => 'required|exists:non_conformance_reports,id',
            'action_type' => 'required|in:corrective,preventive',
            'action_description' => 'required|string',
            'root_cause_analysis' => 'nullable|string',
            'preventive_measures' => 'nullable|string',
            'responsible_person' => 'required|exists:users,id',
            'planned_start_date' => 'required|date',
            'planned_completion_date' => 'required|date|after:planned_start_date',
        ]);

        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'planned';

        $capa = CorrectiveAction::create($validated);

        return redirect()->route('quality.capa.show', $capa)
            ->with('success', 'تم إنشاء الإجراء التصحيحي بنجاح');
    }

    public function show(CorrectiveAction $capa)
    {
        $capa->load(['nonConformanceReport', 'responsiblePerson', 'verifiedBy']);

        return view('quality::capa.show', compact('capa'));
    }

    public function edit(CorrectiveAction $capa)
    {
        $ncrs = NonConformanceReport::with('item')->get();
        $users = User::all();

        return view('quality::capa.edit', compact('capa', 'ncrs', 'users'));
    }

    public function update(Request $request, CorrectiveAction $capa)
    {
        $validated = $request->validate([
            'action_description' => 'required|string',
            'root_cause_analysis' => 'nullable|string',
            'preventive_measures' => 'nullable|string',
            'responsible_person' => 'required|exists:users,id',
            'planned_completion_date' => 'required|date',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'implementation_notes' => 'nullable|string',
            'status' => 'required',
        ]);

        $validated['updated_by'] = auth()->id();
        $capa->update($validated);

        return redirect()->route('quality.capa.show', $capa)
            ->with('success', 'تم تحديث الإجراء بنجاح');
    }

    public function verify(Request $request, CorrectiveAction $capa)
    {
        $request->validate([
            'verification_result' => 'required|string',
            'is_effective' => 'required|boolean',
        ]);

        $capa->update([
            'verified_by' => auth()->id(),
            'verification_date' => now(),
            'verification_result' => $request->verification_result,
            'is_effective' => $request->is_effective,
            'status' => 'verified',
        ]);

        return redirect()->route('quality.capa.show', $capa)
            ->with('success', 'تم التحقق من الإجراء بنجاح');
    }

    public function destroy(CorrectiveAction $capa)
    {
        $capa->delete();

        return redirect()->route('quality.capa.index')
            ->with('success', 'تم حذف الإجراء بنجاح');
    }
}

