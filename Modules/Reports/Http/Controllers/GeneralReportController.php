<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Models\User;
use App\Models\ProType;
use App\Models\OperHead;
use App\Models\JournalHead;
use Illuminate\Routing\Controller;
use Modules\Reports\Services\ReportCalculationTrait;

class GeneralReportController extends Controller
{
    use ReportCalculationTrait;

    public function __construct()
    {
        $this->middleware('can:view account-movement-report');
    }

    public function index(): \Illuminate\Contracts\View\View
    {
        return view('reports::general-reports.index');
    }

    // محلل العمل اليومي
    public function overall()
    {
        // Get filters from request
        $userId = request('user_id');
        $typeId = request('type_id');
        $dateFrom = request('date_from') ?: now()->startOfDay();
        $dateTo = request('date_to') ?: now()->endOfDay();

        // Build query with filters
        $query = OperHead::with(['user', 'type']);

        if ($userId) {
            $query->where('user', $userId);
        }

        if ($typeId) {
            $query->where('pro_type', $typeId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $opers = $query->orderBy('created_at', 'desc')
            ->paginate(100);

        // Get users for the filter dropdown
        $users = User::all();

        // Get operation types for the filter dropdown
        $types = \App\Models\ProType::all();

        return view('reports::general-reports.overall', compact('opers', 'users', 'types'));
    }

    // اليومية العامة
    public function journalSummery()
    {
        $journalHeads = JournalHead::with(['dets' => function ($query) {
            $query->orderBy('debit', 'desc');
        }])->orderBy('journal_id', 'desc')->orderBy('date', 'desc')->get();

        // جلب أنواع العمليات من قاعدة البيانات
        $operationTypes = ProType::where('isdeleted', 0)
            ->orderBy('ptext', 'asc')
            ->get();

        return view('reports::general-reports.journal-summery', compact('journalHeads', 'operationTypes'));
    }

    // كشف حساب عام - تفاصيل اليومية
    public function generalJournalDetails()
    {
        return view('reports::general-reports.general-journal-details');
    }

    public function dailyActivityAnalyzer()
    {
        return view('reports::general-reports.daily-activity-analyzer');
    }

    public function generalCashboxMovementReport()
    {
        return view('reports::general-reports.general-cashbox-movement-report');
    }
}
