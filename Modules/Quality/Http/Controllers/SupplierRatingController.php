<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\SupplierRating;
use Modules\Quality\Models\QualityInspection;
use Modules\Quality\Models\NonConformanceReport;
use Modules\Accounts\Models\AccHead;
use Carbon\Carbon;

class SupplierRatingController extends Controller
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
        $ratings = SupplierRating::with(['supplier', 'ratedBy'])
            ->orderBy('rating_date', 'desc')
            ->paginate(20);

        $stats = [
            'total_suppliers' => AccHead::where('code', 'like', '2101%')->count(),
            'excellent' => SupplierRating::where('rating', 'excellent')->count(),
            'good' => SupplierRating::where('rating', 'good')->count(),
            'poor' => SupplierRating::whereIn('rating', ['poor', 'unacceptable'])->count(),
        ];

        return view('quality::suppliers.index', compact('ratings', 'stats'));
    }

    public function create()
    {
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

        return view('quality::suppliers.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:acc_head,id',
            'period_type' => 'required|in:monthly,quarterly,annual',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'quality_score' => 'required|numeric|min:0|max:100',
            'delivery_score' => 'required|numeric|min:0|max:100',
            'documentation_score' => 'required|numeric|min:0|max:100',
        ]);

        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['rating_date'] = now();
        $validated['rated_by'] = auth()->id();

        // Auto-calculate metrics
        $supplierId = $validated['supplier_id'];
        $periodStart = Carbon::parse($validated['period_start']);
        $periodEnd = Carbon::parse($validated['period_end']);

        // Quality metrics from inspections
        $inspections = QualityInspection::where('supplier_id', $supplierId)
            ->whereBetween('inspection_date', [$periodStart, $periodEnd])
            ->get();

        $validated['total_inspections'] = $inspections->count();
        $validated['passed_inspections'] = $inspections->where('result', 'pass')->count();
        $validated['failed_inspections'] = $inspections->where('result', 'fail')->count();

        // NCR metrics
        $ncrs = NonConformanceReport::whereHas('inspection', function($q) use ($supplierId) {
            $q->where('supplier_id', $supplierId);
        })->whereBetween('detected_date', [$periodStart, $periodEnd])->get();

        $validated['ncrs_raised'] = $ncrs->count();
        $validated['critical_ncrs'] = $ncrs->where('severity', 'critical')->count();
        $validated['major_ncrs'] = $ncrs->where('severity', 'major')->count();
        $validated['minor_ncrs'] = $ncrs->where('severity', 'minor')->count();

        // Add default values for missing fields
        $validated['total_deliveries'] = 0;
        $validated['on_time_deliveries'] = 0;
        $validated['certificates_required'] = 0;
        $validated['certificates_received'] = 0;
        $validated['supplier_status'] = 'approved';

        $rating = SupplierRating::create($validated);

        return redirect()->route('quality.suppliers.show', $rating)
            ->with('success', 'تم إنشاء تقييم المورد بنجاح');
    }

    public function show(SupplierRating $supplier)
    {
        $supplier->load(['supplier', 'ratedBy', 'approvedBy']);

        return view('quality::suppliers.show', ['rating' => $supplier]);
    }

    public function edit(SupplierRating $supplier)
    {
        return view('quality::suppliers.edit', ['rating' => $supplier]);
    }

    public function update(Request $request, SupplierRating $supplier)
    {
        $validated = $request->validate([
            'quality_score' => 'required|numeric|min:0|max:100',
            'delivery_score' => 'required|numeric|min:0|max:100',
            'documentation_score' => 'required|numeric|min:0|max:100',
        ]);

        $supplier->update($validated);

        return redirect()->route('quality.suppliers.show', $supplier)
            ->with('success', 'تم تحديث التقييم بنجاح');
    }

    public function destroy(SupplierRating $supplier)
    {
        $supplier->delete();

        return redirect()->route('quality.suppliers.index')
            ->with('success', 'تم حذف التقييم بنجاح');
    }
}

