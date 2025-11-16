<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Modules\Accounts\Models\AccHead;

/**
 * @deprecated This controller is kept for backward compatibility only.
 * All report functionality has been moved to Modules/Reports.
 * Please use the appropriate controllers in Modules/Reports/Http/Controllers instead.
 */
class ReportController extends Controller
{
    /**
     * @deprecated This method is kept for backward compatibility.
     * The view has been moved to Modules/Reports/Resources/views/general-reports/index.blade.php
     * Use Modules/Reports/Http/Controllers/GeneralReportController instead.
     */
    public function index()
    {
        return view('reports::general-reports.index');
    }

    /**
     * @deprecated This method is kept for backward compatibility.
     * The view has been moved to Modules/Reports/Resources/views/general-reports/accounts-balance.blade.php
     * Use Modules/Reports/Http/Controllers/AccountsReportController instead.
     */
    public function accountsBalance()
    {
        $accounts = AccHead::where('parent_id', 0)->get();
        return view('reports::general-reports.accounts-balance', compact('accounts'));
    }
}
