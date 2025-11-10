<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Note;
use App\Models\User;
use App\Models\AccHead;
use App\Models\OperHead;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use App\Models\JournalDetail;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function overall()
    {
        // Get filters from request
        $userId = request('user_id');
        $typeId = request('type_id');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

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
            ->take(100)
            ->get();

        // Get users for the filter dropdown
        $users = User::all();

        // Get operation types for the filter dropdown
        $types = \App\Models\ProType::all();

        return view('reports.overall', compact('opers', 'users', 'types'));
    }

    // accounts tree
    public function accountsTree()
    {
        // Load all accounts with recursive children relationships
        $accounts = AccHead::where('parent_id', null)
            ->with('children.children.children.children.children')
            ->get();
        return view('reports.accounts-tree', compact('accounts'));
    }

    // accounts balance
    public function accountsBalance()
    {
        $accounts = AccHead::where('parent_id', 0)->get();
        return view('reports.accounts-balance', compact('accounts'));
    }

    // محلل العمل اليومي
    public function dailyActivityAnalyzer()
    {
        $users = User::all();
        $operations = OperHead::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('reports.daily-activity-analyzer', compact('users', 'operations'));
    }

    // اليومية العامة
    public function generalJournal()
    {
        $accounts = AccHead::where('isdeleted', 0)->get();
        $journalDetails = JournalDetail::with(['head', 'accHead', 'costCenter'])
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        return view('reports.general-journal', compact('accounts', 'journalDetails'));
    }

    // الميزانية العمومية
    public function generalBalanceSheet()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));

        // جميع الأصول الرئيسية (الحسابات التي ليس لديها parent)
        $assets = AccHead::where('code', 'like', '1%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code');
            }, 'children.children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code');
            }])
            ->orderBy('code')
            ->get();

        // نفس الشيء للخصوم وحقوق الملكية
        $liabilities = AccHead::where('code', 'like', '2%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code');
            }, 'children.children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code');
            }])
            ->orderBy('code')
            ->get();

        $equity = AccHead::where('code', 'like', '3%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code');
            }, 'children.children' => function ($q) {
                $q->where('isdeleted', 0)->orderBy('code');
            }])
            ->orderBy('code')
            ->get();

        return view('reports.general-balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'asOfDate'
        ));
    }

    // كشف حساب حساب
    public function generalAccountStatement()
    {
        $accounts = AccHead::where('is_deleted', 0)->get();
        $selectedAccount = null;
        $movements = new LengthAwarePaginator([], 0, 50);
        $openingBalance = 0;
        $closingBalance = 0;

        if (request('account_id')) {
            $selectedAccount = AccHead::find(request('account_id'));
            if ($selectedAccount) {
                $fromDate = request('from_date');
                $toDate = request('to_date');

                $movements = JournalDetail::where('account_id', $selectedAccount->id)
                    ->with(['head', 'costCenter'])
                    ->when($fromDate, function ($q) use ($fromDate) {
                        $q->whereDate('crtime', '>=', $fromDate);
                    })
                    ->when($toDate, function ($q) use ($toDate) {
                        $q->whereDate('crtime', '<=', $toDate);
                    })
                    ->orderBy('crtime', 'asc')
                    ->paginate(50);

                // Calculate opening and closing balances
                $openingBalance = $this->calculateAccountBalance($selectedAccount->id, $fromDate);
                $closingBalance = $this->calculateAccountBalance($selectedAccount->id, $toDate);
            }
        }

        return view('reports.general-account-statement', compact(
            'accounts',
            'selectedAccount',
            'movements',
            'openingBalance',
            'closingBalance'
        ));
    }

    // ميزان الحسابات
    public function generalAccountBalances()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $accountGroup = request('account_group');

        $query = AccHead::where('isdeleted', 0)->orderBy('code', 'asc');

        if ($accountGroup) {
            $query->where('code', 'like', $accountGroup . '%');
        }

        $accountBalances = $query->paginate(200)->through(function ($account) use ($asOfDate) {
            $balance = $this->calculateAccountBalance($account->id, $asOfDate);
            $debit = $balance > 0 ? $balance : 0;
            $credit = $balance < 0 ? abs($balance) : 0;

            $account->debit = $debit;
            $account->credit = $credit;
            $account->balance = $balance;
            return $account;
        });

        $totalDebit = $accountBalances->sum('debit');
        $totalCredit = $accountBalances->sum('credit');
        $totalBalance = $accountBalances->sum('balance');

        return view('reports.general-account-balances', compact(
            'accountBalances',
            'totalDebit',
            'totalCredit',
            'totalBalance',
            'asOfDate'
        ));
    }

    // قائمة الأصناف مع الأرصدة كل المخازن
    public function generalInventoryBalances()
    {
        $notes = Note::with('noteDetails')->get();
        $warehouses = AccHead::where('code', 'like', '%1104')->where('isdeleted', 0)->get();

        $inventoryBalances = Item::with(['units'])
            ->paginate(50)
            ->through(function ($item) {
                $item->current_balance = $this->calculateItemBalance($item->id);
                $item->min_balance = $item->min_balance ?? 0;
                $item->max_balance = $item->max_balance ?? 999999;
                $item->main_unit = $item->units->first();
                return $item;
            });

        $totalBalance = $inventoryBalances->sum('current_balance');
        $totalItems = $inventoryBalances->count();
        $lowStockItems = $inventoryBalances->where('current_balance', '<=', 'min_balance')->count();
        $normalStockItems = $inventoryBalances->where('current_balance', '>', 'min_balance')->count();

        return view('reports.general-inventory-balances', compact(
            'notes',
            'warehouses',
            'inventoryBalances',
            'totalBalance',
            'totalItems',
            'lowStockItems',
            'normalStockItems'
        ));
    }

    // قائمة الأصناف مع الأرصدة مخزن معين
    public function generalInventoryBalancesByStore()
    {
        $warehouses = AccHead::where('code', 'like', '%123')->where('isdeleted', 0)->get();
        $notes = Note::with('noteDetails')->get();
        $selectedWarehouse = null;
        $inventoryBalances = Item::whereRaw('0=1')->paginate(50);

        if (request('warehouse_id')) {
            $selectedWarehouse = AccHead::find(request('warehouse_id'));
            if ($selectedWarehouse) {
                $inventoryBalances = Item::with(['units'])
                    ->paginate(50)
                    ->through(function ($item) use ($selectedWarehouse) {
                        $item->current_balance = $this->calculateItemBalanceByWarehouse($item->id, $selectedWarehouse->id);
                        $item->value = $item->current_balance * ($item->cost_price ?? 0);
                        $item->main_unit = $item->units->first();
                        return $item;
                    });
            }
        }

        $totalBalance = $inventoryBalances->sum('current_balance');
        $totalValue = $inventoryBalances->sum('value');
        $totalItems = $inventoryBalances->count();
        $lowStockItems = $inventoryBalances->where('current_balance', '<=', 'min_balance')->count();
        $highStockItems = $inventoryBalances->where('current_balance', '>=', 'max_balance')->count();
        $normalStockItems = $inventoryBalances->where('current_balance', '>', 'min_balance')
            ->where('current_balance', '<', 'max_balance')->count();

        return view('reports.general-inventory-balances-by-store', compact(
            'warehouses',
            'notes',
            'selectedWarehouse',
            'inventoryBalances',
            'totalBalance',
            'totalValue',
            'totalItems',
            'lowStockItems',
            'highStockItems',
            'normalStockItems'
        ));
    }

    // حركة الصنف
    public function generalInventoryMovements()
    {
        $items = Item::all();
        $warehouses = AccHead::where('code', 'like', '%123')->where('isdeleted', 0)->get();
        $notes = Note::with('noteDetails')->get();
        $selectedItem = null;
        $movements = OperationItems::whereRaw('0=1')->paginate(50);
        $currentBalance = 0;

        if (request('item_id')) {
            $selectedItem = Item::with('unit')->find(request('item_id'));
            if ($selectedItem) {
                $warehouseId = request('warehouse_id', 'all');
                $fromDate = request('from_date');
                $toDate = request('to_date');

                $query = OperationItems::where('item_id', $selectedItem->id)
                    ->with('warehouse');

                if ($warehouseId !== 'all') {
                    $query->where('detail_store', $warehouseId);
                }

                if ($fromDate) {
                    $query->whereDate('created_at', '>=', $fromDate);
                }

                if ($toDate) {
                    $query->whereDate('created_at', '<=', $toDate);
                }

                $movements = $query->orderBy('created_at', 'asc')->paginate(50);
                $currentBalance = $this->calculateItemBalance($selectedItem->id);
            }
        }

        $totalIn = $movements->sum('qty_in');
        $totalOut = $movements->sum('qty_out');
        $netMovement = $totalIn - $totalOut;
        $totalOperations = $movements->count();

        return view('reports.general-inventory-movements', compact(
            'items',
            'warehouses',
            'notes',
            'selectedItem',
            'movements',
            'currentBalance',
            'totalIn',
            'totalOut',
            'netMovement',
            'totalOperations'
        ));
    }

    // تقرير المبيعات اليومية
    // public function generalSalesDailyReport(Request $request)
    // {
    //     // جلب العملاء
    //     $customers = AccHead::where('code', 'like', '1103%')
    //         ->where('isdeleted', 0)
    //         ->get();

    //     // بناء الاستعلام
    //     $query = OperHead::where('pro_type', 10)
    //         ->with('acc1Head')
    //         ->when($request->from_date, fn($q) => $q->whereDate('pro_date', '>=', $request->from_date))
    //         ->when($request->to_date, fn($q) => $q->whereDate('pro_date', '<=', $request->to_date))
    //         ->when($request->customer_id, fn($q) => $q->where('acc1', $request->customer_id))
    //         ->orderBy('pro_date', 'desc');

    //     // جلب البيانات مع الـ Pagination
    //     $sales = $query->paginate(50);

    //     // الحسابات
    //     $totalQuantity = $sales->sum('total_quantity');
    //     $totalSales = $sales->sum('total_sales');
    //     $totalDiscount = $sales->sum('discount');
    //     $totalNetSales = $sales->sum('net_sales');
    //     $totalInvoices = $sales->count();
    //     $averageInvoiceValue = $totalInvoices > 0 ? $totalNetSales / $totalInvoices : 0;

    //     // إذا ما فيش فلاتر → افتراضي اليوم
    //     if (!$request->filled('from_date') && !$request->filled('to_date')) {
    //         $request->merge([
    //             'from_date' => today()->format('Y-m-d'),
    //             'to_date' => today()->format('Y-m-d')
    //         ]);
    //     }

    //     return view('reports.general-sales-daily-report', compact(
    //         'customers',
    //         'sales',
    //         'totalQuantity',
    //         'totalSales',
    //         'totalDiscount',
    //         'totalNetSales',
    //         'totalInvoices',
    //         'averageInvoiceValue'
    //     ));
    // }

    // sales report by address
    public function salesReportByAddress()
    {
        return view('reports.sales.manage-sales-report-by-adress');
    }

    // تقرير المشتريات اليومية
    public function generalPurchasesDailyReport()
    {
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

        $purchases = OperHead::where('pro_type', 11) // Purchase invoices
            ->with('acc1Head')
            ->when(request('from_date'), function ($q) {
                $q->whereDate('pro_date', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('pro_date', '<=', request('to_date'));
            })
            ->when(request('supplier_id'), function ($q) {
                $q->where('acc1', request('supplier_id'));
            })
            ->orderBy('pro_date', 'desc')
            ->paginate(50);

        $totalQuantity = $purchases->sum('total_quantity');
        $totalPurchases = $purchases->sum('total_purchases');
        $totalDiscount = $purchases->sum('discount');
        $totalNetPurchases = $purchases->sum('net_purchases');
        $totalInvoices = $purchases->count();
        $averageInvoiceValue = $totalInvoices > 0 ? $totalNetPurchases / $totalInvoices : 0;

        return view('reports.general-purchases-daily-report', compact(
            'suppliers',
            'purchases',
            'totalQuantity',
            'totalPurchases',
            'totalDiscount',
            'totalNetPurchases',
            'totalInvoices',
            'averageInvoiceValue'
        ));
    }

    // تقرير العملاء اليومية
    // public function generalCustomersDailyReport()
    // {
    //     $customers = AccHead::where('code', 'like', '1103%')->where('isdeleted', 0)->get();

    //     $query = JournalDetail::whereHas('accHead', function ($q) {
    //         $q->where('code', 'like', '1103%'); // Customer accounts
    //     })->with(['accHead', 'head']);

    //     if (request('from_date')) {
    //         $query->whereDate('crtime', '>=', request('from_date'));
    //     }
    //     if (request('to_date')) {
    //         $query->whereDate('crtime', '<=', request('to_date'));
    //     }
    //     if (request('customer_id')) {
    //         $query->where('account_id', request('customer_id'));
    //     }

    //     $customerTransactions = $query->orderBy('crtime', 'desc')->paginate(50);

    //     $totalAmount = $customerTransactions->sum('debit') + $customerTransactions->sum('credit');
    //     $totalSales = $customerTransactions->sum('debit');
    //     $totalPayments = $customerTransactions->sum('credit');
    //     $finalBalance = $totalSales - $totalPayments;
    //     $totalTransactions = $customerTransactions->count();

    //     return view('reports.general-customers-daily-report', compact(
    //         'customers',
    //         'customerTransactions',
    //         'totalAmount',
    //         'totalSales',
    //         'totalPayments',
    //         'finalBalance',
    //         'totalTransactions'
    //     ));
    // }

    // تقرير العملاء إجماليات
    // public function generalCustomersTotalReport()
    // {
    //     $groupBy = request('group_by', 'customer');
    //     $fromDate = request('from_date');
    //     $toDate = request('to_date');

    //     $query = JournalDetail::whereHas('account', function ($q) {
    //         $q->where('code', 'like', '1103%'); // Customer accounts
    //     })->with('account');

    //     if ($fromDate) {
    //         $query->whereDate('crtime', '>=', $fromDate);
    //     }
    //     if ($toDate) {
    //         $query->whereDate('crtime', '<=', $toDate);
    //     }

    //     if ($groupBy === 'customer') {
    //         $customerTotals = $query->selectRaw('account_id, SUM(debit) as total_sales, SUM(credit) as total_payments, COUNT(*) as transactions_count')
    //             ->groupBy('account_id')
    //             ->with('account')
    //             ->orderBy('total_sales', 'desc')
    //             ->paginate(50);
    //     } else {
    //         $customerTotals = $query->selectRaw('DATE(crtime) as date, SUM(debit) as total_sales, SUM(credit) as total_payments, COUNT(*) as transactions_count')
    //             ->groupBy('date')
    //             ->orderBy('date', 'desc')
    //             ->paginate(50);
    //     }

    //     $grandTotalTransactions = $customerTotals->sum('transactions_count');
    //     $grandTotalSales = $customerTotals->sum('total_sales');
    //     $grandTotalPayments = $customerTotals->sum('total_payments');
    //     $grandTotalBalance = $grandTotalSales - $grandTotalPayments;
    //     $grandAverageTransaction = $grandTotalTransactions > 0 ? ($grandTotalSales + $grandTotalPayments) / $grandTotalTransactions : 0;

    //     $totalCustomers = $customerTotals->count();
    //     $topCustomer = $customerTotals->first() ? $customerTotals->first()->accHead->aname : '---';
    //     $averageSalesPerCustomer = $totalCustomers > 0 ? $grandTotalSales / $totalCustomers : 0;
    //     $averageBalancePerCustomer = $totalCustomers > 0 ? $grandTotalBalance / $totalCustomers : 0;

    //     return view('reports.general-customers-total-report', compact(
    //         'customerTotals',
    //         'groupBy',
    //         'grandTotalTransactions',
    //         'grandTotalSales',
    //         'grandTotalPayments',
    //         'grandTotalBalance',
    //         'grandAverageTransaction',
    //         'totalCustomers',
    //         'topCustomer',
    //         'averageSalesPerCustomer',
    //         'averageBalancePerCustomer'
    //     ));
    // }

    // تقرير العملاء أصناف
    // public function generalCustomersItemsReport()
    // {
    //     $customers = AccHead::where('code', 'like', '1103%')->where('isdeleted', 0)->get();

    //     $query = OperationItems::whereHas('operation', function ($q) {
    //         $q->where('pro_type', 10); // Sales invoices
    //     })->with(['item', 'operation']);

    //     if (request('from_date')) {
    //         $query->whereHas('operation', function ($q) {
    //             $q->whereDate('pro_date', '>=', request('from_date'));
    //         });
    //     }
    //     if (request('to_date')) {
    //         $query->whereHas('operation', function ($q) {
    //             $q->whereDate('pro_date', '<=', request('to_date'));
    //         });
    //     }
    //     if (request('customer_id')) {
    //         $query->whereHas('operation', function ($q) {
    //             $q->where('acc1', request('customer_id'));
    //         });
    //     }

    //     $customerItems = $query->selectRaw('item_id, SUM(qty_out) as total_quantity, SUM(qty_out * price) as total_sales, COUNT(DISTINCT operation_id) as invoices_count')
    //         ->groupBy('item_id')
    //         ->with('item')
    //         ->orderBy('total_quantity', 'desc')
    //         ->paginate(50);

    //     $totalQuantity = $customerItems->sum('total_quantity');
    //     $totalSales = $customerItems->sum('total_sales');
    //     $averagePrice = $totalQuantity > 0 ? $totalSales / $totalQuantity : 0;
    //     $totalInvoices = $customerItems->sum('invoices_count');
    //     $totalItems = $customerItems->count();
    //     $topSellingItem = $customerItems->first() ? $customerItems->first()->item->name : '---';
    //     $averageQuantityPerItem = $totalItems > 0 ? $totalQuantity / $totalItems : 0;
    //     $averageSalesPerItem = $totalItems > 0 ? $totalSales / $totalItems : 0;

    //     return view('reports.general-customers-items-report', compact(
    //         'customers',
    //         'customerItems',
    //         'totalQuantity',
    //         'totalSales',
    //         'averagePrice',
    //         'totalInvoices',
    //         'totalItems',
    //         'topSellingItem',
    //         'averageQuantityPerItem',
    //         'averageSalesPerItem'
    //     ));
    // }
    // تقرير اعمار ديون العملاء
    // public function generalCustomersDebtHistoryReport()
    // {
    //     return view('reports.customers.customer-debt-history');
    // }

    // تقرير الموردين اليومية
    // public function generalSuppliersDailyReport()
    // {
    //     $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

    //     $query = JournalDetail::whereHas('account', function ($q) {
    //         $q->where('code', 'like', '2101%'); // Supplier accounts
    //     })->with(['account', 'journalHead']);

    //     if (request('from_date')) {
    //         $query->whereDate('crtime', '>=', request('from_date'));
    //     }
    //     if (request('to_date')) {
    //         $query->whereDate('crtime', '<=', request('to_date'));
    //     }
    //     if (request('supplier_id')) {
    //         $query->where('account_id', request('supplier_id'));
    //     }

    //     $supplierTransactions = $query->orderBy('crtime', 'desc')->paginate(50);

    //     $totalAmount = $supplierTransactions->sum('debit') + $supplierTransactions->sum('credit');
    //     $totalPurchases = $supplierTransactions->sum('credit');
    //     $totalPayments = $supplierTransactions->sum('debit');
    //     $finalBalance = $totalPurchases - $totalPayments;
    //     $totalTransactions = $supplierTransactions->count();

    //     return view('reports.general-suppliers-daily-report', compact(
    //         'suppliers',
    //         'supplierTransactions',
    //         'totalAmount',
    //         'totalPurchases',
    //         'totalPayments',
    //         'finalBalance',
    //         'totalTransactions'
    //     ));
    // }

    // تقرير الموردين إجماليات
    // public function generalSuppliersTotalReport()
    // {
    //     $groupBy = request('group_by', 'supplier');
    //     $fromDate = request('from_date');
    //     $toDate = request('to_date');

    //     $query = JournalDetail::whereHas('account', function ($q) {
    //         $q->where('code', 'like', '2101%'); // Supplier accounts
    //     })->with('account');

    //     if ($fromDate) {
    //         $query->whereDate('crtime', '>=', $fromDate);
    //     }
    //     if ($toDate) {
    //         $query->whereDate('crtime', '<=', $toDate);
    //     }

    //     if ($groupBy === 'supplier') {
    //         $supplierTotals = $query->selectRaw('account_id, SUM(credit) as total_purchases, SUM(debit) as total_payments, COUNT(*) as transactions_count')
    //             ->groupBy('account_id')
    //             ->with('account')
    //             ->orderBy('total_purchases', 'desc')
    //             ->paginate(50);
    //     } else {
    //         $supplierTotals = $query->selectRaw('DATE(crtime) as date, SUM(credit) as total_purchases, SUM(debit) as total_payments, COUNT(*) as transactions_count')
    //             ->groupBy('date')
    //             ->orderBy('date', 'desc')
    //             ->paginate(50);
    //     }

    //     $grandTotalTransactions = $supplierTotals->sum('transactions_count');
    //     $grandTotalPurchases = $supplierTotals->sum('total_purchases');
    //     $grandTotalPayments = $supplierTotals->sum('total_payments');
    //     $grandTotalBalance = $grandTotalPurchases - $grandTotalPayments;
    //     $grandAverageTransaction = $grandTotalTransactions > 0 ? ($grandTotalPurchases + $grandTotalPayments) / $grandTotalTransactions : 0;

    //     $totalSuppliers = $supplierTotals->count();
    //     $topSupplier = $supplierTotals->first() ? $supplierTotals->first()->accHead->aname : '---';
    //     $averagePurchasesPerSupplier = $totalSuppliers > 0 ? $grandTotalPurchases / $totalSuppliers : 0;
    //     $averageBalancePerSupplier = $totalSuppliers > 0 ? $grandTotalBalance / $totalSuppliers : 0;

    //     return view('reports.general-suppliers-total-report', compact(
    //         'supplierTotals',
    //         'groupBy',
    //         'grandTotalTransactions',
    //         'grandTotalPurchases',
    //         'grandTotalPayments',
    //         'grandTotalBalance',
    //         'grandAverageTransaction',
    //         'totalSuppliers',
    //         'topSupplier',
    //         'averagePurchasesPerSupplier',
    //         'averageBalancePerSupplier'
    //     ));
    // }

    // تقرير الموردين أصناف
    // public function generalSuppliersItemsReport()
    // {
    //     $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

    //     $query = OperationItems::whereHas('operation', function ($q) {
    //         $q->where('pro_type', 11); // Purchase invoices
    //     })->with(['item', 'operation']);

    //     if (request('from_date')) {
    //         $query->whereHas('operation', function ($q) {
    //             $q->whereDate('pro_date', '>=', request('from_date'));
    //         });
    //     }
    //     if (request('to_date')) {
    //         $query->whereHas('operation', function ($q) {
    //             $q->whereDate('pro_date', '<=', request('to_date'));
    //         });
    //     }
    //     if (request('supplier_id')) {
    //         $query->whereHas('operation', function ($q) {
    //             $q->where('acc1', request('supplier_id'));
    //         });
    //     }

    //     $supplierItems = $query->selectRaw('item_id, SUM(qty_in) as total_quantity, SUM(qty_in * price) as total_purchases, COUNT(DISTINCT operation_id) as invoices_count')
    //         ->groupBy('item_id')
    //         ->with('item')
    //         ->orderBy('total_quantity', 'desc')
    //         ->paginate(50);

    //     $totalQuantity = $supplierItems->sum('total_quantity');
    //     $totalPurchases = $supplierItems->sum('total_purchases');
    //     $averagePrice = $totalQuantity > 0 ? $totalPurchases / $totalQuantity : 0;
    //     $totalInvoices = $supplierItems->sum('invoices_count');
    //     $totalItems = $supplierItems->count();
    //     $topPurchasedItem = $supplierItems->first() ? $supplierItems->first()->item->name : '---';
    //     $averageQuantityPerItem = $totalItems > 0 ? $totalQuantity / $totalItems : 0;
    //     $averagePurchasesPerItem = $totalItems > 0 ? $totalPurchases / $totalItems : 0;

    //     return view('reports.general-suppliers-items-report', compact(
    //         'suppliers',
    //         'supplierItems',
    //         'totalQuantity',
    //         'totalPurchases',
    //         'averagePrice',
    //         'totalInvoices',
    //         'totalItems',
    //         'topPurchasedItem',
    //         'averageQuantityPerItem',
    //         'averagePurchasesPerItem'
    //     ));
    // }

    // ميزان المصروفات
    public function expensesBalanceReport()
    {
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $expenseCategory = request('expense_category');
        $costCenter = request('cost_center');

        $expenseCategories = collect(); // This would be populated with expense categories
        $costCenters = CostCenter::all();

        $expenseBalances = AccHead::where('code', 'like', '57%') // Expense accounts
            ->where('isdeleted', 0)
            ->paginate(50)
            ->through(function ($account) use ($asOfDate) {
                $balance = $this->calculateAccountBalance($account->id, $asOfDate);
                $account->total_expenses = $balance > 0 ? $balance : 0;
                $account->total_payments = $balance < 0 ? abs($balance) : 0;
                $account->balance = $balance;
                return $account;
            });

        $totalExpenses = $expenseBalances->sum('total_expenses');
        $totalPayments = $expenseBalances->sum('total_payments');
        $totalBalance = $expenseBalances->sum('balance');
        $totalAccounts = $expenseBalances->count();
        $highestExpense = $expenseBalances->sortByDesc('total_expenses')->first()->name ?? '---';
        $averageExpensePerAccount = $totalAccounts > 0 ? $totalExpenses / $totalAccounts : 0;
        $netExpenses = $totalExpenses - $totalPayments;

        return view('reports.expenses-balance-report', compact(
            'expenseCategories',
            'costCenters',
            'expenseBalances',
            'totalExpenses',
            'totalPayments',
            'totalBalance',
            'totalAccounts',
            'highestExpense',
            'averageExpensePerAccount',
            'netExpenses',
            'asOfDate'
        ));
    }

    // كشف حساب مصروف
    public function generalExpensesDailyReport()
    {
        $expenseAccounts = AccHead::where('code', 'like', '57%')->where('isdeleted', 0)->where('is_basic', 0)->get();
        $selectedAccount = null;
        $openingBalance = 0;
        $closingBalance = 0;

        // Initialize with empty paginator
        $expenseTransactions = JournalDetail::whereNull('id')->paginate(50);

        if (request('expense_account')) {
            $selectedAccount = AccHead::find(request('expense_account'));
            if ($selectedAccount) {
                $fromDate = request('from_date');
                $toDate = request('to_date');

                $expenseTransactions = JournalDetail::where('account_id', $selectedAccount->id)
                    ->with(['head', 'costCenter'])
                    ->when($fromDate, function ($q) use ($fromDate) {
                        $q->whereDate('crtime', '>=', $fromDate);
                    })
                    ->when($toDate, function ($q) use ($toDate) {
                        $q->whereDate('crtime', '<=', $toDate);
                    })
                    ->orderBy('crtime', 'asc')
                    ->paginate(50);

                $openingBalance = $this->calculateAccountBalance($selectedAccount->id, $fromDate);
                $closingBalance = $this->calculateAccountBalance($selectedAccount->id, $toDate);
            }
        }

        return view('reports.general-expenses-daily-report', compact(
            'expenseAccounts',
            'selectedAccount',
            'expenseTransactions',
            'openingBalance',
            'closingBalance'
        ));
    }

    // قائمة مراكز التكلفة
    // public function generalCostCentersList()
    // {
    //     $asOfDate = request('as_of_date', now()->format('Y-m-d'));
    //     $costCenterType = request('cost_center_type');
    //     $search = request('search');

    //     $costCenters = CostCenter::when($costCenterType, function ($q) use ($costCenterType) {
    //         $q->where('type', $costCenterType);
    //     })
    //         ->when($search, function ($q) use ($search) {
    //             $q->where('name', 'like', '%' . $search . '%');
    //         })
    //         ->paginate(50)
    //         ->through(function ($center) use ($asOfDate) {
    //             $center->total_expenses = $this->calculateCostCenterExpenses($center->id, $asOfDate);
    //             $center->total_revenues = $this->calculateCostCenterRevenues($center->id, $asOfDate);
    //             $center->net_cost = $center->total_expenses - $center->total_revenues;
    //             return $center;
    //         });

    //     $totalExpenses = $costCenters->sum('total_expenses');
    //     $totalRevenues = $costCenters->sum('total_revenues');
    //     $totalNetCost = $costCenters->sum('net_cost');
    //     $totalCostCenters = $costCenters->count();
    //     $activeCostCenters = $costCenters->where('is_active', true)->count();
    //     $averageCostPerCenter = $totalCostCenters > 0 ? $totalNetCost / $totalCostCenters : 0;

    //     return view('reports.general-cost-centers-list', compact(
    //         'costCenters',
    //         'totalExpenses',
    //         'totalRevenues',
    //         'totalNetCost',
    //         'totalCostCenters',
    //         'activeCostCenters',
    //         'averageCostPerCenter',
    //         'asOfDate'
    //     ));
    // }

    // كشف حساب مركز التكلفة
    // public function generalCostCenterAccountStatement()
    // {
    //     $costCenters = CostCenter::all();
    //     $selectedCostCenter = null;
    //     $costCenterTransactions = collect();
    //     $openingBalance = 0;
    //     $closingBalance = 0;

    //     if (request('cost_center_id')) {
    //         $selectedCostCenter = CostCenter::find(request('cost_center_id'));
    //         if ($selectedCostCenter) {
    //             $fromDate = request('from_date');
    //             $toDate = request('to_date');

    //             $costCenterTransactions = JournalDetail::where('cost_center_id', $selectedCostCenter->id)
    //                 ->with(['head', 'accHead'])
    //                 ->when($fromDate, function ($q) use ($fromDate) {
    //                     $q->whereDate('crtime', '>=', $fromDate);
    //                 })
    //                 ->when($toDate, function ($q) use ($toDate) {
    //                     $q->whereDate('crtime', '<=', $toDate);
    //                 })
    //                 ->orderBy('crtime', 'asc')
    //                 ->paginate(50);

    //             $openingBalance = $this->calculateCostCenterBalance($selectedCostCenter->id, $fromDate);
    //             $closingBalance = $this->calculateCostCenterBalance($selectedCostCenter->id, $toDate);
    //         }
    //     }

    //     return view('reports.general-cost-center-account-statement', compact(
    //         'costCenters',
    //         'selectedCostCenter',
    //         'costCenterTransactions',
    //         'openingBalance',
    //         'closingBalance'
    //     ));
    // }

    // كشف حساب عام مع مركز تكلفة
    // public function generalAccountStatementWithCostCenter()
    // {
    //     $accounts = AccHead::where('isdeleted', 0)->get();
    //     $costCenters = CostCenter::all();
    //     $selectedAccount = null;
    //     $accountTransactions = collect();
    //     $openingBalance = 0;
    //     $closingBalance = 0;
    //     $costCenterSummary = collect();

    //     if (request('account_id')) {
    //         $selectedAccount = AccHead::find(request('account_id'));
    //         if ($selectedAccount) {
    //             $costCenterId = request('cost_center_id');
    //             $fromDate = request('from_date');
    //             $toDate = request('to_date');

    //             $query = JournalDetail::where('account_id', $selectedAccount->id)
    //                 ->with(['head', 'costCenter']);

    //             if ($costCenterId) {
    //                 $query->where('cost_center_id', $costCenterId);
    //             }

    //             if ($fromDate) {
    //                 $query->whereDate('crtime', '>=', $fromDate);
    //             }

    //             if ($toDate) {
    //                 $query->whereDate('crtime', '<=', $toDate);
    //             }

    //             $accountTransactions = $query->orderBy('crtime', 'asc')->paginate(50);

    //             $openingBalance = $this->calculateAccountBalance($selectedAccount->id, $fromDate);
    //             $closingBalance = $this->calculateAccountBalance($selectedAccount->id, $toDate);

    //             // Calculate cost center summary
    //             $costCenterSummary = JournalDetail::where('account_id', $selectedAccount->id)
    //                 ->with('costCenter')
    //                 ->when($fromDate, function ($q) use ($fromDate) {
    //                     $q->whereDate('crtime', '>=', $fromDate);
    //                 })
    //                 ->when($toDate, function ($q) use ($toDate) {
    //                     $q->whereDate('crtime', '<=', $toDate);
    //                 })
    //                 ->get()
    //                 ->groupBy('cost_center_id')
    //                 ->map(function ($transactions, $costCenterId) {
    //                     $costCenter = $transactions->first()->costCenter;
    //                     return (object) [
    //                         'cost_center_name' => $costCenter ? $costCenter->name : 'بدون مركز تكلفة',
    //                         'total_debit' => $transactions->sum('debit'),
    //                         'total_credit' => $transactions->sum('credit'),
    //                         'net_amount' => $transactions->sum('debit') - $transactions->sum('credit')
    //                     ];
    //                 });
    //         }
    //     }

    //     return view('reports.general-account-statement-with-cost-center', compact(
    //         'accounts',
    //         'costCenters',
    //         'selectedAccount',
    //         'accountTransactions',
    //         'openingBalance',
    //         'closingBalance',
    //         'costCenterSummary'
    //     ));
    // }

    // Helper methods
    private function calculateAccountBalance($accountId, $asOfDate = null)
    {
        $query = JournalDetail::where('account_id', $accountId);

        if ($asOfDate) {
            $query->whereHas('operHead', function ($q) use ($asOfDate) {
                $q->whereDate('pro_date', '<=', $asOfDate);
            });
        }

        $debits = $query->sum('debit');
        $credits = $query->sum('credit');

        return $debits - $credits;
    }

    private function calculateItemBalance($itemId, $warehouseId = null)
    {
        $query = OperationItems::where('item_id', $itemId);

        if ($warehouseId) {
            $query->where('detail_store', $warehouseId);
        }

        $qtyIn = $query->sum('qty_in');
        $qtyOut = $query->sum('qty_out');

        return $qtyIn - $qtyOut;
    }

    private function calculateItemBalanceByWarehouse($itemId, $warehouseId)
    {
        return $this->calculateItemBalance($itemId, $warehouseId);
    }

    private function calculateCostCenterExpenses($costCenterId, $asOfDate = null)
    {
        $query = JournalDetail::where('cost_center_id', $costCenterId)
            ->whereHas('account', function ($q) {
                $q->where('code', 'like', '57%'); // Expense accounts
            });

        if ($asOfDate) {
            $query->whereHas('operHead', function ($q) use ($asOfDate) {
                $q->whereDate('pro_date', '<=', $asOfDate);
            });
        }

        return $query->sum('debit');
    }

    private function calculateCostCenterRevenues($costCenterId, $asOfDate = null)
    {
        $query = JournalDetail::where('cost_center_id', $costCenterId)
            ->whereHas('account', function ($q) {
                $q->where('code', 'like', '47%'); // Revenue accounts
            });

        if ($asOfDate) {
            $query->whereHas('operHead', function ($q) use ($asOfDate) {
                $q->whereDate('pro_date', '<=', $asOfDate);
            });
        }

        return $query->sum('credit');
    }

    private function calculateCostCenterBalance($costCenterId, $asOfDate = null)
    {
        $query = JournalDetail::where('cost_center_id', $costCenterId);

        if ($asOfDate) {
            $query->whereHas('operHead', function ($q) use ($asOfDate) {
                $q->whereDate('pro_date', '<=', $asOfDate);
            });
        }

        $debits = $query->sum('debit');
        $credits = $query->sum('credit');

        return $debits - $credits;
    }

    // قائمة الحسابات مع الارصدة
    public function generalAccountBalancesByStore()
    {
        $warehouses = AccHead::where('code', 'like', '1104%')->where('isdeleted', 0)->get();
        $asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $selectedWarehouse = null;
        $accountBalances = collect();

        if (request('warehouse_id')) {
            $selectedWarehouse = AccHead::find(request('warehouse_id'));
            if ($selectedWarehouse) {
                $accountBalances = AccHead::where('isdeleted', 0)
                    ->paginate(50)
                    ->through(function ($account) use ($asOfDate, $selectedWarehouse) {
                        $balance = $this->calculateAccountBalance($account->id, $asOfDate);
                        $account->debit = $balance > 0 ? $balance : 0;
                        $account->credit = $balance < 0 ? abs($balance) : 0;
                        $account->balance = $balance;
                        return $account;
                    });
            }
        }

        $totalDebit = $accountBalances->sum('debit');
        $totalCredit = $accountBalances->sum('credit');
        $totalBalance = $accountBalances->sum('balance');

        return view('reports.general-account-balances-by-store', compact(
            'warehouses',
            'selectedWarehouse',
            'accountBalances',
            'totalDebit',
            'totalCredit',
            'totalBalance',
            'asOfDate'
        ));
    }

    // تقارير المبيعات
    public function generalSalesReport()
    {
        $customers = AccHead::where('code', 'like', '1103%')->where('isdeleted', 0)->get();

        $sales = OperHead::where('pro_type', 10) // Sales invoices
            ->with('acc1Head')
            ->when(request('from_date'), function ($q) {
                $q->whereDate('pro_date', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('pro_date', '<=', request('to_date'));
            })
            ->when(request('customer_id'), function ($q) {
                $q->where('acc1', request('customer_id'));
            })
            ->orderBy('pro_date', 'desc')
            ->paginate(50);

        $totalQuantity = $sales->sum('total_quantity');
        $totalSales = $sales->sum('total_sales');
        $totalDiscount = $sales->sum('discount');
        $totalNetSales = $sales->sum('net_sales');
        $totalInvoices = $sales->count();
        $averageInvoiceValue = $totalInvoices > 0 ? $totalNetSales / $totalInvoices : 0;

        return view('reports.general-sales-report', compact(
            'customers',
            'sales',
            'totalQuantity',
            'totalSales',
            'totalDiscount',
            'totalNetSales',
            'totalInvoices',
            'averageInvoiceValue'
        ));
    }

    // تقارير المشتريات
    public function generalPurchasesReport()
    {
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

        $purchases = OperHead::where('pro_type', 11) // Purchase invoices
            ->with('acc1Head')
            ->when(request('from_date'), function ($q) {
                $q->whereDate('pro_date', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('pro_date', '<=', request('to_date'));
            })
            ->when(request('supplier_id'), function ($q) {
                $q->where('acc1', request('supplier_id'));
            })
            ->orderBy('pro_date', 'desc')
            ->paginate(50);

        $totalQuantity = $purchases->sum('total_quantity');
        $totalPurchases = $purchases->sum('total_purchases');
        $totalDiscount = $purchases->sum('discount');
        $totalNetPurchases = $purchases->sum('net_purchases');
        $totalInvoices = $purchases->count();
        $averageInvoiceValue = $totalInvoices > 0 ? $totalNetPurchases / $totalInvoices : 0;

        return view('reports.general-purchases-report', compact(
            'suppliers',
            'purchases',
            'totalQuantity',
            'totalPurchases',
            'totalDiscount',
            'totalNetPurchases',
            'totalInvoices',
            'averageInvoiceValue'
        ));
    }

    // تقارير العملاء
    public function generalCustomersReport()
    {
        $customers = AccHead::where('code', 'like', '1103%')->where('isdeleted', 0)->get();

        $customerTransactions = JournalDetail::whereHas('account', function ($q) {
            $q->where('code', 'like', '122%'); // Customer accounts
        })->with(['account', 'journalHead'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('customer_id'), function ($q) {
                $q->where('account_id', request('customer_id'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalAmount = $customerTransactions->sum('debit') + $customerTransactions->sum('credit');
        $totalSales = $customerTransactions->sum('debit');
        $totalPayments = $customerTransactions->sum('credit');
        $finalBalance = $totalSales - $totalPayments;
        $totalTransactions = $customerTransactions->count();

        return view('reports.general-customers-report', compact(
            'customers',
            'customerTransactions',
            'totalAmount',
            'totalSales',
            'totalPayments',
            'finalBalance',
            'totalTransactions'
        ));
    }

    // تقارير الموردين
    public function generalSuppliersReport()
    {
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();

        $supplierTransactions = JournalDetail::whereHas('account', function ($q) {
            $q->where('code', 'like', '211%'); // Supplier accounts
        })->with(['account', 'journalHead'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('supplier_id'), function ($q) {
                $q->where('account_id', request('supplier_id'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalAmount = $supplierTransactions->sum('debit') + $supplierTransactions->sum('credit');
        $totalPurchases = $supplierTransactions->sum('credit');
        $totalPayments = $supplierTransactions->sum('debit');
        $finalBalance = $totalPurchases - $totalPayments;
        $totalTransactions = $supplierTransactions->count();

        return view('reports.general-suppliers-report', compact(
            'suppliers',
            'supplierTransactions',
            'totalAmount',
            'totalPurchases',
            'totalPayments',
            'finalBalance',
            'totalTransactions'
        ));
    }

    // تقارير المصروفات
    public function generalExpensesReport()
    {
        $expenseAccounts = AccHead::where('code', 'like', '57%')->where('isdeleted', 0)->get();

        $expenseTransactions = JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '57%'); // Expense accounts
        })->with(['accHead', 'head', 'costCenter'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('expense_account'), function ($q) {
                $q->where('account_id', request('expense_account'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalExpenses = $expenseTransactions->sum('debit');
        $totalPayments = $expenseTransactions->sum('credit');
        $netExpenses = $totalExpenses - $totalPayments;
        $totalTransactions = $expenseTransactions->count();

        return view('reports.general-expenses-report', compact(
            'expenseAccounts',
            'expenseTransactions',
            'totalExpenses',
            'totalPayments',
            'netExpenses',
            'totalTransactions'
        ));
    }

    // تقارير مراكز التكلفة
    public function generalCostCentersReport()
    {
        $costCenters = CostCenter::all();

        $costCenterTransactions = JournalDetail::with(['account', 'journalHead', 'costCenter'])
            ->when(request('from_date'), function ($q) {
                $q->whereDate('crtime', '>=', request('from_date'));
            })
            ->when(request('to_date'), function ($q) {
                $q->whereDate('crtime', '<=', request('to_date'));
            })
            ->when(request('cost_center_id'), function ($q) {
                $q->where('cost_center_id', request('cost_center_id'));
            })
            ->orderBy('crtime', 'desc')
            ->paginate(50);

        $totalExpenses = $costCenterTransactions->sum('debit');
        $totalRevenues = $costCenterTransactions->sum('credit');
        $netCost = $totalExpenses - $totalRevenues;
        $totalTransactions = $costCenterTransactions->count();

        return view('reports.general-cost-centers-report', compact(
            'costCenters',
            'costCenterTransactions',
            'totalExpenses',
            'totalRevenues',
            'netCost',
            'totalTransactions'
        ));
    }


    // تقرير المخزون العام
    public function generalInventoryReport()
    {
        $items = \App\Models\Item::with('units')->paginate(50);
        foreach ($items as $item) {
            $item->main_unit = $item->units->first();
        }
        return view('reports.general-inventory-report', compact('items'));
    }

    // تقرير حركة المخزون اليومية
    public function generalInventoryDailyMovementReport()
    {
        return view('reports.general-inventory-daily-movement-report');
    }

    // تقرير جرد المخزون
    public function generalInventoryStocktakingReport()
    {
        return view('reports.general-inventory-stocktaking-report');
    }

    // تقرير الحسابات العام
    public function generalAccountsReport()
    {
        return view('reports.general-accounts-report');
    }

    // تقرير كشف حساب عام
    public function generalAccountStatementReport()
    {
        return view('reports.general-account-statement-report');
    }

    // تقرير النقدية والبنوك
    // public function generalCashBankReport()
    // {
    //     return view('reports.general-cash-bank-report');
    // }

    // تقرير حركة الصندوق
    public function generalCashboxMovementReport()
    {
        return view('reports.general-cashbox-movement-report');
    }


    // Controller Code
    protected function getQuantityStatus($item)
    {
        // Calculate current quantity from operation_items
        $currentQuantity = $this->calculateCurrentQuantity($item->id);

        if ($currentQuantity < $item->min_order_quantity) {
            // Send notification for low quantity
            $this->sendQuantityNotification($item, $currentQuantity, 'below_min');
            // Clear any "above_max" notifications since status changed
            $this->clearQuantityNotification($item->id, 'above_max');
            return 'below_min';
        } elseif ($currentQuantity > $item->max_order_quantity) {
            // Send notification for high quantity
            $this->sendQuantityNotification($item, $currentQuantity, 'above_max');
            // Clear any "below_min" notifications since status changed
            $this->clearQuantityNotification($item->id, 'below_min');
            return 'above_max';
        } else {
            // Quantity is within limits - clear any existing notifications
            $this->clearQuantityNotification($item->id, 'below_min');
            $this->clearQuantityNotification($item->id, 'above_max');
            return 'within_limits';
        }
    }

    /**
     * Clear quantity notification cache for a specific item and status
     */
    protected function clearQuantityNotification($itemId, $status)
    {
        $notificationKey = "item_quantity_{$itemId}_{$status}";
        cache()->forget($notificationKey);
    }

    protected function getRequiredCompensation($item)
    {
        // Calculate current quantity from operation_items
        $currentQuantity = $this->calculateCurrentQuantity($item->id);

        if ($currentQuantity < $item->min_order_quantity) {
            // المطلوب تعويضه = الحد الأدنى - الكمية الحالية
            return $item->min_order_quantity - $currentQuantity;
        } elseif ($currentQuantity > $item->max_order_quantity) {
            // الكمية الزيادة = الكمية الحالية - الحد الأقصى
            return $currentQuantity - $item->max_order_quantity;
        }
        return 0; // لا يوجد تعويض مطلوب
    }

    /**
     * Calculate current quantity from operation_items table
     */
    protected function calculateCurrentQuantity($itemId)
    {
        $qtyIn = DB::table('operation_items')
            ->where('item_id', $itemId)
            ->where('qty_in', '>', 0)
            ->sum('qty_in');

        $qtyOut = DB::table('operation_items')
            ->where('item_id', $itemId)
            ->where('qty_out', '>', 0)
            ->sum('qty_out');

        return $qtyIn - $qtyOut;
    }

    /**
     * Send quantity limit notification
     */
    protected function sendQuantityNotification($item, $currentQuantity, $status)
    {
        // Check if we already sent a notification for this item with the same status
        $notificationKey = "item_quantity_{$item->id}_{$status}";
        $lastNotification = cache()->get($notificationKey);

        // If we already sent a notification for this status, check if quantity changed significantly
        if ($lastNotification) {
            $quantityDifference = abs($lastNotification['quantity'] - $currentQuantity);
            $minChangeThreshold = $this->getMinChangeThreshold($item, $status);

            // Skip notification if quantity hasn't changed significantly
            if ($quantityDifference < $minChangeThreshold) {
                return; // Skip notification - insufficient change
            }
        }

        // Get all users with notification permissions or specific role
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['مدير', 'admin', 'مشرف مخزن']);
        })->orWhere('id', 1)->get(); // Include admin user

        foreach ($users as $user) {
            $title = '';
            $message = '';
            $icon = '';

            if ($status === 'below_min') {
                $title = 'تنبيه: كمية منخفضة';
                $message = "الصنف '{$item->name}' وصل للحد الأدنى. الكمية الحالية: {$currentQuantity}";
                $icon = 'exclamation-triangle';
            } elseif ($status === 'above_max') {
                $title = 'تنبيه: كمية زائدة';
                $message = "الصنف '{$item->name}' تجاوز الحد الأقصى. الكمية الحالية: {$currentQuantity}";
                $icon = 'exclamation-circle';
            }

            if ($title && $message) {
                $user->notify(new \Modules\Notifications\Notifications\OrderNotification([
                    'id' => $item->id,
                    'title' => $title,
                    'message' => $message,
                    'icon' => $icon,
                    'created_at' => now()->toDateTimeString(),
                ]));
            }
        }

        // Store notification info in cache to prevent duplicates
        cache()->put($notificationKey, [
            'quantity' => $currentQuantity,
            'status' => $status,
            'sent_at' => now(),
            'item_id' => $item->id
        ], now()->addDays(1)); // Cache for 1 day
    }

    /**
     * Get minimum change threshold for notifications based on item type and limits
     */
    protected function getMinChangeThreshold($item, $status)
    {
        if ($status === 'below_min') {
            // For low quantity, notify if change is at least 1% of min_order_quantity or 1 unit
            return max(1, $item->min_order_quantity * 0.01);
        } elseif ($status === 'above_max') {
            // For high quantity, notify if change is at least 1% of max_order_quantity or 1 unit
            return max(1, $item->max_order_quantity * 0.01);
        }

        return 1; // Default threshold
    }

    public function getItemsMaxMinQuantity()
    {
        $items = Item::select('id', 'name', 'code', 'min_order_quantity', 'max_order_quantity')
            ->paginate(50);

        // Map the items within the paginator
        $items->getCollection()->transform(function ($item) {
            // Calculate current quantity from operation_items
            $currentQuantity = $this->calculateCurrentQuantity($item->id);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'current_quantity' => $currentQuantity,
                'min_order_quantity' => $item->min_order_quantity,
                'max_order_quantity' => $item->max_order_quantity,
                'status' => $this->getQuantityStatus($item),
                'required_compensation' => $this->getRequiredCompensation($item)
            ];
        });

        return view('reports.items.items-max&min-quantity', compact('items'));
    }

    /**
     * Check all items for quantity limits and send notifications
     * This can be called manually or from other parts of the system
     */
    public function checkAllItemsQuantityLimits()
    {
        $items = Item::whereNotNull('min_order_quantity')
            ->orWhereNotNull('max_order_quantity')
            ->get();

        $notificationsSent = 0;

        foreach ($items as $item) {
            $currentQuantity = $this->calculateCurrentQuantity($item->id);
            $status = $this->getQuantityStatus($item);

            if ($status !== 'within_limits') {
                $notificationsSent++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم فحص {$items->count()} صنف وتم إرسال {$notificationsSent} إشعار",
            'items_checked' => $items->count(),
            'notifications_sent' => $notificationsSent
        ]);
    }

    /**
     * Check specific item quantity after operation (can be called from other controllers)
     */
    public function checkItemQuantityAfterOperation($itemId)
    {
        $item = Item::find($itemId);

        if (!$item) {
            return false;
        }

        $currentQuantity = $this->calculateCurrentQuantity($item->id);
        $status = $this->getQuantityStatus($item);

        return $status !== 'within_limits'; // Return true if notification was sent
    }

    /**
     * Get items with quantity issues for dashboard display
     */
    public function getItemsWithQuantityIssues()
    {
        $items = Item::whereNotNull('min_order_quantity')
            ->orWhereNotNull('max_order_quantity')
            ->get()
            ->map(function ($item) {
                $currentQuantity = $this->calculateCurrentQuantity($item->id);
                $status = $this->getQuantityStatus($item);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'current_quantity' => $currentQuantity,
                    'min_order_quantity' => $item->min_order_quantity,
                    'max_order_quantity' => $item->max_order_quantity,
                    'status' => $status,
                    'required_compensation' => $this->getRequiredCompensation($item),
                    'issue_type' => $status === 'below_min' ? 'منخفضة' : ($status === 'above_max' ? 'زائدة' : 'طبيعية')
                ];
            })
            ->filter(function ($item) {
                return $item['status'] !== 'within_limits';
            })
            ->values();

        return response()->json($items);
    }

    // Controller Method
    public function pricesCompareReport()
    {
        // جلب كل الأصناف التي لها عروض أسعار (pro_tybe = 15)
        $priceData = OperationItems::where('pro_tybe', 15)
            ->with(['operhead', 'item'])
            ->select('item_id', 'item_price', 'pro_id')
            ->get();

        if ($priceData->isEmpty()) {
            return view('reports.items.prices-compare-report', [
                'items' => [],
                'suppliers' => [],
                'message' => 'لا توجد عروض أسعار متاحة'
            ]);
        }

        // تجميع البيانات حسب الصنف
        $itemsData = $priceData->groupBy('item_id')->map(function ($group) {
            $firstItem = $group->first();
            $itemModel = $firstItem->item ?? Item::find($firstItem->item_id);
            $itemName = $itemModel ? $itemModel->name : 'صنف غير محدد';

            // جمع عروض الموردين مع التأكد من وجود بيانات صحيحة
            $supplierOffers = $group->map(function ($row) {
                $supplierId = $row->operhead ? $row->operhead->acc1 : null;
                return [
                    'supplier_id' => $supplierId,
                    'price' => (float) $row->item_price,
                ];
            })->filter(function ($offer) {
                return $offer['supplier_id'] !== null && $offer['price'] > 0;
            });

            if ($supplierOffers->isEmpty()) {
                return null; // تجاهل الأصناف بدون عروض صحيحة
            }

            // تجميع حسب المورد وأخذ أقل سعر لكل مورد
            $supplierPrices = $supplierOffers->groupBy('supplier_id')->map(function ($offers) {
                return $offers->min('price');
            });

            // العثور على أفضل سعر والمورد
            $bestPrice = $supplierPrices->min();
            $bestSupplierId = $supplierPrices->search($bestPrice);

            return [
                'item_id' => $firstItem->item_id,
                'item_name' => $itemName,
                'suppliers' => $supplierPrices,
                'best_price' => $bestPrice,
                'best_supplier_id' => $bestSupplierId,
                'offers_count' => $supplierPrices->count()
            ];
        })->filter()->values(); // إزالة العناصر الفارغة

        // جلب أسماء الموردين
        $allSupplierIds = $itemsData->flatMap(function ($item) {
            return $item['suppliers']->keys();
        })->unique();

        $suppliers = [];
        if ($allSupplierIds->isNotEmpty()) {
            $suppliersData = \App\Models\AccHead::whereIn('id', $allSupplierIds)->get();
            foreach ($suppliersData as $supplier) {
                $suppliers[$supplier->id] = $supplier->aname;
            }
        }

        // إضافة أسماء الموردين للبيانات
        $items = $itemsData->map(function ($item) use ($suppliers) {
            $item['best_supplier_name'] = $suppliers[$item['best_supplier_id']] ?? 'مورد غير محدد';
            return $item;

            // dd($suppliers[$item['best_supplier_id']]);
        });

        return view('reports.items.prices-compare-report', compact('items', 'suppliers'));
    }

    public function inventoryDiscrepancyReport()
    {
        return view('reports.items.inventory-discrepancy-report');
    }

    public function generalProfitLossReport()
    {
        $fromDate = request('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = request('to_date', now()->format('Y-m-d'));

        // جلب الحسابات الرئيسية للإيرادات
        $revenueAccounts = AccHead::where('code', 'like', '4%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('code')
            ->get();

        // جلب الحسابات الرئيسية للمصروفات
        $expenseAccounts = AccHead::where('code', 'like', '5%')
            ->where('isdeleted', 0)
            ->whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('code')
            ->get();

        $totalRevenue = 0;
        $totalExpenses = 0;

        // حساب الإيرادات
        $this->calculateAccountBalances($revenueAccounts, $fromDate, $toDate, 'revenue', $totalRevenue);

        // حساب المصروفات
        $this->calculateAccountBalances($expenseAccounts, $fromDate, $toDate, 'expense', $totalExpenses);

        $netProfit = $totalRevenue - $totalExpenses;

        return view('reports.general-profit-loss-report', compact(
            'revenueAccounts',
            'expenseAccounts',
            'totalRevenue',
            'totalExpenses',
            'netProfit',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * حساب أرصدة الحسابات بشكل متداخل
     */
    private function calculateAccountBalances($accounts, $fromDate, $toDate, $type, &$total)
    {
        foreach ($accounts as $account) {
            // حساب رصيد الحساب الحالي
            $balance = JournalDetail::where('account_id', $account->id)
                ->whereHas('head.oper', function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('pro_date', [$fromDate, $toDate]);
                })
                ->sum($type === 'revenue' ? 'credit' : 'debit');

            $account->balance = $balance;
            $account->childrenTotal = 0;

            // حساب أرصدة الحسابات الفرعية
            if ($account->children && $account->children->count() > 0) {
                $childrenTotal = 0;
                $this->calculateAccountBalances($account->children, $fromDate, $toDate, $type, $childrenTotal);
                $account->childrenTotal = $childrenTotal;
            }

            // الإجمالي الكلي للحساب وأبنائه
            $account->totalWithChildren = $balance + $account->childrenTotal;
            $total += $account->totalWithChildren;
        }
    }

    /**
     * Clear all quantity notification caches (useful for testing)
     */
    public function clearAllQuantityNotifications()
    {
        $items = Item::whereNotNull('min_order_quantity')
            ->orWhereNotNull('max_order_quantity')
            ->get();

        $clearedCount = 0;

        foreach ($items as $item) {
            $this->clearQuantityNotification($item->id, 'below_min');
            $this->clearQuantityNotification($item->id, 'above_max');
            $clearedCount += 2;
        }

        return response()->json([
            'success' => true,
            'message' => "تم مسح {$clearedCount} إشعار من الكاش",
            'notifications_cleared' => $clearedCount
        ]);
    }

    /**
     * Get notification status for a specific item (useful for debugging)
     */
    public function getItemNotificationStatus($itemId)
    {
        $item = Item::find($itemId);

        if (!$item) {
            return response()->json(['error' => 'الصنف غير موجود'], 404);
        }

        $currentQuantity = $this->calculateCurrentQuantity($item->id);
        $status = $this->getQuantityStatus($item);

        $notificationInfo = [
            'below_min_cache' => cache()->get("item_quantity_{$itemId}_below_min"),
            'above_max_cache' => cache()->get("item_quantity_{$itemId}_above_max"),
        ];

        return response()->json([
            'item_id' => $item->id,
            'item_name' => $item->name,
            'current_quantity' => $currentQuantity,
            'min_order_quantity' => $item->min_order_quantity,
            'max_order_quantity' => $item->max_order_quantity,
            'status' => $status,
            'notification_cache' => $notificationInfo
        ]);
    }

    public function agingReport()
    {
        $today = now();

        $data = DB::table('operhead as o')
            ->leftJoin('journal_details as jd', 'jd.oper_id', '=', 'o.id')
            ->select(
                'o.id',
                'o.pro_num',
                'o.pro_date',
                'o.end_date as due_date',
                'o.fat_net as invoice_value',
                DB::raw('(o.fat_net - IFNULL(SUM(jd.amount),0)) as balance'),
                DB::raw("
                CASE
                    WHEN DATEDIFF(CURDATE(), o.end_date) <= 30 THEN '0-30 يوم'
                    WHEN DATEDIFF(CURDATE(), o.end_date) BETWEEN 31 AND 60 THEN '31-60 يوم'
                    WHEN DATEDIFF(CURDATE(), o.end_date) BETWEEN 61 AND 90 THEN '61-90 يوم'
                    ELSE '+90 يوم'
                END as aging_bucket
            ")
            )
            ->where('o.isdeleted', 0)
            ->where('o.pro_type', 1) // فواتير مبيعات مثلاً
            ->groupBy('o.id', 'o.pro_num', 'o.pro_date', 'o.end_date', 'o.fat_net')
            ->get();

        return view('reports.oper_aging', compact('data', 'today'));
    }
}
