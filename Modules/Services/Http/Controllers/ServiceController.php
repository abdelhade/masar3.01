<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Services\Models\Service;
use Modules\Services\Http\Requests\ServiceRequest;
use Illuminate\Http\RedirectResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Service::with(['serviceType', 'serviceUnit']);
            // ->where('is_active', true);

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by service type
        if ($request->filled('service_type_id')) {
            $query->where('service_type_id', $request->get('service_type_id'));
        }

        $services = $query->paginate(15);

        return view('services::services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $service = new Service();
            $serviceTypes = \Modules\Services\Models\ServiceType::orderBy('name')->get();
            $serviceUnits = \Modules\Services\Models\ServiceUnit::where('is_active', true)->orderBy('name')->get();

            return view('services::services.create', compact('service', 'serviceTypes', 'serviceUnits'));
        } catch (\Exception $e) {
            Log::error('Error in ServiceController@create: ' . $e->getMessage());
            return redirect()->route('services.services.index')
                ->with('error', 'حدث خطأ في تحميل الصفحة: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['branch_id'] = $data['branch_id'] ?? 1; // Default branch ID
        
        Service::create($data);

        return redirect()->route('services.services.index')
            ->with('success', 'تم إنشاء الخدمة بنجاح');
    }

    /**
     * Show the specified resource.
     */
    public function show(Service $service)
    {
        $service->load(['serviceType', 'serviceUnit', 'bookings']);
        
        return view('services::services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        try {
            $service->load(['serviceType', 'serviceUnit']);
            $serviceTypes = \Modules\Services\Models\ServiceType::orderBy('name')->get();
            $serviceUnits = \Modules\Services\Models\ServiceUnit::where('is_active', true)->orderBy('name')->get();

            return view('services::services.edit', compact('service', 'serviceTypes', 'serviceUnits'));
        } catch (\Exception $e) {
            Log::error('Error in ServiceController@edit: ' . $e->getMessage());
            return redirect()->route('services.services.index')
                ->with('error', 'حدث خطأ في تحميل الصفحة: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $request->validated();
        $data['branch_id'] = $data['branch_id'] ?? $service->branch_id; // Keep existing branch_id if not provided
        
        $service->update($data);

        return redirect()->route('services.services.index')
            ->with('success', 'تم تحديث الخدمة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
        // Check if service has bookings
        if ($service->bookings()->exists()) {
            return redirect()->route('services.services.index')
                ->with('error', 'لا يمكن حذف الخدمة لوجود حجوزات مرتبطة بها');
        }

        $service->delete();

        return redirect()->route('services.services.index')
            ->with('success', 'تم حذف الخدمة بنجاح');
    }

    /**
     * Toggle service active status.
     */
    public function toggleStatus(Service $service): RedirectResponse
    {
        $service->update(['is_active' => !$service->is_active]);

        $status = $service->is_active ? 'تفعيل' : 'إلغاء تفعيل';
        
        return redirect()->route('services.services.index')
            ->with('success', "تم {$status} الخدمة بنجاح");
    }
}
