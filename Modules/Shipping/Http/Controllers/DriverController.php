<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Shipping\Models\Driver;
use Modules\Shipping\Http\Requests\DriverRequest;
use RealRashid\SweetAlert\Facades\Alert;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::paginate(10);
        return view('shipping::drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('shipping::drivers.create');
    }

    public function store(DriverRequest $request)
    {
        Driver::create($request->validated());
        Alert::toast('تم إنشاء السائق بنجاح.', 'success');
        return redirect()->route('drivers.index');
    }

    public function edit(Driver $driver)
    {
        return view('shipping::drivers.edit', compact('driver'));
    }

    public function update(DriverRequest $request, Driver $driver)
    {
        $driver->update($request->validated());
        Alert::toast('تم تحديث السائق بنجاح.', 'success');
        return redirect()->route('drivers.index');
    }

    public function destroy(Driver $driver)
    {
        if ($driver->orders()->exists()) {
            Alert::toast('لا يمكن حذف السائق لوجود طلبات مرتبطة.', 'error');
            return redirect()->route('drivers.index');
        }
        $driver->delete();
        Alert::toast('تم حذف السائق بنجاح.', 'success');
        return redirect()->route('drivers.index');
    }
}
