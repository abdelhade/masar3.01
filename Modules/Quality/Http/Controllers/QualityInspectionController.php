<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\QualityInspection;
use App\Models\Item;
use Modules\Accounts\Models\AccHead;

class QualityInspectionController extends Controller
{
    public function index()
    {
        $inspections = QualityInspection::with(['item', 'inspector', 'supplier'])
            ->orderBy('inspection_date', 'desc')
            ->paginate(20);

        return view('quality::inspections.index', compact('inspections'));
    }

    public function create()
    {
        $items = Item::where('isdeleted', 0)->get();
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();
        
        return view('quality::inspections.create', compact('items', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'inspection_type' => 'required|in:receiving,in_process,final,random,customer_complaint',
            'inspection_date' => 'required|date',
            'quantity_inspected' => 'required|numeric|min:0',
            'pass_quantity' => 'required|numeric|min:0',
            'fail_quantity' => 'required|numeric|min:0',
            'result' => 'required|in:pass,fail,conditional',
            'action_taken' => 'required',
        ]);

        $validated['inspector_id'] = auth()->id();
        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'completed';

        $inspection = QualityInspection::create($validated);

        return redirect()->route('quality.inspections.show', $inspection)
            ->with('success', 'تم إنشاء الفحص بنجاح');
    }

    public function show(QualityInspection $inspection)
    {
        $inspection->load(['item', 'inspector', 'supplier', 'qualityStandard']);
        
        return view('quality::inspections.show', compact('inspection'));
    }

    public function edit(QualityInspection $inspection)
    {
        $items = Item::where('isdeleted', 0)->get();
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();
        
        return view('quality::inspections.edit', compact('inspection', 'items', 'suppliers'));
    }

    public function update(Request $request, QualityInspection $inspection)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'inspection_type' => 'required',
            'inspection_date' => 'required|date',
            'quantity_inspected' => 'required|numeric|min:0',
            'pass_quantity' => 'required|numeric|min:0',
            'fail_quantity' => 'required|numeric|min:0',
            'result' => 'required|in:pass,fail,conditional',
            'action_taken' => 'required',
        ]);

        $validated['updated_by'] = auth()->id();
        $inspection->update($validated);

        return redirect()->route('quality.inspections.show', $inspection)
            ->with('success', 'تم تحديث الفحص بنجاح');
    }

    public function destroy(QualityInspection $inspection)
    {
        $inspection->delete();

        return redirect()->route('quality.inspections.index')
            ->with('success', 'تم حذف الفحص بنجاح');
    }
}

