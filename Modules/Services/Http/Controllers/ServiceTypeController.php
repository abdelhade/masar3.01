<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Services\Models\ServiceType;
use Modules\Services\Http\Requests\ServiceTypeRequest;
use Modules\Branches\Models\Branch;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceTypes = ServiceType::with('branch')
            ->orderBy('name')
            ->paginate(15);

        return view('services::service-types.index', compact('serviceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('services::service-types.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceTypeRequest $request)
    {
        ServiceType::create($request->validated());

        return redirect()
            ->route('services.service-types.index')
            ->with('success', __('تم إنشاء نوع الخدمة بنجاح'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceType $serviceType)
    {
        $serviceType->load(['branch', 'services']);
        return view('services::service-types.show', compact('serviceType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceType $serviceType)
    {
        try {
            $branches = Branch::where('is_active', true)->get();
            return view('services::service-types.edit', compact('serviceType', 'branches'));
        } catch (\Exception $e) {
            Log::error('Error in ServiceTypeController@edit: ' . $e->getMessage());
            return redirect()->route('services.service-types.index')
                ->with('error', 'حدث خطأ في تحميل الصفحة: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceTypeRequest $request, ServiceType $serviceType)
    {
        $serviceType->update($request->validated());

        return redirect()
            ->route('services.service-types.index')
            ->with('success', __('تم تحديث نوع الخدمة بنجاح'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceType $serviceType)
    {
        // Check if service type has services
        if ($serviceType->services()->count() > 0) {
            return redirect()
                ->route('services.service-types.index')
                ->with('error', __('لا يمكن حذف نوع الخدمة لأنه يحتوي على خدمات'));
        }

        $serviceType->delete();

        return redirect()
            ->route('services.service-types.index')
            ->with('success', __('تم حذف نوع الخدمة بنجاح'));
    }
}
