<?php

namespace Modules\Checks\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Checks\Models\Check;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\Storage;
use Modules\Checks\Services\CheckService;
use Modules\Checks\Services\CheckPortfolioService;
use Modules\Checks\Http\Requests\ClearCheckRequest;
use Modules\Checks\Http\Requests\StoreCheckRequest;
use Modules\Checks\Services\CheckAccountingService;
use Modules\Checks\Http\Requests\BatchCancelRequest;
use Modules\Checks\Http\Requests\UpdateCheckRequest;
use Modules\Checks\Http\Requests\BatchCollectRequest;
use Modules\Checks\Http\Requests\CollectCheckRequest;

class ChecksController extends Controller
{
    public function __construct(
        private CheckService $checkService,
        private CheckAccountingService $accountingService,
        private CheckPortfolioService $portfolioService
    ) {
        $this->middleware('can:view check-portfolios-incoming')->only(['incoming', 'createIncoming', 'management', 'dashboard', 'statistics']);
        $this->middleware('can:view check-portfolios-outgoing')->only(['outgoing', 'createOutgoing']);
        $this->middleware('can:create check-portfolios-incoming')->only(['createIncoming', 'store']);
        $this->middleware('can:create check-portfolios-outgoing')->only(['createOutgoing', 'store']);
        // Store, Update, Destroy, etc. will have internal checks because they are shared
    }

    /**
     * Display the main checks page
     */
    public function index(Request $request)
    {
        return redirect()->route('checks.incoming');
    }

    /**
     * Display incoming checks (أوراق القبض)
     */
    public function incoming(Request $request)
    {
        $checks = $this->checkService->getChecks([
            'type' => 'incoming',
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'days_ahead' => $request->get('days_ahead'),
            'bank_name' => $request->get('bank_name'),
            'account_number' => $request->get('account_number'),
            'payee_name' => $request->get('payee_name'),
            'payer_name' => $request->get('payer_name'),
            'amount_min' => $request->get('amount_min'),
            'amount_max' => $request->get('amount_max'),
            'issue_date_from' => $request->get('issue_date_from'),
            'issue_date_to' => $request->get('issue_date_to'),
        ]);

        $pageType = 'incoming';
        $pageTitle = 'أوراق القبض';

        return view('checks::index', compact('checks', 'pageType', 'pageTitle'));
    }

    /**
     * Show create form for incoming check
     */
    public function createIncoming()
    {
        $pageType = 'incoming';
        $pageTitle = 'إضافة ورقة قبض';

        // تحميل الحسابات الطبيعية (عملاء، موردين، موظفين، دائنين، مدينين)
        $accountTypes = [
            1 => 'العملاء',
            2 => 'الموردين',
            5 => 'الموظفين',
            9 => 'الدائنين',
            10 => 'المدينين',
        ];

        $groupedAccounts = [];
        foreach ($accountTypes as $typeId => $typeName) {
            $accounts = AccHead::where('is_basic', 0)
                ->where('isdeleted', 0)
                ->where('acc_type', $typeId)
                ->select('id', 'aname', 'code', 'balance')
                ->orderBy('code')
                ->get();

            if ($accounts->isNotEmpty()) {
                $groupedAccounts[$typeName] = $accounts;
            }
        }

        return view('checks::create', compact('pageType', 'pageTitle', 'groupedAccounts'));
    }

    /**
     * Display outgoing checks (أوراق الدفع)
     */
    public function outgoing(Request $request)
    {
        $checks = $this->checkService->getChecks([
            'type' => 'outgoing',
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'days_ahead' => $request->get('days_ahead'),
            'bank_name' => $request->get('bank_name'),
            'account_number' => $request->get('account_number'),
            'payee_name' => $request->get('payee_name'),
            'payer_name' => $request->get('payer_name'),
            'amount_min' => $request->get('amount_min'),
            'amount_max' => $request->get('amount_max'),
            'issue_date_from' => $request->get('issue_date_from'),
            'issue_date_to' => $request->get('issue_date_to'),
        ]);

        $pageType = 'outgoing';
        $pageTitle = 'أوراق الدفع';

        return view('checks::index', compact('checks', 'pageType', 'pageTitle'));
    }

    /**
     * Show create form for outgoing check
     */
    public function createOutgoing()
    {
        $pageType = 'outgoing';
        $pageTitle = 'إضافة ورقة دفع';

        // تحميل الحسابات الطبيعية (عملاء، موردين، موظفين، دائنين، مدينين)
        $accountTypes = [
            1 => 'العملاء',
            2 => 'الموردين',
            5 => 'الموظفين',
            9 => 'الدائنين',
            10 => 'المدينين',
        ];

        $groupedAccounts = [];
        foreach ($accountTypes as $typeId => $typeName) {
            $accounts = AccHead::where('is_basic', 0)
                ->where('isdeleted', 0)
                ->where('acc_type', $typeId)
                ->select('id', 'aname', 'code', 'balance')
                ->orderBy('code')
                ->get();

            if ($accounts->isNotEmpty()) {
                $groupedAccounts[$typeName] = $accounts;
            }
        }

        return view('checks::create', compact('pageType', 'pageTitle', 'groupedAccounts'));
    }

    /**
     * Show the dashboard with statistics
     */
    public function dashboard(Request $request)
    {
        $dateFilter = $request->get('date_filter', 'month');
        $dateRange = $this->getDateRange($dateFilter);

        $stats = $this->checkService->getStatistics($dateRange);

        $overdueChecks = Check::where('status', Check::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $recentChecks = Check::with(['creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $checksByBank = Check::whereBetween('created_at', $dateRange)
            ->select('bank_name', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('bank_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $monthlyTrend = Check::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('count(*) as count'),
            DB::raw('sum(amount) as total_amount')
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('checks::dashboard', compact(
            'stats',
            'overdueChecks',
            'recentChecks',
            'checksByBank',
            'monthlyTrend',
            'dateFilter'
        ));
    }

    /**
     * Show the management page
     */
    public function management(Request $request)
    {
        $checks = $this->checkService->getChecks([
            'type' => 'incoming',
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ]);

        $pageType = 'incoming';
        $pageTitle = 'إدارة الشيكات';
        $defaultTab = 'management';

        return view('checks::index', compact('checks', 'pageType', 'pageTitle', 'defaultTab'));
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(Check $check, $attachmentIndex)
    {
        if (! $check->attachments || ! isset($check->attachments[$attachmentIndex])) {
            abort(404, 'Attachment not found');
        }

        $attachment = $check->attachments[$attachmentIndex];
        $filePath = $attachment['path'];

        if (! Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk('public')->path($filePath);

        return response()->download($fullPath, $attachment['name']);
    }

    /**
     * Export checks to Excel/CSV
     */
    public function export(Request $request)
    {
        $checks = $this->checkService->getChecks([
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'bank_name' => $request->get('bank_name'),
            'per_page' => 10000, // Get all for export
        ])->items();

        $filename = 'checks_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($checks) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for proper Arabic encoding
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV headers
            fputcsv($file, [
                'رقم الشيك',
                'البنك',
                'رقم الحساب',
                'صاحب الحساب',
                'المبلغ',
                'تاريخ الإصدار',
                'تاريخ الاستحقاق',
                'تاريخ الدفع',
                'الحالة',
                'النوع',
                'المستفيد',
                'الدافع',
                'رقم المرجع',
                'ملاحظات',
                'أنشئ بواسطة',
                'تاريخ الإنشاء',
            ]);

            // Data rows
            foreach ($checks as $check) {
                fputcsv($file, [
                    $check->check_number,
                    $check->bank_name,
                    $check->account_number,
                    $check->account_holder_name,
                    $check->amount,
                    $check->issue_date->format('Y-m-d'),
                    $check->due_date->format('Y-m-d'),
                    $check->payment_date ? $check->payment_date->format('Y-m-d') : '',
                    $check->status,
                    $check->type,
                    $check->payee_name,
                    $check->payer_name,
                    $check->reference_number,
                    $check->notes,
                    $check->creator->name ?? '',
                    $check->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show single check
     */
    public function show(Check $check)
    {
        $check->load(['creator', 'approver']);

        return response()->json($check);
    }

    /**
     * Show edit form data
     */
    public function edit(Check $check)
    {
        return response()->json($check);
    }

    /**
     * Store a newly created check
     */
    public function store(StoreCheckRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['pro_date'] = $validated['issue_date'];
            $validated['customer_id'] = $validated['type'] === 'incoming' ? $validated['acc1_id'] : null;
            $validated['supplier_id'] = $validated['type'] === 'outgoing' ? $validated['acc1_id'] : null;

            $this->checkService->createCheck($validated);

            // Redirect to the appropriate index page based on check type
            $routeName = $validated['type'] === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)->with('success', 'تم إضافة الشيك وإنشاء القيد المحاسبي بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update the specified check
     */
    public function update(UpdateCheckRequest $request, Check $check)
    {
        try {
            $this->checkService->updateCheck($check, $request->validated());

            // Redirect to the appropriate index page based on check type
            $routeName = $check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)->with('success', 'تم تحديث الشيك بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete the specified check
     */
    public function destroy(Check $check)
    {
        try {
            $this->checkService->deleteCheck($check);

            return response()->json(['success' => true, 'message' => 'تم حذف الشيك بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show collect check page (صفحة تحصيل الورقة)
     */
    public function collect(Check $check)
    {
        if ($check->status !== Check::STATUS_PENDING) {
            $routeName = $check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)
                ->with('error', 'لا يمكن تحصيل ورقة غير معلقة');
        }

        // تحميل حسابات البنوك
        $bankAccounts = AccHead::where('is_basic', 0)
            ->where('isdeleted', 0)
            ->where('code', 'like', '1102%')
            ->select('id', 'aname', 'code', 'balance')
            ->orderBy('code')
            ->get();

        // تحميل حسابات الصناديق
        $cashAccounts = AccHead::where('is_basic', 0)
            ->where('isdeleted', 0)
            ->where(function ($query) {
                $query->where('is_fund', 1)
                    ->orWhere('is_cash', 1)
                    ->orWhere('code', 'like', '1101%');
            })
            ->select('id', 'aname', 'code', 'balance')
            ->orderBy('code')
            ->get();

        $pageTitle = $check->type === 'incoming' ? 'تحصيل ورقة قبض' : 'تحصيل ورقة دفع';

        return view('checks::collect', compact('check', 'bankAccounts', 'cashAccounts', 'pageTitle'));
    }

    /**
     * Store collect check (تنفيذ تحصيل الورقة)
     */
    public function storeCollect(CollectCheckRequest $request, Check $check)
    {
        try {
            if ($check->status !== Check::STATUS_PENDING) {
                return redirect()->route('checks.collect', $check)
                    ->with('error', 'لا يمكن تحصيل ورقة غير معلقة');
            }

            $validated = $request->validated();

            $this->accountingService->collectCheck(
                $check,
                $validated['account_type'],
                $validated['account_id'],
                $validated['collection_date'],
                $validated['branch_id']
            );

            $routeName = $check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)
                ->with('success', 'تم تحصيل الورقة بنجاح وإنشاء القيد المحاسبي');
        } catch (\Exception $e) {
            return redirect()->route('checks.collect', $check)
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Show clear check page (صفحة تظهير الورقة)
     */
    public function showClear(Check $check)
    {
        if ($check->status !== Check::STATUS_PENDING) {
            $routeName = $check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)
                ->with('error', 'لا يمكن تظهير ورقة غير معلقة');
        }

        // تحميل جميع الحسابات
        $accounts = AccHead::where('is_basic', 0)
            ->where('isdeleted', 0)
            ->select('id', 'aname', 'code', 'balance')
            ->orderBy('code')
            ->get();

        $pageTitle = $check->type === 'incoming' ? 'تظهير ورقة قبض' : 'تظهير ورقة دفع';

        return view('checks::clear', compact('check', 'accounts', 'pageTitle'));
    }

    /**
     * Clear a check (تحصيل الشيك - تحويل للبنك)
     */
    public function clear(ClearCheckRequest $request, Check $check)
    {
        try {
            $validated = $request->validated();

            $this->accountingService->clearCheck(
                $check,
                $validated['bank_account_id'],
                $validated['collection_date'],
                $validated['branch_id']
            );

            $routeName = $check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)
                ->with('success', 'تم تظهير الورقة بنجاح وإنشاء القيد المحاسبي');
        } catch (\Exception $e) {
            return redirect()->route('checks.show-clear', $check)
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Show cancel with reversal entry page (صفحة إلغاء بقيد عكسي)
     */
    public function showCancelReversal(Check $check)
    {
        if ($check->status === Check::STATUS_CANCELLED) {
            $routeName = $check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)
                ->with('error', 'الورقة ملغاة بالفعل');
        }

        $pageTitle = $check->type === 'incoming' ? 'إلغاء ورقة قبض بقيد عكسي' : 'إلغاء ورقة دفع بقيد عكسي';

        return view('checks::cancel-reversal', compact('check', 'pageTitle'));
    }

    /**
     * Cancel check with reversal entry (إلغاء بقيد عكسي)
     */
    public function cancelReversal(Request $request, Check $check)
    {
        try {
            $request->validate([
                'branch_id' => ['required', 'exists:branches,id'],
            ]);

            $this->accountingService->cancelCheckWithReversal($check, $request->branch_id);

            $routeName = $check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing';

            return redirect()->route($routeName)
                ->with('success', 'تم إلغاء الورقة بقيد عكسي بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('checks.show-cancel-reversal', $check)
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Batch collect checks
     */
    public function batchCollect(BatchCollectRequest $request)
    {
        try {
            $validated = $request->validated();

            $processedCount = $this->accountingService->batchCollectChecks(
                $validated['ids'],
                $validated['bank_account_id'],
                $validated['collection_date'],
                $validated['branch_id']
            );

            return response()->json([
                'success' => true,
                'message' => "تم تحصيل {$processedCount} شيك بنجاح",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batch cancel with reversal entry
     */
    public function batchCancelReversal(BatchCancelRequest $request)
    {
        try {
            $validated = $request->validated();
            $checks = Check::whereIn('id', $validated['ids'])->get();

            foreach ($checks as $check) {
                $this->accountingService->cancelCheckWithReversal($check, $validated['branch_id']);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الشيكات بقيد عكسي بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get checks statistics API endpoint
     */
    public function statistics(Request $request)
    {
        $dateRange = $this->getDateRange($request->get('period', 'month'));

        $stats = $this->checkService->getStatistics($dateRange);

        return response()->json($stats);
    }

    /**
     * Get accounts for Tom Select (AJAX)
     */
    public function getAccounts(Request $request)
    {
        $search = $request->get('search', '');

        // فلترة الحسابات الطبيعية فقط (عملاء، موردين، موظفين، دائنين، مدينين)
        $allowedTypes = [1, 2, 5, 9, 10];

        $query = AccHead::where('is_basic', 0)
            ->where('isdeleted', 0)
            ->whereIn('acc_type', $allowedTypes);

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('aname', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $accounts = $query->select('id', 'aname', 'code', 'balance', 'acc_type')
            ->orderBy('acc_type')
            ->orderBy('code')
            ->limit(100)
            ->get();

        return response()->json([
            'results' => $accounts->map(function ($account) {
                return [
                    'value' => $account->id,
                    'text' => "[{$account->code}] {$account->aname} - " . number_format($account->balance ?? 0, 2) . ' ر.س',
                    'balance' => $account->balance ?? 0,
                ];
            })->toArray(),
        ]);
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period): array
    {
        return match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
