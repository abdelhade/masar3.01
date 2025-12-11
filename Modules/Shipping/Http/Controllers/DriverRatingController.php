<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Models\{DriverRating, Order};
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class DriverRatingController extends Controller
{
    public function create($orderId)
    {
        $order = Order::with('driver')->findOrFail($orderId);
        
        if ($order->rating) {
            Alert::warning(__('This order has already been rated.'));
            return redirect()->back();
        }
        
        return view('shipping::ratings.driver-create', compact('order'));
    }

    public function store(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->rating) {
            Alert::warning(__('This order has already been rated.'));
            return redirect()->back();
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        DriverRating::create([
            'driver_id' => $order->driver_id,
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'customer_name' => $order->customer_name,
            'rated_by' => auth()->id(),
        ]);

        Alert::toast(__('Rating submitted successfully.'), 'success');
        return redirect()->route('orders.index');
    }
}
