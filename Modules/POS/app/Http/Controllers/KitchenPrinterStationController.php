<?php

declare(strict_types=1);

namespace Modules\POS\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\POS\Http\Requests\KitchenPrinterStationRequest;
use Modules\POS\Models\KitchenPrinterStation;
use RealRashid\SweetAlert\Facades\Alert;

class KitchenPrinterStationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Kitchen Printer Settings')->only(['index']);
        $this->middleware('permission:edit Kitchen Printer Settings')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $stations = KitchenPrinterStation::orderBy('sort_order')->get();

        return view('pos::kitchen-printers.index', compact('stations'));
    }

    public function create()
    {
        return view('pos::kitchen-printers.create');
    }

    public function store(KitchenPrinterStationRequest $request)
    {
        try {
            KitchenPrinterStation::create($request->validated());
            Alert::success(__('pos.printer_station_created'));

            return redirect()->route('kitchen-printers.index');
        } catch (\Exception $e) {
            Alert::error(__('pos.printer_station_create_failed'));

            return back()->withInput();
        }
    }

    public function edit(KitchenPrinterStation $kitchenPrinter)
    {
        return view('pos::kitchen-printers.edit', compact('kitchenPrinter'));
    }

    public function update(KitchenPrinterStationRequest $request, KitchenPrinterStation $kitchenPrinter)
    {
        try {
            $kitchenPrinter->update($request->validated());
            Alert::success(__('pos.printer_station_updated'));

            return redirect()->route('kitchen-printers.index');
        } catch (\Exception $e) {
            Alert::error(__('pos.printer_station_update_failed'));

            return back()->withInput();
        }
    }

    public function destroy(KitchenPrinterStation $kitchenPrinter)
    {
        try {
            $kitchenPrinter->delete();
            Alert::success(__('pos.printer_station_deleted'));
        } catch (\Exception $e) {
            Alert::error(__('pos.printer_station_delete_failed'));
        }

        return redirect()->route('kitchen-printers.index');
    }
}
