<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\LoginSession;
use App\Models\OperHead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Accounts\Models\AccHead;

class DashboardController extends Controller
{
    public function index()
    {
        // الكروت العلوية
        $totalClients = Client::where('isdeleted', 0)->count();
        $totalLogins = LoginSession::count();
        $todaySales = OperHead::where('pro_type', 10)
            ->where('isdeleted', 0)
            ->whereDate('pro_date', today())
            ->sum('fat_net') ?? 0;

        // آخر 5 حسابات تم إنشاءها
        $recentAccounts = AccHead::orderBy('id', 'desc')
            ->limit(5)
            ->get(['id', 'code', 'aname']);

        // آخر 5 أصناف
        $recentItems = Item::where('isdeleted', 0)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'code', 'created_at']);

        // آخر 5 عمليات
        $recentOperations = OperHead::where('isdeleted', 0)
            ->with(['acc1Head:id,aname', 'user:id,name'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get(['id', 'pro_id', 'pro_type', 'pro_date', 'fat_net', 'acc1', 'user', 'pro_serial']);

        // آخر 5 عمليات تسجيل دخول
        $recentLogins = LoginSession::with('user:id,name')
            ->orderBy('login_at', 'desc')
            ->limit(5)
            ->get(['id', 'user_id', 'ip_address', 'login_at', 'logout_at']);

        // إحصائيات المبيعات
        $salesStats = [
            'last_invoice' => OperHead::where('pro_type', 10)
                ->where('isdeleted', 0)
                ->orderBy('id', 'desc')
                ->first(['id', 'pro_id', 'fat_net', 'pro_date']),
            'today' => OperHead::where('pro_type', 10)
                ->where('isdeleted', 0)
                ->whereDate('pro_date', today())
                ->sum('fat_net') ?? 0,
            'last_week' => OperHead::where('pro_type', 10)
                ->where('isdeleted', 0)
                ->whereBetween('pro_date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                ->sum('fat_net') ?? 0,
            'last_month' => OperHead::where('pro_type', 10)
                ->where('isdeleted', 0)
                ->whereMonth('pro_date', Carbon::now()->subMonth()->month)
                ->whereYear('pro_date', Carbon::now()->subMonth()->year)
                ->sum('fat_net') ?? 0,
        ];

        return view('admin.main-dashboard', compact(
            'totalClients',
            'totalLogins',
            'todaySales',
            'recentAccounts',
            'recentItems',
            'recentOperations',
            'recentLogins',
            'salesStats'
        ));
    }
}
