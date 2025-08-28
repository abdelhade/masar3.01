<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Maintenance\Models\ServiceType;
use Modules\Maintenance\Http\Requests\ServiceTypeRequest;
use RealRashid\SweetAlert\Facades\Alert;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $types = ServiceType::all();
        return view('maintenance::service-types.index', compact('types'));
    }

    public function create()
    {
        return view('maintenance::service-types.create');
    }

    public function store(ServiceTypeRequest $request)
    {
        try {
            ServiceType::create($request->validated());
            Alert::toast('تم إضافة نوع الصيانة بنجاح', 'success');
            return redirect()->route('service.types.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء إضافة نوع الصيانة', 'error');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $type = ServiceType::findOrFail($id);
        return view('maintenance::service-types.edit', compact('type'));
    }

    public function update(ServiceTypeRequest $request, ServiceType $type)
    {
        try {
            $type->update($request->validated());
            Alert::toast('تم تعديل نوع الصيانة بنجاح', 'success');
            return redirect()->route('service.types.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء تعديل نوع الصيانة', 'error');
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $type = ServiceType::findOrFail($id);
            $type->delete();
            Alert::toast('تم حذف نوع الصيانة بنجاح', 'success');
            return redirect()->route('service.types.index');
        } catch (\Exception $e) {
            Alert::toast('لا يمكن حذف نوع الصيانة لأنه مرتبط بسجلات أخرى', 'error');
            return redirect()->route('service.types.index');
        }
    }
}
