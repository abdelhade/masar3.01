@canany(['view Daily Activity Analyzer', 'view General Journal'])

    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-chart-pie"></i>
            {{ __('General Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">

            @can('view Daily Activity Analyzer')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.overall') }}">
                        <i class="ti-control-record"></i>{{ __('Daily Work Analyzer') }}
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.daily-activity-analyzer') }}">
                        <i class="ti-control-record"></i>{{ __('Daily Activity Analyzer') }}
                    </a>
                </li>
            @endcan

            @can('view General Journal')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.journal-summery') }}">
                        <i class="ti-control-record"></i>{{ __('General Journal') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan

@canany([
    'view Accounts Tree',
    'view Balance Sheet',
    'view Profit Loss Report',
    'view Income Statement Total',
    'view
    Accounts Balance',
    'view Account Movement Report',
    ])
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-book"></i>
            {{ __('Accounts Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            @can('view Accounts Tree')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.accounts-tree') }}">
                        <i class="ti-control-record"></i>{{ __('Accounts Tree') }}
                    </a>
                </li>
            @endcan

            @can('view Balance Sheet')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.general-balance-sheet') }}">
                        <i class="ti-control-record"></i>{{ __('Balance Sheet') }}
                    </a>
                </li>
            @endcan

            @can('view Profit Loss Report')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.general-profit-loss-report') }}">
                        <i class="ti-control-record"></i>{{ __('Profit & Loss') }}
                    </a>
                </li>
            @endcan

            @can('view Income Statement Total')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.general-profit-loss-report-total') }}">
                        <i class="ti-control-record"></i>{{ __('Income Statement Total') }}
                    </a>
                </li>
            @endcan

            @can('view Accounts Balance')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reports.general-account-balances') }}">
                        <i class="ti-control-record"></i>{{ __('Accounts Balance') }}
                    </a>
                </li>
            @endcan

            @can('view Account Movement Report')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('account-movement') }}">
                        <i class="ti-control-record"></i>{{ __('Account Movement') }}
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan

@can('view Items Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-boxes"></i>
            {{ __('Inventory Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-inventory-balances') }}">
                    <i class="ti-control-record"></i>{{ __('Items List with Balances') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-inventory-balances-by-store') }}">
                    <i class="ti-control-record"></i>{{ __('Balances by Warehouse') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-inventory-movements') }}">
                    <i class="ti-control-record"></i>{{ __('Item Movement') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.get-items-max-min-quantity') }}">
                    <i class="ti-control-record"></i>{{ __('Min & Max Order Limit') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('prices.compare.report') }}">
                    <i class="ti-control-record"></i>{{ __('Price Comparison') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.items.inactive') }}">
                    <i class="ti-control-record"></i>{{ __('Inactive Items') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.items.idle') }}">
                    <i class="ti-control-record"></i>{{ __('Idle Items Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.items.most-expensive') }}">
                    <i class="ti-control-record"></i>{{ __('reports.most_expensive_items_report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.items.with-stores') }}">
                    <i class="ti-control-record"></i>{{ __('Items per Warehouse') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.inventory-discrepancy-report') }}">
                    <i class="ti-control-record"></i>{{ __('Inventory Discrepancy Monitoring') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view Sales Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-shopping-cart"></i>
            {{ __('Sales Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-sales-report') }}">
                    <i class="ti-control-record"></i>{{ __('General Sales Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.sales.total') }}">
                    <i class="ti-control-record"></i>{{ __('Sales Totals') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.sales.items') }}">
                    <i class="ti-control-record"></i>{{ __('Sales Items') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.sales.representative') }}">
                    <i class="ti-control-record"></i>{{ __('Sales by Representative') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-sales-report-by-address') }}">
                    <i class="ti-control-record"></i>{{ __('Sales by Address') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.item-sales') }}">
                    <i class="ti-control-record"></i>{{ __('Item Sales') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('sales.invoice-report') }}">
                    <i class="ti-control-record"></i>{{ __('Sales Invoices') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('sales-orders-report') }}">
                    <i class="ti-control-record"></i>{{ __('Sales Orders') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase-quotations-reports') }}">
                    <i class="ti-control-record"></i>{{ __('Customer Quotations') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view Purchases Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-shopping-basket"></i>
            {{ __('Purchase Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.purchasing.dashboard') }}">
                    <i class="ti-control-record"></i>{{ __('Purchasing Dashboard') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-purchases-report') }}">
                    <i class="ti-control-record"></i>{{ __('General Purchases Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-purchases-daily-report') }}">
                    <i class="ti-control-record"></i>{{ __('Daily Purchases') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.purchases.total') }}">
                    <i class="ti-control-record"></i>{{ __('Purchases Totals') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.purchases.items') }}">
                    <i class="ti-control-record"></i>{{ __('Purchases Items') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.item-purchase') }}">
                    <i class="ti-control-record"></i>{{ __('Item Purchase') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('billing.invoice-report') }}">
                    <i class="ti-control-record"></i>{{ __('Purchase Invoices') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('supplier-rfqs-report') }}">
                    <i class="ti-control-record"></i>{{ __('Supplier Quotations') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view Customer Quotation Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-users"></i>
            {{ __('Customers Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-customers-report') }}">
                    <i class="ti-control-record"></i>{{ __('General Customers Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-customers-daily-report') }}">
                    <i class="ti-control-record"></i>{{ __('Daily Customers Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-customers-items-report') }}">
                    <i class="ti-control-record"></i>{{ __('Customers Items Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-customers-total-report') }}">
                    <i class="ti-control-record"></i>{{ __('Customers Totals Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.customer-debt-history') }}">
                    <i class="ti-control-record"></i>{{ __('Customer Debt Aging') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view Supplier Quotation Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-truck"></i>
            {{ __('Suppliers Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-suppliers-report') }}">
                    <i class="ti-control-record"></i>{{ __('General Suppliers Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-suppliers-daily-report') }}">
                    <i class="ti-control-record"></i>{{ __('Daily Suppliers Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-suppliers-items-report') }}">
                    <i class="ti-control-record"></i>{{ __('Suppliers Items Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-suppliers-total-report') }}">
                    <i class="ti-control-record"></i>{{ __('Suppliers Totals Report') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view Expenses Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-file-invoice-dollar"></i>
            {{ __('Expenses Reports') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-expenses-report') }}">
                    <i class="ti-control-record"></i>{{ __('General Expenses Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-expenses-daily-report') }}">
                    <i class="ti-control-record"></i>{{ __('Expense Account Statement') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.expenses-balance-report') }}">
                    <i class="ti-control-record"></i>{{ __('Expenses Balance') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cost-centers-report') }}">
                    <i class="ti-control-record"></i>{{ __('Cost Centers Report') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cost-center-account-statement') }}">
                    <i class="ti-control-record"></i>{{ __('Cost Center Account Statement') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cost-centers-list') }}">
                    <i class="ti-control-record"></i>{{ __('Cost Centers List') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view General Cashbox Movement Report')
    <li class="nav-item has-submenu">
        <a class="nav-link" href="javascript: void(0);">
            <i class="fas fa-money-bill-wave"></i>
            {{ __('Cash & Bank') }}
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cashbox-movement-report') }}">
                    <i class="ti-control-record"></i>{{ __('Cashbox Movement') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cash-bank-report') }}">
                    <i class="ti-control-record"></i>{{ __('Bank Movement') }}
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('view Manufacturing Invoices Report')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('manufacturing.invoice.report') }}">
            <i class="fas fa-industry"></i>
            <span>{{ __('Manufacturing Reports') }}</span>
        </a>
    </li>
@endcan
