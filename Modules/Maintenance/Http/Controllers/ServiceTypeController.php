<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Maintenance\Http\Requests\ServiceTypeRequest;
use Modules\Maintenance\Models\ServiceType;
use RealRashid\SweetAlert\Facades\Alert;

class ServiceTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Service Types')->only(['index']);
        $this->middleware('permission:create Service Types')->only(['create', 'store']);
        $this->middleware('permission:edit Service Types')->only(['edit', 'update']);
        $this->middleware('permission:delete Service Types')->only(['destroy']);
    }

    public function index()
    {
        $types = ServiceType::all();

        return view('maintenance::service-types.index', compact('types'));
    }

    public function create()
    {
        $branches = userBranches();
        return view('maintenance::service-types.create', compact('branches'));
    }

    public function store(ServiceTypeRequest $request)
    {

        try {
            ServiceType::create($request->validated());
            Alert::toast(__('Item created successfully'), 'success');

            return redirect()->route('service.types.index');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $type = ServiceType::findOrFail($id);
        $branches = userBranches();

        return view('maintenance::service-types.edit', compact('type', 'branches'));
    }

    public function update(ServiceTypeRequest $request, ServiceType $service_type)
    {
        try {
            $service_type->update($request->validated());
            Alert::toast(__('Item updated successfully'), 'success');

            return redirect()->route('service.types.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back();
        }
    }

    public function show($id)
    {
        $type = ServiceType::findOrFail($id);
        $type->load('branch');

        return view('maintenance::service-types.show', compact('type'));
    }

    public function destroy($id)
    {
        try {
            $type = ServiceType::findOrFail($id);
            $type->delete();
            Alert::toast(__('Item deleted successfully'), 'success');
        } catch (\Exception $e) {
            Alert::toast(__('An error occurred'), 'error');
        }

        return redirect()->route('service.types.index');
    }
}
