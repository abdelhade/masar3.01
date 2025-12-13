<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Fleet\Models\Vehicle;
use Modules\Fleet\Models\Trip;
use Modules\Fleet\Models\FuelRecord;
use Modules\Fleet\Enums\VehicleStatus;
use Modules\Fleet\Enums\TripStatus;

class FleetDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Fleet Dashboard')->only(['index']);
    }

    public function index()
    {
        // Vehicle Statistics
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', VehicleStatus::AVAILABLE)->count();
        $inUseVehicles = Vehicle::where('status', VehicleStatus::IN_USE)->count();
        $maintenanceVehicles = Vehicle::where('status', VehicleStatus::MAINTENANCE)->count();
        $outOfServiceVehicles = Vehicle::where('status', VehicleStatus::OUT_OF_SERVICE)->count();

        // Trip Statistics
        $todayTrips = Trip::whereDate('start_date', today())->count();
        $monthTrips = Trip::whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();
        $completedTrips = Trip::where('status', TripStatus::COMPLETED)->count();
        $inProgressTrips = Trip::where('status', TripStatus::IN_PROGRESS)->count();

        // Fuel Statistics
        $todayFuelCost = FuelRecord::whereDate('fuel_date', today())->sum('cost');
        $monthFuelCost = FuelRecord::whereMonth('fuel_date', now()->month)
            ->whereYear('fuel_date', now()->year)
            ->sum('cost');
        $totalFuelQuantity = FuelRecord::whereMonth('fuel_date', now()->month)
            ->whereYear('fuel_date', now()->year)
            ->sum('quantity');

        // Recent Trips
        $recentTrips = Trip::with(['vehicle', 'driver'])
            ->latest()
            ->limit(5)
            ->get();

        // Recent Fuel Records
        $recentFuelRecords = FuelRecord::with(['vehicle'])
            ->latest()
            ->limit(5)
            ->get();

        return view('fleet::dashboard.index', compact(
            'totalVehicles',
            'availableVehicles',
            'inUseVehicles',
            'maintenanceVehicles',
            'outOfServiceVehicles',
            'todayTrips',
            'monthTrips',
            'completedTrips',
            'inProgressTrips',
            'todayFuelCost',
            'monthFuelCost',
            'totalFuelQuantity',
            'recentTrips',
            'recentFuelRecords'
        ));
    }
}
