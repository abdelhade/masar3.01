<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Models\ShippingZone;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ShippingZoneController extends Controller
{
    public function index()
    {
        $zones = ShippingZone::paginate(10);
        return view('shipping::zones.index', compact('zones'));
    }

    public function create()
    {
        $branches = userBranches();
        return view('shipping::zones.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:shipping_zones,code',
            'description' => 'nullable|string',
            'base_rate' => 'required|numeric|min:0',
            'rate_per_kg' => 'required|numeric|min:0',
            'estimated_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'branch_id' => 'required|exists:branches,id',
        ]);

        ShippingZone::create($validated);
        Alert::toast(__('Zone created successfully.'), 'success');
        return redirect()->route('shipping.zones.index');
    }

    public function edit(ShippingZone $zone)
    {
        $branches = userBranches();
        return view('shipping::zones.edit', compact('zone', 'branches'));
    }

    public function update(Request $request, ShippingZone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:shipping_zones,code,' . $zone->id,
            'description' => 'nullable|string',
            'base_rate' => 'required|numeric|min:0',
            'rate_per_kg' => 'required|numeric|min:0',
            'estimated_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $zone->update($validated);
        Alert::toast(__('Zone updated successfully.'), 'success');
        return redirect()->route('shipping.zones.index');
    }

    public function destroy(ShippingZone $zone)
    {
        $zone->delete();
        Alert::toast(__('Zone deleted successfully.'), 'success');
        return redirect()->route('shipping.zones.index');
    }
}
