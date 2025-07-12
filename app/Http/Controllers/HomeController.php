<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\{Item, User, Voucher, OperHead};

class HomeController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index()
    {
        $totalItems = Cache::remember('total_items', 600, fn() => Item::count());
        $totalUsers = Cache::remember('total_users', 600, fn() => User::count());
        $totalVouchers = Cache::remember('total_vouchers', 600, fn() => Voucher::count());
        $totalEmployees = Cache::remember('total_employees', 600, fn() => Employee::count());

        // Sales by month for the current year
        $salesByMonth = Cache::remember('sales_by_month_' . now()->year, 600, function () {
            return OperHead::selectRaw('MONTH(created_at) as month, SUM(pro_value) as total')
                ->where('pro_type', 10)
                ->whereYear('created_at', now()->year)
                ->groupByRaw('MONTH(created_at)')
                ->pluck('total', 'month')
                ->toArray();
        });

        // Top 5 selling items
        $topItems = Cache::remember('top_items', 600, function () {
            return Item::select('items.name', DB::raw('SUM(operation_items.qty_in) as total_sold'))
                ->join('operation_items', 'items.id', '=', 'operation_items.item_id')
                ->join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
                ->where('operhead.pro_type', 10)
                ->whereYear('operhead.created_at', now()->year)
                ->groupBy('items.id', 'items.name')
                ->orderByDesc('total_sold')
                ->take(5)
                ->get();
        });

        // Recent 5 transactions
        $recentTransactions = Cache::remember('recent_transactions', 600, function () {
            return OperHead::where('pro_type', 10)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(['id', 'created_at', 'pro_value']);
        });

        return view('home', compact(
            'totalItems',
            'totalUsers',
            'totalVouchers',
            'totalEmployees',
            'salesByMonth',
            'topItems',
            'recentTransactions'
        ));
    }
}
