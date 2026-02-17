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

        // Get invoice settings and user permissions
        $isSales = in_array($type, [10, 12, 14, 16, 19, 22]);
        $isPurchase = in_array($type, [11, 13, 15, 17, 20, 23, 24, 25]);
        
        $defaultAcc1Id = null;
        if ($isSales) {
            $defaultAcc1Id = 61; // العميل النقدي
        } elseif ($isPurchase) {
            $defaultAcc1Id = 64; // المورد النقدي
        }

        $defaultAcc2Id = $acc2List->first()?->id;

        $canEditStore = auth()->user()->id == 1 || !auth()->user()->can('prevent_editing_store');

        $userSettings = [
            // General Settings
            'multi_currency_enabled' => setting('multi_currency_enabled', false),
            
            // Invoice Settings
            'prevent_negative_invoice' => setting('prevent_negative_invoice', true),
            'new_after_save' => setting('new_after_save', true),
            'allow_edit_price_payments' => setting('allow_edit_price_payments', true),
            'allow_zero_price_in_invoice' => setting('allow_zero_price_in_invoice', true),
            'allow_zero_opening_balance' => setting('allow_zero_opening_balance', true),
            'allow_zero_invoice_total' => setting('allow_zero_invoice_total', true),
            'allow_edit_invoice_value' => setting('allow_edit_invoice_value', true),
            'change_quantity_on_value_edit' => setting('change_quantity_on_value_edit', true),
            'prevent_duplicate_items_in_sales' => setting('prevent_duplicate_items_in_sales', true),
            'prevent_duplicate_items_in_purchases' => setting('prevent_duplicate_items_in_purchases', true),
            'print_free_quantity_separately' => setting('print_free_quantity_separately', true),
            'allow_purchase_price_change' => setting('allow_purchase_price_change', true),
            'show_unit_with_conversion_factor' => setting('show_unit_with_conversion_factor', true),
            'show_due_date_in_invoices' => setting('show_due_date_in_invoices', true),
            'default_quantity_greater_than_zero' => setting('default_quantity_greater_than_zero', true),
            'allow_hide_items_by_company' => setting('allow_hide_items_by_company', true),
            
            // Display Settings
            'invoice_show_item_details' => setting('invoice_show_item_details', true),
            'invoice_show_recommended_items' => setting('invoice_show_recommended_items', false),
            
            // Tax Settings
            'is_vat_enabled' => isVatEnabled(),
            'vat_level' => getVatLevel(),
            'is_withholding_tax_enabled' => isWithholdingTaxEnabled(),
            'withholding_tax_level' => getWithholdingTaxLevel(),
            
            // Expiry Date Settings
            'expiry_mode' => [
                'disabled' => setting('expiry_mode_disabled', false),
                'nearest_first' => setting('expiry_mode_nearest_first', true),
                'show_all' => setting('expiry_mode_show_all', false),
            ],
            
            // Permissions
            'permissions' => [
                'prevent_transactions_without_stock' => auth()->user()->can('prevent_transactions_without_stock'),
                'prevent_editing_store' => !$canEditStore,
                'allow_price_change' => auth()->user()->can('allow_price_change'),
                'allow_discount_change' => auth()->user()->can('allow_discount_change'),
                'allow_purchase_with_zero_price' => auth()->user()->can('allow_purchase_with_zero_price'),
            ]
        ];

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
            'cashAccounts' => $cashAccounts,
            'userSettings' => $userSettings,
            'defaultAcc1Id' => $defaultAcc1Id,
            'defaultAcc2Id' => $defaultAcc2Id,
            'canEditStore' => $canEditStore
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
