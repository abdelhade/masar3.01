<?php

namespace Modules\Manufacturing\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Manufacturing\Models\ManufacturingStage;
use Modules\Manufacturing\Http\Requests\ManufacturingStageRequest;
use RealRashid\SweetAlert\Facades\Alert;

class ManufacturingStageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stages = ManufacturingStage::ordered()->paginate(20);

        return view('manufacturing::manufacturing-stages.index', compact('stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = userBranches();
        return view('manufacturing::manufacturing-stages.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ManufacturingStageRequest $request)
    {
        try {
            ManufacturingStage::create($request->validated());
            Alert::toast('تم التنفيذ بنجاح', 'success');
            return redirect()->route('manufacturing.stages.index');
        } catch (\Exception) {
            Alert::toast('error', 'حدث خطأ عند التسجيل ');
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ManufacturingStage $manufacturingStage)
    {
        return view('manufacturing::manufacturing-stages.show', compact('manufacturingStage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ManufacturingStage $manufacturingStage)
    {
        return view('manufacturing::manufacturing-stages.edit', compact('manufacturingStage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ManufacturingStageRequest $request, ManufacturingStage $manufacturingStage)
    {
        // try {
        $manufacturingStage->update($request->validated());
        Alert::toast('تم التنفيذ بنجاح', 'success');

        return redirect()->route('manufacturing.stages.index');
        // } catch (\Exception) {
        //     Alert::toast('error', 'حدث خطأ عند التسجيل ');
        //     return redirect()->back();
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ManufacturingStage $manufacturingStage)
    {
        try {
            $manufacturingStage->delete();
            Alert::toast('success', 'تم حذف المرحلة بنجاح');
            return redirect()
                ->route('manufacturing.stages.index');
        } catch (\Exception) {
            Alert::toast('error', 'حدث خطأ أثناء حذف المرحلة ');
            return redirect()->back();
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(ManufacturingStage $manufacturingStage)
    {
        try {
            $manufacturingStage->update([
                'is_active' => !$manufacturingStage->is_active
            ]);
            return redirect() > back();
        } catch (\Exception) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تغيير الحالة: ');
        }
    }
}
