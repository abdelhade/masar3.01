<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Models\ShippingCompany;
use Modules\Shipping\Http\Requests\ShipmentRequest;
use RealRashid\SweetAlert\Facades\Alert;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('shippingCompany')->paginate(10);
        return view('shipping::shipments.index', compact('shipments'));
    }

    public function create()
    {
        $companies = ShippingCompany::where('is_active', true)->get();
        return view('shipping::shipments.create', compact('companies'));
    }

    public function store(ShipmentRequest $request)
    {
        Shipment::create($request->validated());
        Alert::toast('تم إنشاء الشحنة بنجاح.', 'success');
        return redirect()->route('shipments.index');
    }

    public function edit(Shipment $shipment)
    {
        $companies = ShippingCompany::where('is_active', true)->get();
        return view('shipping::shipments.edit', compact('shipment', 'companies'));
    }

    public function update(ShipmentRequest $request, Shipment $shipment)
    {
        $shipment->update($request->validated());
        Alert::toast('تم تحديث الشحنة بنجاح.', 'success');
        return redirect()->route('shipments.index');
    }

    public function destroy(Shipment $shipment)
    {
        $shipment->delete();
        Alert::toast('تم حذف الشحنة بنجاح.', 'success');
        return redirect()->route('shipments.index');
    }
}
