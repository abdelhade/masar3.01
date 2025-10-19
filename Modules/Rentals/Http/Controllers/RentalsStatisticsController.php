<?php

namespace Modules\Rentals\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Modules\Rentals\Models\RentalsBuilding;
use Modules\Rentals\Models\RentalsUnit;
use Modules\Rentals\Models\RentalsLease;

class RentalsStatisticsController extends Controller
{
    /**
     * عرض شاشة الإحصائيات الرئيسية
     */
    public function index()
    {
        $stats = $this->getDashboardStatistics();

        return view('rentals::dashboard.index', compact('stats'));
    }

    /**
     * جلب جميع الإحصائيات بشكل محسّن
     */
    private function getDashboardStatistics()
    {
        // استخدام Cache لمدة 5 دقائق لتحسين الأداء
        return Cache::remember('rentals_dashboard_stats', 300, function () {
            return [
                'overview' => $this->getOverviewStats(),
                'buildings' => $this->getBuildingsStats(),
                'leases' => $this->getLeasesStats(),
                'financial' => $this->getFinancialStats(),
                'charts' => $this->getChartsData(),
            ];
        });
    }

    /**
     * إحصائيات عامة سريعة
     */
    private function getOverviewStats()
    {
        return [
            'total_buildings' => RentalsBuilding::count(),
            'total_units' => RentalsUnit::count(),
            'active_leases' => RentalsLease::where('status', 'active')->count(),
            'total_clients' => RentalsLease::distinct('client_id')->count('client_id'),
        ];
    }

    /**
     * إحصائيات المباني
     */
    private function getBuildingsStats()
    {
        return RentalsBuilding::select(
            'id',
            'name',
            'address',
            'floors',
            DB::raw('(SELECT COUNT(*) FROM rentals_units WHERE building_id = rentals_buildings.id) as total_units'),
        )
            ->withCount('units')
            ->get()
            ->map(function ($building) {
                $building->occupancy_rate = $building->total_units > 0
                    ? round(($building->rented_units / $building->total_units) * 100, 2)
                    : 0;
                return $building;
            });
    }

    /**
     * إحصائيات الوحدات حسب الحالة
     */


    /**
     * إحصائيات العقود
     */
    private function getLeasesStats()
    {
        $now = now();

        return [
            'active' => RentalsLease::where('status', 'active')
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->count(),
            'expired' => RentalsLease::where('end_date', '<', $now)->count(),
            'upcoming' => RentalsLease::where('start_date', '>', $now)->count(),
            'expiring_soon' => RentalsLease::where('status', 'active')
                ->whereBetween('end_date', [$now, $now->copy()->addDays(30)])
                ->count(),
            'recent_leases' => $this->getRecentLeases(),
        ];
    }

    /**
     * أحدث العقود
     */
    private function getRecentLeases()
    {
        return RentalsLease::with(['unit.building', 'client'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($lease) {
                return [
                    'id' => $lease->id,
                    'client_name' => $lease->client->name ?? 'غير محدد',
                    'unit_name' => $lease->unit->name ?? 'غير محدد',
                    'building_name' => $lease->unit->building->name ?? 'غير محدد',
                    'rent_amount' => $lease->rent_amount,
                    'start_date' => $lease->start_date->format('Y-m-d'),
                    'end_date' => $lease->end_date->format('Y-m-d'),
                    'status' => $lease->status,
                ];
            });
    }

    /**
     * الإحصائيات المالية
     */
    private function getFinancialStats()
    {
        $activeLeases = RentalsLease::where('status', 'active')->get();

        return [
            'total_monthly_revenue' => $activeLeases->sum('rent_amount'),
            'total_yearly_revenue' => $activeLeases->sum('rent_amount') * 12,
            'average_rent' => $activeLeases->avg('rent_amount'),
            'highest_rent' => $activeLeases->max('rent_amount'),
            'lowest_rent' => $activeLeases->min('rent_amount'),
            'revenue_by_building' => $this->getRevenueByBuilding(),
        ];
    }

    /**
     * الإيرادات حسب المبنى
     */
    private function getRevenueByBuilding()
    {
        return RentalsBuilding::select(
            'rentals_buildings.id',
            'rentals_buildings.name',
            DB::raw('SUM(rentals_leases.rent_amount) as total_revenue'),
            DB::raw('COUNT(rentals_leases.id) as active_leases_count')
        )
            ->leftJoin('rentals_units', 'rentals_buildings.id', '=', 'rentals_units.building_id')
            ->leftJoin('rentals_leases', function ($join) {
                $join->on('rentals_units.id', '=', 'rentals_leases.unit_id')
                    ->where('rentals_leases.status', '=', 'active');
            })
            ->groupBy('rentals_buildings.id', 'rentals_buildings.name')
            ->get();
    }

    /**
     * بيانات الرسوم البيانية
     */
    private function getChartsData()
    {
        return [
            'monthly_leases' => $this->getMonthlyLeasesChart(),
            'revenue_trend' => $this->getRevenueTrendChart(),
            'occupancy_rate' => $this->getOccupancyRateChart(),
        ];
    }

    /**
     * بيانات رسم العقود الشهرية
     */
    private function getMonthlyLeasesChart()
    {
        return RentalsLease::select(
            DB::raw('DATE_FORMAT(start_date, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('start_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * بيانات اتجاه الإيرادات
     */
    private function getRevenueTrendChart()
    {
        return RentalsLease::select(
            DB::raw('DATE_FORMAT(start_date, "%Y-%m") as month'),
            DB::raw('SUM(rent_amount) as revenue')
        )
            ->where('status', 'active')
            ->where('start_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * بيانات معدل الإشغال
     */
    private function getOccupancyRateChart()
    {
        $buildings = RentalsBuilding::withCount([
            'units',
            'units as rented_units_count' => function ($query) {
            }
        ])->get();

        return $buildings->map(function ($building) {
            return [
                'name' => $building->name,
                'occupancy_rate' => $building->units_count > 0
                    ? round(($building->rented_units_count / $building->units_count) * 100, 2)
                    : 0,
            ];
        });
    }

    /**
     * تحديث Cache بشكل يدوي
     */
    public function refreshCache()
    {
        Cache::forget('rentals_dashboard_stats');
        return redirect()->route('rentals.dashboard.index')
            ->with('success', 'تم تحديث الإحصائيات بنجاح');
    }

    /**
     * API endpoint للحصول على الإحصائيات بصيغة JSON
     */
    public function apiStats()
    {
        $stats = $this->getDashboardStatistics();
        return response()->json($stats);
    }
}
