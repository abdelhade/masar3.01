<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Models\OperHead;
use Illuminate\Routing\Controller;
use Modules\Maintenance\Http\Requests\MaintenanceRequest;
use Modules\Maintenance\Models\Maintenance;
use Modules\Maintenance\Models\ServiceType;
use RealRashid\SweetAlert\Facades\Alert;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Maintenances')->only(['index']);
        $this->middleware('permission:create Maintenances')->only(['create', 'store']);
        $this->middleware('permission:edit Maintenances')->only(['edit', 'update']);
        $this->middleware('permission:delete Maintenances')->only(['destroy']);
    }

    public function index()
    {
        $maintenances = Maintenance::with('type')->orderBy('accural_date', 'asc')->paginate(20);

        return view('maintenance::maintenances.index', compact('maintenances'));
    }

    public function create()
    {
        $types = ServiceType::all();
        $branches = userBranches();

        return view('maintenance::maintenances.create', compact('types', 'branches'));
    }

    public function store(MaintenanceRequest $request)
    {
        try {
            $maintenance = Maintenance::create($request->validated());

            OperHead::create([
                'pro_date' => $request->date,
                'accural_date' => $request->accural_date,
                'info' => 'صيانة '.$request->item.' رقم البند '.$request->item_number,
                'status' => $request->status,
                'op2' => $maintenance->id,
            ]);

            Alert::toast(__('Item created successfully'), 'success');

            return redirect()->route('maintenances.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back();
        }
    }

    public function edit(Maintenance $maintenance)
    {
        $types = ServiceType::all();

        return view('maintenance::maintenances.edit', compact('maintenance', 'types'));
    }

    public function update(MaintenanceRequest $request, Maintenance $maintenance)
    {
        try {
            $maintenance->update($request->validated());
            $maintenance->operHead()->update([
                'pro_date' => $request->date,
                'accural_date' => $request->accural_date,
                'info' => 'صيانة '.$request->item_name.' رقم البند '.$request->item_number,
                'status' => $request->status,
            ]);
            Alert::toast(__('Item updated successfully'), 'success');

            return redirect()->route('maintenances.index');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back();
        }
    }

    public function show(Maintenance $maintenance)
    {
        $maintenance->load(['type', 'branch']);

        return view('maintenance::maintenances.show', compact('maintenance'));
    }

    public function destroy(Maintenance $maintenance)
    {
        try {
            $maintenance->delete();
            Alert::toast(__('Item deleted successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred'), 'error');
        }

        return redirect()->route('maintenances.index');
    }
}
