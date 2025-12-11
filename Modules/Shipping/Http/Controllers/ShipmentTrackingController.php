<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentTrackingController extends Controller
{
    public function track(Request $request)
    {
        $trackingNumber = $request->input('tracking_number');
        $shipment = null;

        if ($trackingNumber) {
            $shipment = Shipment::with(['statusHistory.changedBy', 'shippingCompany'])
                ->where('tracking_number', $trackingNumber)
                ->first();
        }

        return view('shipping::tracking.index', compact('shipment', 'trackingNumber'));
    }

    public function show($trackingNumber)
    {
        $shipment = Shipment::with(['statusHistory.changedBy', 'shippingCompany'])
            ->where('tracking_number', $trackingNumber)
            ->firstOrFail();

        return view('shipping::tracking.show', compact('shipment'));
    }
}
