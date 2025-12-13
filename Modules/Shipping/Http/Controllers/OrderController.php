<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Http\Requests\OrderRequest;
use Modules\Shipping\Models\Driver;
use Modules\Shipping\Models\Order;
use Modules\Shipping\Models\Shipment;
use RealRashid\SweetAlert\Facades\Alert;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Orders')->only(['index']);
        $this->middleware('permission:create Orders')->only(['create', 'store']);
        $this->middleware('permission:edit Orders')->only(['edit', 'update']);
        $this->middleware('permission:delete Orders')->only(['destroy']);
    }

    public function index()
    {
        $orders = Order::with(['driver', 'shipment'])->paginate(10);

        return view('shipping::orders.index', compact('orders'));
    }

    public function create()
    {
        $branches = userBranches();
        $drivers = Driver::where('is_available', true)->get();
        $shipments = Shipment::all();

        return view('shipping::orders.create', compact('drivers', 'shipments', 'branches'));
    }

    public function store(OrderRequest $request)
    {
        Order::create($request->validated());
        Alert::toast(__('Order created successfully.'), 'success');

        return redirect()->route('orders.index');
    }

    public function edit(Order $order)
    {
        $drivers = Driver::where('is_available', true)->get();
        $shipments = Shipment::all();

        return view('shipping::orders.edit', compact('order', 'drivers', 'shipments'));
    }

    public function update(OrderRequest $request, Order $order)
    {
        $order->update($request->validated());
        Alert::toast(__('Order updated successfully.'), 'success');

        return redirect()->route('orders.index');
    }

    public function show(Order $order)
    {
        $order->load(['driver', 'shipment', 'branch']);

        return view('shipping::orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        $order->delete();
        Alert::toast(__('Order deleted successfully.'), 'success');

        return redirect()->route('orders.index');
    }
}
