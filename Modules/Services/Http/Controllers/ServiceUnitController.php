<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Services\Models\ServiceUnit;
use Modules\Services\Http\Requests\ServiceUnitRequest;

class ServiceUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceUnits = ServiceUnit::orderBy('name')
            ->paginate(15);

        return view('services::service-units.index', compact('serviceUnits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('services::service-units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceUnitRequest $request)
    {
        ServiceUnit::create($request->validated());

        return redirect()
            ->route('services.service-units.index')
            ->with('success', __('تم إنشاء وحدة الخدمة بنجاح'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceUnit $serviceUnit)
    {
        $serviceUnit->load('services');
        return view('services::service-units.show', compact('serviceUnit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceUnit $serviceUnit)
    {
        return view('services::service-units.edit', compact('serviceUnit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceUnitRequest $request, ServiceUnit $serviceUnit)
    {
        $serviceUnit->update($request->validated());

        return redirect()
            ->route('services.service-units.index')
            ->with('success', __('تم تحديث وحدة الخدمة بنجاح'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceUnit $serviceUnit)
    {
        // Check if service unit has services
        if ($serviceUnit->services()->count() > 0) {
            return redirect()
                ->route('services.service-units.index')
                ->with('error', __('لا يمكن حذف وحدة الخدمة لأنها تحتوي على خدمات'));
        }

        $serviceUnit->delete();

        return redirect()
            ->route('services.service-units.index')
            ->with('success', __('تم حذف وحدة الخدمة بنجاح'));
    }
}
