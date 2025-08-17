<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Shipping\Models\Order;
use Modules\Shipping\Models\Driver;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Http\Requests\OrderRequest;
use RealRashid\SweetAlert\Facades\Alert;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['driver', 'shipment'])->paginate(10);
        return view('shipping::orders.index', compact('orders'));
    }

    public function create()
    {
        $drivers = Driver::where('is_available', true)->get();
        $shipments = Shipment::all();
        return view('shipping::orders.create', compact('drivers', 'shipments'));
    }

    public function store(OrderRequest $request)
    {
        Order::create($request->validated());
        Alert::toast('تم إنشاء الطلب بنجاح.', 'success');
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
        Alert::toast('تم تحديث الطلب بنجاح.', 'success');
        return redirect()->route('orders.index');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        Alert::toast('تم حذف الطلب بنجاح.', 'success');
        return redirect()->route('orders.index');
    }
}
