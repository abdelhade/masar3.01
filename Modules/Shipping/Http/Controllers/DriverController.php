<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Http\Requests\DriverRequest;
use Modules\Shipping\Models\Driver;
use RealRashid\SweetAlert\Facades\Alert;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Drivers')->only(['index']);
        $this->middleware('permission:create Drivers')->only(['create', 'store']);
        $this->middleware('permission:edit Drivers')->only(['edit', 'update']);
        $this->middleware('permission:delete Drivers')->only(['destroy']);
    }

    public function index()
    {
        $drivers = Driver::paginate(10);

        return view('shipping::drivers.index', compact('drivers'));
    }

    public function create()
    {
        $branches = userBranches();

        return view('shipping::drivers.create', compact('branches'));
    }

    public function store(DriverRequest $request)
    {
        Driver::create($request->validated());
        Alert::toast(__('Driver created successfully.'), 'success');

        return redirect()->route('drivers.index');
    }

    public function edit(Driver $driver)
    {
        return view('shipping::drivers.edit', compact('driver'));
    }

    public function update(DriverRequest $request, Driver $driver)
    {
        $driver->update($request->validated());
        Alert::toast(__('Driver updated successfully.'), 'success');

        return redirect()->route('drivers.index');
    }

    public function show(Driver $driver)
    {
        $driver->load('branch');

        return view('shipping::drivers.show', compact('driver'));
    }

    public function destroy(Driver $driver)
    {
        if ($driver->orders()->exists()) {
            Alert::toast(__('Cannot delete driver with existing orders.'), 'error');

            return redirect()->route('drivers.index');
        }

        $driver->delete();
        Alert::toast(__('Driver deleted successfully.'), 'success');

        return redirect()->route('drivers.index');
    }
}
