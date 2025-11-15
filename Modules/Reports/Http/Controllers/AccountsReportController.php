<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccHead;

class AccountsReportController extends Controller
{
        // accounts tree
    public function accountsTree()
    {
        // Load all accounts with recursive children relationships
        $accounts = AccHead::where('parent_id', null)
            ->with('children.children.children.children.children')
            ->get();
        return view('reports::accounts-reports.accounts-tree', compact('accounts'));
    }
}