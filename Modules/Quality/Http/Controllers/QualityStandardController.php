<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\QualityStandard;
use App\Models\Item;

class QualityStandardController extends Controller
{
        public function __construct()
    {
        $this->middleware('can:view standards')->only(['index' , 'show']);
        $this->middleware('can:create standards')->only(['create', 'store']);
        $this->middleware('can:edit standards')->only(['edit', 'update']);
        $this->middleware('can:delete standards')->only(['destroy']);
    }
    public function index()
    {
        $standards = QualityStandard::with(['item', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => QualityStandard::count(),
            'active' => QualityStandard::where('is_active', true)->count(),
            'inactive' => QualityStandard::where('is_active', false)->count(),
        ];

        return view('quality::standards.index', compact('standards', 'stats'));
    }

    public function create()
    {
        $items = Item::where('isdeleted', 0)->get();
        return view('quality::standards.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'standard_code' => 'required|string|unique:quality_standards,standard_code',
            'standard_name' => 'required|string',
            'description' => 'nullable|string',
            'test_method' => 'nullable|string',
            'sample_size' => 'required|min:1',
            'test_frequency' => 'required|in:per_batch,daily,weekly,monthly',
            'acceptance_threshold' => 'required|numeric|min:0|max:100',
            'max_defects_allowed' => 'required|min:0',
            'specifications' => 'nullable|array',
            'chemical_properties' => 'nullable|array',
            'physical_properties' => 'nullable|array',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ], [

            'max_defects_allowed.integer' => 'حقل الحد الأقصى للعيوب يجب أن يكون عدداً صحيحاً.',
            'acceptance_threshold.numeric' => 'حقل عتبة القبول يجب أن يكون رقماً.',
            'acceptance_threshold.min' => 'حقل عتبة القبول يجب أن يكون على الأقل 0.',
            'acceptance_threshold.max' => 'حقل عتبة القبول يجب أن لا يتجاوز 100.',
        ]);

        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['created_by'] = auth()->id();

        $standard = QualityStandard::create($validated);

        return redirect()->route('quality.standards.show', $standard)
            ->with('success', 'تم إنشاء معيار الجودة بنجاح');
    }

    public function show(QualityStandard $standard)
    {
        $standard->load(['item', 'inspections']);

        return view('quality::standards.show', compact('standard'));
    }

    public function edit(QualityStandard $standard)
    {
        $items = Item::where('isdeleted', 0)->get();
        return view('quality::standards.edit', compact('standard', 'items'));
    }

    public function update(Request $request, QualityStandard $standard)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'standard_code' => 'required|string|unique:quality_standards,standard_code,' . $standard->id,
            'standard_name' => 'required|string',
            'description' => 'nullable|string',
            'test_method' => 'nullable|string',
            'sample_size' => 'required|min:1',
            'test_frequency' => 'required|in:per_batch,daily,weekly,monthly',
            'acceptance_threshold' => 'required|numeric|min:0|max:100',
            'max_defects_allowed' => 'required|min:0',
            'specifications' => 'nullable|array',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ], [


            'max_defects_allowed.integer' => 'حقل الحد الأقصى للعيوب يجب أن يكون عدداً صحيحاً.',
            'acceptance_threshold.numeric' => 'حقل عتبة القبول يجب أن يكون رقماً.',
            'acceptance_threshold.min' => 'حقل عتبة القبول يجب أن يكون على الأقل 0.',
            'acceptance_threshold.max' => 'حقل عتبة القبول يجب أن لا يتجاوز 100.',
        ]);

        $validated['updated_by'] = auth()->id();
        $standard->update($validated);

        return redirect()->route('quality.standards.show', $standard)
            ->with('success', 'تم تحديث معيار الجودة بنجاح');
    }

    public function destroy(QualityStandard $standard)
    {
        $standard->delete();

        return redirect()->route('quality.standards.index')
            ->with('success', 'تم حذف المعيار بنجاح');
    }
}

