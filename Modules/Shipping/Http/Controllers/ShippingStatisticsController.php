<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Models\Order;
use Modules\Shipping\Models\Driver;
use Modules\Shipping\Models\ShippingCompany;

class ShippingStatisticsController extends Controller
{
    public function index()
    {
        $stats = $this->getDashboardStatistics();
        $widgetStats = $this->getWidgetStats();
        $topDrivers = $this->getTopDrivers();
        $recentShipments = $this->getRecentShipments();
        
        return view('shipping::dashboard.index', compact('stats', 'widgetStats', 'topDrivers', 'recentShipments'));
    }

    private function getWidgetStats(): array
    {
        return [
            'total' => Shipment::count(),
            'in_transit' => Shipment::whereIn('status', ['in_transit', 'out_for_delivery'])->count(),
            'delivered' => Shipment::where('status', 'delivered')->count(),
            'pending' => Shipment::where('status', 'pending')->count(),
        ];
    }

    private function getTopDrivers()
    {
        return Driver::where('rating', '>', 0)
            ->orderBy('rating', 'desc')
            ->orderBy('completed_deliveries', 'desc')
            ->limit(5)
            ->get();
    }

    private function getRecentShipments()
    {
        return Shipment::with('shippingCompany')
            ->latest()
            ->limit(5)
            ->get();
    }

    private function getDashboardStatistics(): array
    {
        return [
            'overview' => $this->getOverviewStats(),
            'shipment_status' => $this->getShipmentStatusBreakdown(),
            'delivery_status' => $this->getOrderDeliveryStatusBreakdown(),
            'drivers' => $this->getDriverStats(),
            'companies' => $this->getCompanyStats(),
            'monthly_trend' => $this->getMonthlyTrend(),
        ];
    }

    private function getOverviewStats(): array
    {
        return [
            'total_shipments' => Shipment::count(),
            'total_orders' => Order::count(),
            'total_drivers' => Driver::count(),
            'total_companies' => ShippingCompany::count(),
            'active_companies' => ShippingCompany::where('is_active', true)->count(),
            'available_drivers' => Driver::where('is_available', true)->count(),
        ];
    }

    private function getShipmentStatusBreakdown(): array
    {
        $statusCounts = Shipment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => (int)$item->count])
            ->toArray();
        $total = array_sum($statusCounts);
        $statuses = [
            'pending' => 'قيد الانتظار',
            'in_transit' => 'قيد النقل',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغاة',
        ];
        $breakdown = [];
        foreach ($statuses as $key => $label) {
            $count = $statusCounts[$key] ?? 0;
            $breakdown[$key] = [
                'label' => $label,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        }
        return $breakdown;
    }

    private function getOrderDeliveryStatusBreakdown(): array
    {
        $statusCounts = Order::select('delivery_status', DB::raw('count(*) as count'))
            ->groupBy('delivery_status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->delivery_status => (int)$item->count])
            ->toArray();
        $total = array_sum($statusCounts);
        $statuses = [
            'pending' => 'قيد الانتظار',
            'out_for_delivery' => 'جاري التوصيل',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغاة',
        ];
        $breakdown = [];
        foreach ($statuses as $key => $label) {
            $count = $statusCounts[$key] ?? 0;
            $breakdown[$key] = [
                'label' => $label,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        }
        return $breakdown;
    }

    private function getDriverStats(): array
    {
        return [
            'total' => Driver::count(),
            'available' => Driver::where('is_available', true)->count(),
            'busy' => Driver::where('is_available', false)->count(),
        ];
    }

    private function getCompanyStats(): array
    {
        return [
            'total' => ShippingCompany::count(),
            'active' => ShippingCompany::where('is_active', true)->count(),
            'inactive' => ShippingCompany::where('is_active', false)->count(),
        ];
    }

    private function getMonthlyTrend(): array
    {
        $months = Shipment::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as month_name'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
            DB::raw('SUM(CASE WHEN status = "in_transit" THEN 1 ELSE 0 END) as in_transit'),
            DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered'),
            DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month', 'month_name')
            ->orderBy('month', 'asc')
            ->get();
        return [
            'labels' => $months->pluck('month_name')->toArray(),
            'data' => [
                'total' => $months->pluck('total')->map(fn($v) => (int)$v)->toArray(),
                'pending' => $months->pluck('pending')->map(fn($v) => (int)$v)->toArray(),
                'in_transit' => $months->pluck('in_transit')->map(fn($v) => (int)$v)->toArray(),
                'delivered' => $months->pluck('delivered')->map(fn($v) => (int)$v)->toArray(),
                'cancelled' => $months->pluck('cancelled')->map(fn($v) => (int)$v)->toArray(),
            ],
        ];
    }
}
