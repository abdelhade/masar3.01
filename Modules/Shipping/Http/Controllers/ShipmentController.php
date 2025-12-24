<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Models\ShippingCompany;
use Modules\Shipping\Http\Requests\ShipmentRequest;
use RealRashid\SweetAlert\Facades\Alert;

class ShipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Shipments')->only(['index']);
        $this->middleware('permission:create Shipments')->only(['create', 'store']);
        $this->middleware('permission:edit Shipments')->only(['edit', 'update']);
        $this->middleware('permission:delete Shipments')->only(['destroy']);
    }

    public function index()
    {
        $shipments = Shipment::with('shippingCompany')->paginate(10);
        return view('shipping::shipments.index', compact('shipments'));
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['shippingCompany']);
        return view('shipping::shipments.show', compact('shipment'));
    }

    public function create()
    {
        $branches = userBranches();
        $companies = ShippingCompany::where('is_active', true)->get();
        return view('shipping::shipments.create', compact('companies', 'branches'));
    }

    public function store(ShipmentRequest $request)
    {
        Shipment::create($request->validated());
        Alert::toast(__('Shipment created successfully.'), 'success');
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
        Alert::toast(__('Shipment updated successfully.'), 'success');
        return redirect()->route('shipments.index');
    }

    public function destroy(Shipment $shipment)
    {
        // Check if shipment has orders
        if ($shipment->orders()->exists()) {
            Alert::toast(__('Cannot delete shipment with existing orders.'), 'error');
            return redirect()->route('shipments.index');
        }
        
        $shipment->delete();
        Alert::toast(__('Shipment deleted successfully.'), 'success');
        return redirect()->route('shipments.index');
    }
}
