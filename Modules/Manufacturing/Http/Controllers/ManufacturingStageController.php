<?php

namespace Modules\Manufacturing\Http\Controllers;

use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Manufacturing\Models\ManufacturingStage;
use Modules\Manufacturing\Http\Requests\ManufacturingStageRequest;

class ManufacturingStageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Manufacturing Stages')->only(['index', 'show']);
        $this->middleware('permission:create Manufacturing Stages')->only(['create', 'store']);
        $this->middleware('permission:edit Manufacturing Stages')->only(['edit', 'update']);
        $this->middleware('permission:delete Manufacturing Stages')->only(['destroy']);
    }

    public function index()
    {
        $stages = ManufacturingStage::paginate(20);

        return view('manufacturing::manufacturing-stages.index', compact('stages'));
    }

    public function create()
    {
        $branches = userBranches();
        return view('manufacturing::manufacturing-stages.create', compact('branches'));
    }

    public function store(ManufacturingStageRequest $request)
    {
        try {
            ManufacturingStage::create($request->validated());
            Alert::toast(__('Created Successfully'), 'success');
            return redirect()->route('manufacturing.stages.index');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred while creating'), 'error');
            return redirect()->back();
        }
    }


    public function show(ManufacturingStage $manufacturingStage) {}


    public function edit(ManufacturingStage $manufacturingStage)
    {
        return view('manufacturing::manufacturing-stages.edit', compact('manufacturingStage'));
    }

    public function update(ManufacturingStageRequest $request, ManufacturingStage $manufacturingStage)
    {
        try {
            $manufacturingStage->update($request->validated());
            Alert::toast(__('Updated Successfully'), 'success');
            return redirect()->route('manufacturing.stages.index');
        } catch (\Exception ) {
            Alert::toast(__('An error occurred while updating'), 'error');
            return redirect()->back();
        }
    }

    public function destroy(ManufacturingStage $manufacturingStage)
    {
        try {
            $manufacturingStage->delete();
            Alert::toast(__('Stage Deleted Successfully'), 'success');
            return redirect()->route('manufacturing.stages.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the stage'), 'error');
            return redirect()->back();
        }
    }
}
