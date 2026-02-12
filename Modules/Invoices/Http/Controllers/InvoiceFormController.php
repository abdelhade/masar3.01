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

        // Fetch initial data using repository
        $data = $this->invoiceDataRepository->getInitialData($type, $branchId);

        // Prepare acc1Options based on invoice type
        $acc1Options = [];
        if (in_array($type, [10, 12, 14, 16, 19, 22])) {
            // Sales invoices - use customers
            $acc1Options = collect($data['accounts']['customers'])->map(function ($acc) {
                return (object) ['id' => $acc['id'], 'aname' => $acc['name']];
            });
        } elseif (in_array($type, [11, 13, 15, 17, 20, 23])) {
            // Purchase invoices - use suppliers
            $acc1Options = collect($data['accounts']['suppliers'])->map(function ($acc) {
                return (object) ['id' => $acc['id'], 'aname' => $acc['name']];
            });
        }

        // Get stores, employees, delivery, cash accounts
        $acc2List = \Modules\Accounts\Models\AccHead::where('acc_type', 2)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->get(['id', 'aname']);
        
        $employees = \Modules\Accounts\Models\AccHead::where('acc_type', 3)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->get(['id', 'aname']);
        
        $deliverys = \Modules\Accounts\Models\AccHead::where('acc_type', 4)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->get(['id', 'aname']);
        
        $cashAccounts = collect($data['accounts']['cash_accounts'])->map(function ($acc) {
            return (object) ['id' => $acc['id'], 'aname' => $acc['name']];
        });

        return view('invoices::invoices.create', [
            'type' => $type,
            'hash' => $hash,
            'branchId' => $branchId,
            'editableFieldsOrder' => $editableFieldsOrder,
            'branches' => collect($data['branches'])->map(fn($b) => (object) $b),
            'acc1Options' => $acc1Options,
            'acc2List' => $acc2List,
            'employees' => $employees,
            'deliverys' => $deliverys,
            'cashAccounts' => $cashAccounts,
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
