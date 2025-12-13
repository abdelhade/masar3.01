<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Maintenance\Http\Requests\PeriodicMaintenanceRequest;
use Modules\Maintenance\Models\PeriodicMaintenanceSchedule;
use Modules\Maintenance\Models\ServiceType;
use RealRashid\SweetAlert\Facades\Alert;

class PeriodicMaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Periodic Maintenance')->only(['index']);
        $this->middleware('permission:create Periodic Maintenance')->only(['create', 'store']);
        $this->middleware('permission:edit Periodic Maintenance')->only(['edit', 'update', 'toggleActive']);
        $this->middleware('permission:delete Periodic Maintenance')->only(['destroy']);
    }

    public function index()
    {
        $schedules = PeriodicMaintenanceSchedule::with('serviceType')
            ->orderBy('next_maintenance_date', 'asc')
            ->paginate(20);

        return view('maintenance::periodic.index', compact('schedules'));
    }

    public function create()
    {
        $types = ServiceType::all();
        $branches = userBranches();

        return view('maintenance::periodic.create', compact('types', 'branches'));
    }

    public function store(PeriodicMaintenanceRequest $request)
    {
        try {
            PeriodicMaintenanceSchedule::create($request->validated());
            Alert::toast(__('Item created successfully'), 'success');

            return redirect()->route('periodic.maintenances.index');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back()->withInput();
        }
    }

    public function edit(PeriodicMaintenanceSchedule $periodicMaintenance)
    {
        $types = ServiceType::all();

        return view('maintenance::periodic.edit', compact('periodicMaintenance', 'types'));
    }

    public function update(PeriodicMaintenanceRequest $request, $id)
    {
        try {
            $periodic_maintenance = PeriodicMaintenanceSchedule::findOrFail($id);

            $periodic_maintenance->update($request->validated());

            Alert::toast(__('Item updated successfully'), 'success');

            return redirect()->route('periodic.maintenances.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back();
        }
    }

    public function destroy(PeriodicMaintenanceSchedule $periodicMaintenance)
    {
        try {
            $periodicMaintenance->delete();
            Alert::toast(__('Item deleted successfully'), 'success');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred'), 'error');
        }

        return redirect()->route('periodic.maintenances.index');
    }

    public function createMaintenanceFromSchedule(PeriodicMaintenanceSchedule $schedule)
    {
        $types = ServiceType::all();
        $branches = userBranches();

        return view('maintenance::maintenances.create-from-schedule', compact('schedule', 'types', 'branches'));
    }

    public function show(PeriodicMaintenanceSchedule $periodicMaintenance)
    {
        $periodicMaintenance->load(['serviceType', 'branch']);

        return view('maintenance::periodic.show', compact('periodicMaintenance'));
    }

    public function toggleActive(PeriodicMaintenanceSchedule $periodicMaintenance)
    {
        try {
            $periodicMaintenance->update(['is_active' => ! $periodicMaintenance->is_active]);
            Alert::toast(__('Item updated successfully'), 'success');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred'), 'error');
        }

        return redirect()->back();
    }
}
