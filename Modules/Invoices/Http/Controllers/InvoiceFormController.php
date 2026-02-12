<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invoices\Repositories\InvoiceDataRepository;

/**
 * Controller for invoice form pages
 */
class InvoiceFormController extends Controller
{
    public function __construct(
        private readonly InvoiceDataRepository $invoiceDataRepository
    ) {}

    /**
     * Show create invoice form
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        $type = (int) $request->query('type', 10);
        $hash = $request->query('hash', '');
        $branchId = $request->query('branch_id');

        // If no branch_id provided, use user's default branch
        if (!$branchId && auth()->user()) {
            $branchId = auth()->user()->branch_id;
        }

        // Get editable fields order from template or default
        $editableFieldsOrder = ['unit', 'quantity', 'batch_number', 'expiry_date', 'price', 'discount', 'sub_value'];

        // Get branches
        $branches = \Modules\Branches\Models\Branch::all();

        // Get acc1Options based on invoice type using codes
        $acc1Options = [];
        if (in_array($type, [10, 12, 14, 16, 19, 22])) {
            // Sales invoices - Customers (code: 1103)
            $acc1Options = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '1103%')
                ->select('id', 'aname')
                ->get();
        } elseif (in_array($type, [11, 13, 15, 17, 20, 23, 24, 25])) {
            // Purchase invoices - Suppliers (code: 2101)
            $acc1Options = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '2101%')
                ->select('id', 'aname')
                ->get();
        }

        // Get stores (code: 1104)
        $acc2List = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1104%')
            ->select('id', 'aname')
            ->get();

        // Get employees (you need to specify the correct code)
        $employees = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '210201%')
            ->select('id', 'aname')
            ->get();

        // Get delivery delegates (you need to specify the correct code)
        $deliverys = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '210201%')
            ->select('id', 'aname')
            ->get();

        // Get cash accounts
        $cashAccounts = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('acc_type', 5) // Assuming acc_type 5 is cash
            ->select('id', 'aname')
            ->get();

        return view('invoices::invoices.create', [
            'type' => $type,
            'hash' => $hash,
            'branchId' => $branchId,
            'editableFieldsOrder' => $editableFieldsOrder,
            'branches' => $branches,
            'acc1Options' => $acc1Options,
            'acc2List' => $acc2List,
            'employees' => $employees,
            'deliverys' => $deliverys,
            'cashAccounts' => $cashAccounts
        ]);
    }

    /**
     * Show edit invoice form
     *
     * @param int $invoiceId
     * @return View
     */
    public function edit(int $invoiceId): View
    {
        // Get invoice to determine type
        $invoice = \App\Models\OperHead::findOrFail($invoiceId);

        return view('invoices::invoices.edit', [
            'invoiceId' => $invoiceId,
            'type' => $invoice->pro_type,
        ]);
    }
}
