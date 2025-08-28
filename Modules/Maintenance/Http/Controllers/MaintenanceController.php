<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Maintenance\Models\Maintenance;
use Modules\Maintenance\Http\Requests\MaintenanceRequest;
use Modules\Maintenance\Models\ServiceType;

class MaintenanceController extends Controller
{
    public function index()
    {
        $maintenances = Maintenance::with('type')->latest()->paginate(20);
        return view('maintenance::maintenances.index', compact('maintenances'));
    }

    public function create()
    {
        $types = ServiceType::all();
        return view('maintenance::maintenances.create', compact('types'));
    }

    public function store(MaintenanceRequest $request)
    {
        try {
            Maintenance::create($request->validated());
            Alert::toast('تم إضافة الصيانة بنجاح', 'success');
            return redirect()->route('maintenances.index');
        } catch (\Exception) {
            Alert::toast('حدث خطأ أثناء إضافة الصيانة: ', 'error');
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
            Alert::toast('تم تعديل بيانات الصيانة بنجاح', 'success');
            return redirect()->route('maintenances.index');
        } catch (\Exception) {
            Alert::toast('حدث خطأ أثناء تعديل بيانات الصيانة: ', 'error');
            return redirect()->back();
        }
    }

    public function destroy(Maintenance $maintenance)
    {
        try {
            $maintenance->delete();
            Alert::toast('تم حذف الصيانة بنجاح', 'success');
            return redirect()->route('maintenances.index');
        } catch (\Exception) {
            Alert::toast('حدث خطأ أثناء حذف الصيانة: ', 'error');
            return redirect()->back();
        }
    }
}
