<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Fleet\Http\Requests\VehicleRequest;
use Modules\Fleet\Models\Vehicle;
use Modules\Fleet\Models\VehicleType;
use Modules\Shipping\Models\Driver;
use RealRashid\SweetAlert\Facades\Alert;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Vehicles')->only(['index', 'show']);
        $this->middleware('permission:create Vehicles')->only(['create', 'store']);
        $this->middleware('permission:edit Vehicles')->only(['edit', 'update']);
        $this->middleware('permission:delete Vehicles')->only(['destroy']);
    }

    public function index()
    {
        $vehicles = Vehicle::with(['vehicleType', 'driver', 'branch'])
            ->latest()
            ->paginate(15);

        return view('fleet::vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $vehicleTypes = VehicleType::where('is_active', true)->get();
        $drivers = Driver::withoutGlobalScopes()->where('is_available', true)->get();
        $branches = userBranches();

        return view('fleet::vehicles.create', compact('vehicleTypes', 'drivers', 'branches'));
    }

    public function store(VehicleRequest $request)
    {
        try {
            Vehicle::create($request->validated());
            Alert::toast(__('fleet::messages.created_successfully'), 'success');

            return redirect()->route('fleet.vehicles.index');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $vehicle = Vehicle::with(['vehicleType', 'driver', 'branch', 'trips', 'fuelRecords'])
            ->findOrFail($id);

        return view('fleet::vehicles.show', compact('vehicle'));
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicleTypes = VehicleType::where('is_active', true)->get();
        $drivers = Driver::withoutGlobalScopes()->where('is_available', true)->get();
        $branches = userBranches();

        return view('fleet::vehicles.edit', compact('vehicle', 'vehicleTypes', 'drivers', 'branches'));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        try {
            $vehicle->update($request->validated());
            Alert::toast(__('fleet::messages.updated_successfully'), 'success');

            return redirect()->route('fleet.vehicles.index');
        } catch (\Exception) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
            Alert::toast(__('fleet::messages.deleted_successfully'), 'success');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');
        }

        return redirect()->route('fleet.vehicles.index');
    }
}
