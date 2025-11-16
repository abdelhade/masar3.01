<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\JournalDetail;
use Modules\Reports\Services\ReportCalculationTrait;

class CostCenterReportController extends Controller
{
    use ReportCalculationTrait;

    public function generalCostCentersReport()
    {
        $costCenters = CostCenter::all();

        $costCenterTransactions = JournalDetail::with(['accHead', 'head', 'costCenter'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('cost_center_id'), function ($q) {
                $q->where('cost_center', request('cost_center_id'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalExpenses = $costCenterTransactions->sum('debit');
        $totalRevenues = $costCenterTransactions->sum('credit');
        $netCost = $totalExpenses - $totalRevenues;
        $totalTransactions = $costCenterTransactions->count();

        return view('reports::cost-centers.general-cost-centers-report', compact(
            'costCenters',
            'costCenterTransactions',
            'totalExpenses',
            'totalRevenues',
            'netCost',
            'totalTransactions'
        ));
    }

    public function generalCostCenterAccountStatement()
    {
        $costCenters = CostCenter::all();
        $selectedCostCenter = null;
        $costCenterTransactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        $openingBalance = 0;
        $closingBalance = 0;

        if (request('cost_center_id')) {
            $selectedCostCenter = CostCenter::find(request('cost_center_id'));
            if ($selectedCostCenter) {
                $fromDate = request('from_date');
                $toDate = request('to_date');

                $costCenterTransactions = JournalDetail::where('cost_center', $selectedCostCenter->id)
                    ->with(['head', 'accHead'])
                    ->when($fromDate, function ($q) use ($fromDate) {
                        $q->whereDate('crtime', '>=', $fromDate);
                    })
                    ->when($toDate, function ($q) use ($toDate) {
                        $q->whereDate('crtime', '<=', $toDate);
                    })
                    ->orderBy('crtime', 'asc')
                    ->paginate(50);

                $openingBalance = $this->calculateCostCenterBalance($selectedCostCenter->id, $fromDate);
                $closingBalance = $this->calculateCostCenterBalance($selectedCostCenter->id, $toDate);
            }
        }

        return view('reports::cost-centers.general-cost-center-account-statement', compact(
            'costCenters',
            'selectedCostCenter',
            'costCenterTransactions',
            'openingBalance',
            'closingBalance'
        ));
    }

    public function generalCostCentersList()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $costCenterType = request('cost_center_type');
        $search = request('search');

        $costCenters = CostCenter::when($costCenterType, function ($q) use ($costCenterType) {
            $q->where('type', $costCenterType);
        })
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })
            ->paginate(50)
            ->through(function ($center) use ($asOfDate) {
                $center->total_expenses = $this->calculateCostCenterExpenses($center->id, $asOfDate);
                $center->total_revenues = $this->calculateCostCenterRevenues($center->id, $asOfDate);
                $center->net_cost = $center->total_expenses - $center->total_revenues;
                return $center;
            });

        $totalExpenses = $costCenters->sum('total_expenses');
        $totalRevenues = $costCenters->sum('total_revenues');
        $totalNetCost = $costCenters->sum('net_cost');
        $totalCostCenters = $costCenters->count();
        $activeCostCenters = $costCenters->where('is_active', true)->count();
        $averageCostPerCenter = $totalCostCenters > 0 ? $totalNetCost / $totalCostCenters : 0;

        return view('reports::cost-centers.general-cost-centers-list', compact(
            'costCenters',
            'totalExpenses',
            'totalRevenues',
            'totalNetCost',
            'totalCostCenters',
            'activeCostCenters',
            'averageCostPerCenter',
            'asOfDate'
        ));
    }
}

