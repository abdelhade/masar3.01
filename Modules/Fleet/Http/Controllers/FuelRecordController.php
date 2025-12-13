<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Fleet\Http\Requests\FuelRecordRequest;
use Modules\Fleet\Models\FuelRecord;
use Modules\Fleet\Models\Vehicle;
use Modules\Fleet\Models\Trip;
use RealRashid\SweetAlert\Facades\Alert;

class FuelRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Fuel Records')->only(['index', 'show']);
        $this->middleware('permission:create Fuel Records')->only(['create', 'store']);
        $this->middleware('permission:edit Fuel Records')->only(['edit', 'update']);
        $this->middleware('permission:delete Fuel Records')->only(['destroy']);
    }

    public function index()
    {
        $fuelRecords = FuelRecord::with(['vehicle', 'trip', 'branch'])
            ->latest()
            ->paginate(15);

        return view('fleet::fuel-records.index', compact('fuelRecords'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('is_active', true)->get();
        $trips = Trip::where('status', 'in_progress')->orWhere('status', 'completed')->get();
        $branches = userBranches();

        return view('fleet::fuel-records.create', compact('vehicles', 'trips', 'branches'));
    }

    public function store(FuelRecordRequest $request)
    {
        try {
            FuelRecord::create($request->validated());
            Alert::toast(__('fleet::messages.created_successfully'), 'success');

            return redirect()->route('fleet.fuel-records.index');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $fuelRecord = FuelRecord::with(['vehicle', 'trip', 'branch'])
            ->findOrFail($id);

        return view('fleet::fuel-records.show', compact('fuelRecord'));
    }

    public function edit($id)
    {
        $fuelRecord = FuelRecord::findOrFail($id);
        $vehicles = Vehicle::where('is_active', true)->get();
        $trips = Trip::where('status', 'in_progress')->orWhere('status', 'completed')->get();
        $branches = userBranches();

        return view('fleet::fuel-records.edit', compact('fuelRecord', 'vehicles', 'trips', 'branches'));
    }

    public function update(FuelRecordRequest $request, FuelRecord $fuelRecord)
    {
        try {
            $fuelRecord->update($request->validated());
            Alert::toast(__('fleet::messages.updated_successfully'), 'success');

            return redirect()->route('fleet.fuel-records.index');
        } catch (\Exception) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $fuelRecord = FuelRecord::findOrFail($id);
            $fuelRecord->delete();
            Alert::toast(__('fleet::messages.deleted_successfully'), 'success');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');
        }

        return redirect()->route('fleet.fuel-records.index');
    }
}
