<?php

namespace Modules\Shipping\Services;

use Modules\Shipping\Models\{Order, Driver};
use Carbon\Carbon;

class OrderScheduler
{
    public function autoAssignDriver($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $driver = Driver::where('is_available', true)
            ->where('branch_id', $order->branch_id)
            ->orderBy('rating', 'desc')
            ->orderBy('completed_deliveries', 'desc')
            ->first();

        if ($driver) {
            $order->driver_id = $driver->id;
            $order->delivery_status = 'assigned';
            $order->assigned_at = now();
            $order->save();

            $driver->is_available = false;
            $driver->save();

            return true;
        }

        return false;
    }

    public function scheduleOrder($orderId, $date, $timeFrom, $timeTo)
    {
        $order = Order::findOrFail($orderId);
        
        $order->scheduled_date = Carbon::parse($date);
        $order->scheduled_time_from = $timeFrom;
        $order->scheduled_time_to = $timeTo;
        $order->save();

        return $order;
    }

    public function getDriverSchedule($driverId, $date)
    {
        return Order::where('driver_id', $driverId)
            ->whereDate('scheduled_date', $date)
            ->orderBy('scheduled_time_from')
            ->get();
    }

    public function optimizeRoutes($driverId, $date)
    {
        $orders = $this->getDriverSchedule($driverId, $date);
        
        // يمكن إضافة خوارزمية لتحسين المسارات هنا
        // مثل Traveling Salesman Problem (TSP)
        
        return $orders;
    }
}
