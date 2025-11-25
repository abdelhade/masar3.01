<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\QualityAudit;
use App\Models\User;

class QualityAuditController extends Controller
{
       public function __construct()
    {
        $this->middleware('can:view audits')->only(['index' , 'show']);
        $this->middleware('can:create audits')->only(['create', 'store']);
        $this->middleware('can:edit audits')->only(['edit', 'update']);
        $this->middleware('can:delete audits')->only(['destroy']);
    }
    public function index()
    {
        $audits = QualityAudit::with(['leadAuditor'])
            ->orderBy('planned_date', 'desc')
            ->paginate(20);

        $stats = [
            'total' => QualityAudit::count(),
            'planned' => QualityAudit::where('status', 'planned')->count(),
            'in_progress' => QualityAudit::where('status', 'in_progress')->count(),
            'completed' => QualityAudit::where('status', 'completed')->count(),
        ];

        return view('quality::audits.index', compact('audits', 'stats'));
    }

    public function create()
    {
        $users = User::all();
        return view('quality::audits.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_title' => 'required|string',
            'audit_type' => 'required|in:internal,external,supplier,certification,customer',

            'planned_date' => 'required|date',
            'lead_auditor_id' => 'required|exists:users,id',
            'audit_team' => 'nullable|array',
            'audit_objectives' => 'nullable|string',
            'external_auditor' => 'nullable|string',
            'external_organization' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['status'] = 'planned';
        $validated['created_by'] = auth()->id();

        $audit = QualityAudit::create($validated);

        return redirect()->route('quality.audits.show', $audit)
            ->with('success', 'تم إنشاء التدقيق بنجاح');
    }

    public function show(QualityAudit $audit)
    {
        $audit->load(['leadAuditor', 'approvedBy']);

        return view('quality::audits.show', compact('audit'));
    }

    public function edit(QualityAudit $audit)
    {
        $users = User::all();
        return view('quality::audits.edit', compact('audit', 'users'));
    }

    public function update(Request $request, QualityAudit $audit)
    {
        $validated = $request->validate([
            'audit_title' => 'required|string',
            'planned_date' => 'required|date',
            'lead_auditor_id' => 'required|exists:users,id',
            'status' => 'required',
            'total_findings' => 'nullable|integer',
            'critical_findings' => 'nullable|integer',
            'major_findings' => 'nullable|integer',
            'minor_findings' => 'nullable|integer',
            'overall_result' => 'nullable',
            'summary' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $audit->update($validated);

        return redirect()->route('quality.audits.show', $audit)
            ->with('success', 'تم تحديث التدقيق بنجاح');
    }

    public function destroy(QualityAudit $audit)
    {
        $audit->delete();

        return redirect()->route('quality.audits.index')
            ->with('success', 'تم حذف التدقيق بنجاح');
    }
}

