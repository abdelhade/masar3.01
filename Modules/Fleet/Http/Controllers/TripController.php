<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Fleet\Http\Requests\TripRequest;
use Modules\Fleet\Models\Trip;
use Modules\Fleet\Models\Vehicle;
use Modules\Shipping\Models\Driver;
use RealRashid\SweetAlert\Facades\Alert;

class TripController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Trips')->only(['index', 'show']);
        $this->middleware('permission:create Trips')->only(['create', 'store']);
        $this->middleware('permission:edit Trips')->only(['edit', 'update']);
        $this->middleware('permission:delete Trips')->only(['destroy']);
    }

    public function index()
    {
        $trips = Trip::with(['vehicle', 'driver', 'branch'])
            ->latest()
            ->paginate(15);

        return view('fleet::trips.index', compact('trips'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('is_active', true)->get();
        $drivers = Driver::where('is_available', true)->get();
        $branches = userBranches();

        return view('fleet::trips.create', compact('vehicles', 'drivers', 'branches'));
    }

    public function store(TripRequest $request)
    {
        try {
            Trip::create($request->validated());
            Alert::toast(__('fleet::messages.created_successfully'), 'success');

            return redirect()->route('fleet.trips.index');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $trip = Trip::with(['vehicle', 'driver', 'branch', 'fuelRecords'])
            ->findOrFail($id);

        return view('fleet::trips.show', compact('trip'));
    }

    public function edit($id)
    {
        $trip = Trip::findOrFail($id);
        $vehicles = Vehicle::where('is_active', true)->get();
        $drivers = Driver::where('is_available', true)->get();
        $branches = userBranches();

        return view('fleet::trips.edit', compact('trip', 'vehicles', 'drivers', 'branches'));
    }

    public function update(TripRequest $request, Trip $trip)
    {
        try {
            $trip->update($request->validated());
            Alert::toast(__('fleet::messages.updated_successfully'), 'success');

            return redirect()->route('fleet.trips.index');
        } catch (\Exception) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $trip = Trip::findOrFail($id);
            $trip->delete();
            Alert::toast(__('fleet::messages.deleted_successfully'), 'success');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');
        }

        return redirect()->route('fleet.trips.index');
    }
}
