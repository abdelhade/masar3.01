<?php

namespace Modules\Rentals\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Rentals\Models\RentalsLease;
use Modules\Rentals\Models\RentalsUnit;
use Modules\Rentals\Enums\LeaseStatus;
use Modules\Rentals\Enums\UnitStatus;

class RentalsReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Active Rentals (Leases that are ACTIVE)
        $activeLeases = RentalsLease::with(['unit.item', 'unit.building', 'client'])
            ->where('status', LeaseStatus::ACTIVE)
            ->get();

        // 2. Available Units (Units/Items that are AVAILABLE)
        $availableUnits = RentalsUnit::with(['item', 'building'])
            ->where('status', UnitStatus::AVAILABLE)
            ->get();
            
        return view('rentals::reports.index', compact('activeLeases', 'availableUnits'));
    }
}
