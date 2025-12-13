<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Fleet\Http\Requests\VehicleTypeRequest;
use Modules\Fleet\Models\VehicleType;
use RealRashid\SweetAlert\Facades\Alert;

class VehicleTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Vehicle Types')->only(['index', 'show']);
        $this->middleware('permission:create Vehicle Types')->only(['create', 'store']);
        $this->middleware('permission:edit Vehicle Types')->only(['edit', 'update']);
        $this->middleware('permission:delete Vehicle Types')->only(['destroy']);
    }

    public function index()
    {
        $types = VehicleType::all();

        return view('fleet::vehicle-types.index', compact('types'));
    }

    public function create()
    {
        return view('fleet::vehicle-types.create');
    }

    public function store(VehicleTypeRequest $request)
    {
        try {
            VehicleType::create($request->validated());
            Alert::toast(__('fleet::messages.created_successfully'), 'success');

            return redirect()->route('fleet.vehicle-types.index');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $type = VehicleType::findOrFail($id);
        $type->load('vehicles');

        return view('fleet::vehicle-types.show', compact('type'));
    }

    public function edit($id)
    {
        $type = VehicleType::findOrFail($id);

        return view('fleet::vehicle-types.edit', compact('type'));
    }

    public function update(VehicleTypeRequest $request, VehicleType $vehicleType)
    {
        try {
            $vehicleType->update($request->validated());
            Alert::toast(__('fleet::messages.updated_successfully'), 'success');

            return redirect()->route('fleet.vehicle-types.index');
        } catch (\Exception) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');

            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $type = VehicleType::findOrFail($id);
            $type->delete();
            Alert::toast(__('fleet::messages.deleted_successfully'), 'success');
        } catch (\Exception $e) {
            Alert::toast(__('fleet::messages.error_occurred'), 'error');
        }

        return redirect()->route('fleet.vehicle-types.index');
    }
}
