@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div id="balance-sheet-report" class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1"><i class="fas fa-balance-scale me-2"></i>{{ __('Balance Sheet') }}</h3>
                        <small class="text-dark"><i class="far fa-calendar-alt me-1"></i>{{ __('Until Date:') }}
                            {{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</small>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="expandAll()" title="{{ __('Expand All') }}">
                            <i class="fas fa-plus-circle me-1"></i>{{ __('Expand All') }}
                        </button>
                        <button class="btn btn-outline-secondary" onclick="collapseAll()" title="{{ __('Collapse All') }}">
                            <i class="fas fa-minus-circle me-1"></i>{{ __('Collapse All') }}
                        </button>
                        <button class="btn btn-outline-info" onclick="compareBalances()"
                            title="{{ __('Compare Balances') }}" id="compareBtn">
                            <i class="fas fa-balance-scale me-1"></i>{{ __('Compare Balances') }}
                        </button>
                        <button class="btn btn-outline-dark" onclick="window.print()" title="{{ __('Print') }}">
                            <i class="fas fa-print me-1"></i>{{ __('Print') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">
                    <!-- Assets -->
                    <div class="col-lg-6">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header text-white">
                                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>{{ __('Assets') }}</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="py-2">{{ __('Account') }}</th>
                                                <th class="text-end py-2">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="assets-tbody">
                                            @foreach ($assets as $asset)
                                                @include(
                                                    'reports::accounts-reports.partials.account-row-recursive',
                                                    ['account' => $asset, 'level' => 0, 'section' => 'assets']
                                                )
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-primary">
                                            <tr class="fw-bold">
                                                <th class="py-3">{{ __('Total Assets') }}</th>
                                                <th class="text-end py-3" id="total-assets-display">
                                                    {{ number_format($totalAssets, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liabilities and Equity -->
                    <div class="col-lg-6">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header text-white">
                                <h5 class="mb-0"><i
                                        class="fas fa-hand-holding-usd me-2"></i>{{ __('Liabilities and Equity') }}</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="py-2">{{ __('Account') }}</th>
                                                <th class="text-end py-2">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="liabilities-equity-tbody">
                                            {{-- Liabilities --}}
                                            @foreach ($liabilities as $liability)
                                                @include(
                                                    'reports::accounts-reports.partials.account-row-recursive',
                                                    [
                                                        'account' => $liability,
                                                        'level' => 0,
                                                        'section' => 'liabilities',
                                                    ]
                                                )
                                            @endforeach

                                            {{-- Equity --}}
                                            @foreach ($equity as $eq)
                                                @include(
                                                    'reports::accounts-reports.partials.account-row-recursive',
                                                    ['account' => $eq, 'level' => 0, 'section' => 'equity']
                                                )
                                            @endforeach

                                            {{-- Net Profit/Loss --}}
                                            <tr class="table-info border-top border-2">
                                                <th class="py-2"><i
                                                        class="fas fa-chart-line me-2"></i>{{ __('Net Profit/Loss') }}</th>
                                                <th
                                                    class="text-end py-2 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($netProfit, 2) }}
                                                </th>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-success">
                                            <tr class="fw-bold">
                                                <th class="py-3">{{ __('Total Liabilities and Equity') }}</th>
                                                <th class="text-end py-3" id="total-liabilities-equity-display">
                                                    {{ number_format($totalLiabilitiesEquity, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Result Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div id="balance-result-alert"
                            class="alert {{ $isBalanced ? 'alert-success' : 'alert-warning' }} shadow-sm border-0">
                            <div class="d-flex align-items-center">
                                <div class="fs-3 me-3">
                                    @if ($isBalanced)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ __('Result:') }}</h5>
                                    @if ($isBalanced)
                                        <p class="mb-0"><strong>{{ __('Balance Sheet is Balanced') }}</strong> ✓</p>
                                    @else
                                        <p class="mb-0">
                                            <strong>{{ __('Balance Sheet is Unbalanced') }}</strong> -
                                            {{ __('Difference:') }}
                                            <span class="badge bg-danger ms-2"
                                                id="balance-difference-badge">{{ number_format($difference, 2) }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Summary -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fs-3 text-primary mb-2"></i>
                                <h6 class="text-muted mb-1">{{ __('Total Assets') }}</h6>
                                <h4 class="text-primary mb-0" id="total-assets-summary">
                                    {{ number_format($totalAssets, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-hand-holding-usd fs-3 text-info mb-2"></i>
                                <h6 class="text-muted mb-1">{{ __('Liabilities and Equity') }}</h6>
                                <h4 class="text-info mb-0" id="total-liabilities-equity-summary">
                                    {{ number_format($totalLiabilities + $totalEquity, 2) }}</h4>
                                <small class="text-muted d-block mt-2">
                                    <span class="me-3">{{ __('Liabilities:') }} <strong
                                            id="total-liabilities-summary">{{ number_format($totalLiabilities, 2) }}</strong></span>
                                    <span>{{ __('Equity:') }} <strong
                                            id="total-equity-summary">{{ number_format($totalEquity, 2) }}</strong></span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-{{ $netProfit >= 0 ? 'success' : 'danger' }} shadow-sm">
                            <div class="card-body text-center">
                                <i
                                    class="fas fa-{{ $netProfit >= 0 ? 'arrow-up' : 'arrow-down' }} fs-3 text-{{ $netProfit >= 0 ? 'success' : 'danger' }} mb-2"></i>
                                <h6 class="text-muted mb-1">{{ __('Net Profit/Loss') }}</h6>
                                <h4 class="text-{{ $netProfit >= 0 ? 'success' : 'danger' }} mb-0">
                                    {{ number_format($netProfit, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compare Balances Modal -->
    <div class="modal fade" id="compareBalancesModal" tabindex="-1" aria-labelledby="compareBalancesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="compareBalancesModalLabel">
                        <i class="fas fa-balance-scale me-2"></i>{{ __('Compare Account Balances with Journal Entries') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="compareLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                        <p class="mt-3">{{ __('Comparing Balances...') }}</p>
                    </div>
                    <div id="compareResults" style="display: none;">
                        <div class="alert alert-info mb-3">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <strong>{{ __('Total Accounts:') }}</strong>
                                    <span id="totalAccounts" class="badge bg-primary ms-2">0</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>{{ __('Accounts with Difference:') }}</strong>
                                    <span id="accountsWithDifference" class="badge bg-warning ms-2">0</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>{{ __('Total Difference:') }}</strong>
                                    <span id="totalDifference" class="badge bg-danger ms-2">0.00</span>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>{{ __('Account Code') }}</th>
                                        <th>{{ __('Account Name') }}</th>
                                        <th class="text-end">{{ __('Account Balance') }}</th>
                                        <th class="text-end">{{ __('Journal Balance') }}</th>
                                        <th class="text-end">{{ __('Difference') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="compareTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="compareError" style="display: none;">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="errorMessage"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // حفظ النصوص المترجمة في متغيرات JavaScript
            const translations = {
                result: __(__('Result:')),
                balanced: __(__('Balance Sheet is Balanced')),
                unbalanced: __(__('Balance Sheet is Unbalanced')),
                difference: __(__('Difference:')),
                noAccounts: __(__('No Accounts to Compare')),
                hasDifference: __(__('There is a Difference')),
                matched: __(__('Identical')),
                comparisonError: __(__('Error During Comparison')),
                serverError: __(__('Server Connection Error'))
            };

            function updateToggleIcon(button, expanded) {
                const icon = button.querySelector('.toggle-icon');
                if (!icon) {
                    return;
                }

                if (expanded) {
                    icon.classList.remove('fa-plus-square');
                    icon.classList.add('fa-minus-square');
                    button.classList.remove('collapsed');
                } else {
                    icon.classList.remove('fa-minus-square');
                    icon.classList.add('fa-plus-square');
                    button.classList.add('collapsed');
                }
            }

            function showBranch(accountId) {
                document.querySelectorAll(`tr.children-${accountId}`).forEach(row => {
                    row.classList.remove('d-none');
                    const toggle = row.querySelector('.collapse-toggle');
                    if (toggle && !toggle.classList.contains('collapsed')) {
                        showBranch(row.dataset.accountId);
                    }
                });
            }

            function hideBranch(accountId) {
                document.querySelectorAll(`tr.children-${accountId}`).forEach(row => {
                    row.classList.add('d-none');
                    const toggle = row.querySelector('.collapse-toggle');
                    if (toggle) {
                        updateToggleIcon(toggle, false);
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                    hideBranch(row.dataset.accountId);
                });
            }

            function toggleChildren(button) {
                const accountId = button.dataset.accountId;
                const isCollapsed = button.classList.contains('collapsed');

                if (isCollapsed) {
                    updateToggleIcon(button, true);
                    button.setAttribute('aria-expanded', 'true');
                    showBranch(accountId);
                } else {
                    updateToggleIcon(button, false);
                    button.setAttribute('aria-expanded', 'false');
                    hideBranch(accountId);
                }

                // تحديث إجمالي الأصول بعد تغيير حالة الطي/الفتح
                calculateTotalAssets();
                // تحديث إجمالي الخصوم وحقوق الملكية بعد تغيير حالة الطي/الفتح
                calculateTotalLiabilitiesEquity();
            }

            function collapseAll() {
                document.querySelectorAll('.account-row').forEach(row => {
                    if (row.dataset.level !== '0') {
                        row.classList.add('d-none');
                    }
                });

                document.querySelectorAll('.collapse-toggle').forEach(button => {
                    updateToggleIcon(button, false);
                    button.setAttribute('aria-expanded', 'false');
                });

                // تحديث إجمالي الأصول بعد طي الكل
                calculateTotalAssets();
                // تحديث إجمالي الخصوم وحقوق الملكية بعد طي الكل
                calculateTotalLiabilitiesEquity();
            }

            function expandAll() {
                document.querySelectorAll('.account-row').forEach(row => {
                    row.classList.remove('d-none');
                });

                document.querySelectorAll('.collapse-toggle').forEach(button => {
                    updateToggleIcon(button, true);
                    button.setAttribute('aria-expanded', 'true');
                });

                // تحديث إجمالي الأصول بعد فتح الكل
                calculateTotalAssets();
                // تحديث إجمالي الخصوم وحقوق الملكية بعد فتح الكل
                calculateTotalLiabilitiesEquity();
            }

            function compareBalances() {
                const modal = new bootstrap.Modal(document.getElementById('compareBalancesModal'));
                modal.show();

                const loadingDiv = document.getElementById('compareLoading');
                const resultsDiv = document.getElementById('compareResults');
                const errorDiv = document.getElementById('compareError');
                const compareBtn = document.getElementById('compareBtn');

                // إظهار التحميل وإخفاء النتائج والأخطاء
                loadingDiv.style.display = 'block';
                resultsDiv.style.display = 'none';
                errorDiv.style.display = 'none';
                compareBtn.disabled = true;

                const asOfDate = $asOfDate;

                fetch(`{{ route('reports.compare-account-balances') }}?as_of_date=${asOfDate}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        loadingDiv.style.display = 'none';
                        compareBtn.disabled = false;

                        if (data.success) {
                            // تحديث الإحصائيات
                            document.getElementById('totalAccounts').textContent = data.total_accounts;
                            document.getElementById('accountsWithDifference').textContent = data.accounts_with_difference;
                            document.getElementById('totalDifference').textContent = parseFloat(data.total_difference)
                                .toFixed(2);

                            // ملء الجدول
                            const tbody = document.getElementById('compareTableBody');
                            tbody.innerHTML = '';

                            if (data.comparisons.length === 0) {
                                tbody.innerHTML =
                                    `<tr><td colspan="6" class="text-center text-muted">${translations.noAccounts}</td></tr>`;
                            } else {
                                data.comparisons.forEach(comparison => {
                                    const row = document.createElement('tr');
                                    row.className = comparison.has_difference ? 'table-warning' : '';

                                    const statusBadge = comparison.has_difference ?
                                        `<span class="badge bg-danger">${translations.hasDifference}</span>` :
                                        `<span class="badge bg-success">${translations.matched}</span>`;

                                    row.innerHTML = `
                            <td>${comparison.code}</td>
                            <td>${comparison.name}</td>
                            <td class="text-end">${parseFloat(comparison.account_balance).toFixed(2)}</td>
                            <td class="text-end">${parseFloat(comparison.journal_balance).toFixed(2)}</td>
                            <td class="text-end ${Math.abs(comparison.difference) > 0.01 ? 'text-danger fw-bold' : 'text-success'}">
                                ${parseFloat(comparison.difference).toFixed(2)}
                            </td>
                            <td class="text-center">${statusBadge}</td>
                        `;
                                    tbody.appendChild(row);
                                });
                            }

                            resultsDiv.style.display = 'block';
                        } else {
                            errorDiv.style.display = 'block';
                            document.getElementById('errorMessage').textContent = data.message || translations
                                .comparisonError;
                        }
                    })
                    .catch(error => {
                        loadingDiv.style.display = 'none';
                        compareBtn.disabled = false;
                        errorDiv.style.display = 'block';
                        document.getElementById('errorMessage').textContent = translations.serverError;
                        console.error('Error:', error);
                    });
            }

            /**
             * حساب إجمالي الأصول من الصفوف المرئية فقط (Client Side)
             */
            function calculateTotalAssets() {
                let total = 0;

                // جلب جميع صفوف الأصول المرئية فقط
                const assetsRows = document.querySelectorAll(
                    '#assets-tbody tr.account-row[data-section="assets"]:not(.d-none)');

                assetsRows.forEach(row => {
                    const hasChildren = row.dataset.hasChildren === '1';
                    const accountId = row.dataset.accountId;

                    // إذا كان الحساب له أطفال، تحقق إذا كانت الحسابات الفرعية ظاهرة أم مخفية
                    if (hasChildren) {
                        // تحقق إذا كانت الحسابات الفرعية مخفية
                        const childrenRows = document.querySelectorAll(`tr.children-${accountId}:not(.d-none)`);
                        // إذا كانت الحسابات الفرعية مخفية، استخدم totalWithChildren (يشمل الأطفال)
                        // إذا كانت ظاهرة، تجاهل الحساب الرئيسي لأننا سنحسب الحسابات الفرعية
                        if (childrenRows.length === 0) {
                            const totalWithChildren = parseFloat(row.dataset.totalWithChildren) || 0;
                            total += totalWithChildren;
                        }
                    } else {
                        // الحساب ليس له أطفال (leaf account)، استخدم balance مباشرة
                        const balance = parseFloat(row.dataset.balance) || 0;
                        total += balance;
                    }
                });

                // تحديث الإجمالي في tfoot
                const totalAssetsDisplay = document.getElementById('total-assets-display');
                if (totalAssetsDisplay) {
                    totalAssetsDisplay.textContent = total.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // تحديث الإجمالي في ملخص الإحصائيات
                const totalAssetsSummary = document.getElementById('total-assets-summary');
                if (totalAssetsSummary) {
                    totalAssetsSummary.textContent = total.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // تحديث نتيجة التوازن
                calculateTotalLiabilitiesEquity();
            }

            /**
             * حساب إجمالي الخصوم وحقوق الملكية من الصفوف المرئية فقط (Client Side)
             */
            function calculateTotalLiabilitiesEquity() {
                // حساب إجمالي الخصوم من الصفوف المرئية
                const liabilitiesRows = document.querySelectorAll(
                    '#liabilities-equity-tbody tr.account-row[data-section="liabilities"]:not(.d-none)');
                let totalLiabilities = 0;

                liabilitiesRows.forEach(row => {
                    const hasChildren = row.dataset.hasChildren === '1';
                    const accountId = row.dataset.accountId;

                    // إذا كان الحساب له أطفال، تحقق إذا كانت الحسابات الفرعية ظاهرة أم مخفية
                    if (hasChildren) {
                        // تحقق إذا كانت الحسابات الفرعية مخفية
                        const childrenRows = document.querySelectorAll(`tr.children-${accountId}:not(.d-none)`);
                        // إذا كانت الحسابات الفرعية مخفية، استخدم totalWithChildren (يشمل الأطفال)
                        // إذا كانت ظاهرة، تجاهل الحساب الرئيسي لأننا سنحسب الحسابات الفرعية
                        if (childrenRows.length === 0) {
                            const totalWithChildren = parseFloat(row.dataset.totalWithChildren) || 0;
                            totalLiabilities += totalWithChildren;
                        }
                    } else {
                        // الحساب ليس له أطفال (leaf account)، استخدم balance مباشرة
                        const balance = parseFloat(row.dataset.balance) || 0;
                        totalLiabilities += balance;
                    }
                });

                // تحديث إجمالي الخصوم في ملخص الإحصائيات
                const totalLiabilitiesSummary = document.getElementById('total-liabilities-summary');
                if (totalLiabilitiesSummary) {
                    totalLiabilitiesSummary.textContent = totalLiabilities.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // حساب إجمالي حقوق الملكية من الصفوف المرئية
                const equityRows = document.querySelectorAll(
                    '#liabilities-equity-tbody tr.account-row[data-section="equity"]:not(.d-none)');
                let totalEquity = 0;

                equityRows.forEach(row => {
                    const hasChildren = row.dataset.hasChildren === '1';
                    const accountId = row.dataset.accountId;

                    // إذا كان الحساب له أطفال، تحقق إذا كانت الحسابات الفرعية ظاهرة أم مخفية
                    if (hasChildren) {
                        // تحقق إذا كانت الحسابات الفرعية مخفية
                        const childrenRows = document.querySelectorAll(`tr.children-${accountId}:not(.d-none)`);
                        // إذا كانت الحسابات الفرعية مخفية، استخدم totalWithChildren (يشمل الأطفال)
                        // إذا كانت ظاهرة، تجاهل الحساب الرئيسي لأننا سنحسب الحسابات الفرعية
                        if (childrenRows.length === 0) {
                            const totalWithChildren = parseFloat(row.dataset.totalWithChildren) || 0;
                            totalEquity += totalWithChildren;
                        }
                    } else {
                        // الحساب ليس له أطفال (leaf account)، استخدم balance مباشرة
                        const balance = parseFloat(row.dataset.balance) || 0;
                        totalEquity += balance;
                    }
                });

                // تحديث إجمالي حقوق الملكية في ملخص الإحصائيات
                const totalEquitySummary = document.getElementById('total-equity-summary');
                if (totalEquitySummary) {
                    totalEquitySummary.textContent = totalEquity.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // تحديث إجمالي الخصوم وحقوق الملكية المدمج في الكرت
                const totalLiabilitiesEquitySummary = document.getElementById('total-liabilities-equity-summary');
                if (totalLiabilitiesEquitySummary) {
                    const totalLiabilitiesEquity = totalLiabilities + totalEquity;
                    totalLiabilitiesEquitySummary.textContent = totalLiabilitiesEquity.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // صافي الربح/الخسارة (من الخادم - لا يتغير)
                const netProfitRow = document.querySelector('#liabilities-equity-tbody tr.table-info');
                let netProfit = 0;
                if (netProfitRow) {
                    const netProfitText = netProfitRow.querySelector('th.text-end').textContent.trim();
                    // إزالة الفواصل والمسافات والتعامل مع القيم السالبة
                    const cleanedText = netProfitText.replace(/,/g, '').replace(/\s/g, '');
                    netProfit = parseFloat(cleanedText) || 0;
                }

                // حساب إجمالي الخصوم وحقوق الملكية
                const totalLiabilitiesEquity = totalLiabilities + totalEquity + netProfit;

                // تحديث الإجمالي في tfoot
                const totalLiabilitiesEquityDisplay = document.getElementById('total-liabilities-equity-display');
                if (totalLiabilitiesEquityDisplay) {
                    totalLiabilitiesEquityDisplay.textContent = totalLiabilitiesEquity.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // تحديث نتيجة التوازن
                updateBalanceResult(totalLiabilitiesEquity);
            }

            /**
             * تحديث نتيجة التوازن في الميزانية
             */
            function updateBalanceResult(totalLiabilitiesEquity) {
                // حساب إجمالي الأصول من الصفوف المرئية (نفس منطق calculateTotalAssets)
                const assetsRows = document.querySelectorAll(
                    '#assets-tbody tr.account-row[data-section="assets"]:not(.d-none)');
                let totalAssets = 0;

                assetsRows.forEach(row => {
                    const hasChildren = row.dataset.hasChildren === '1';
                    const accountId = row.dataset.accountId;

                    if (hasChildren) {
                        const childrenRows = document.querySelectorAll(`tr.children-${accountId}:not(.d-none)`);
                        if (childrenRows.length === 0) {
                            const totalWithChildren = parseFloat(row.dataset.totalWithChildren) || 0;
                            totalAssets += totalWithChildren;
                        }
                    } else {
                        const balance = parseFloat(row.dataset.balance) || 0;
                        totalAssets += balance;
                    }
                });

                const difference = Math.abs(totalAssets - totalLiabilitiesEquity);
                const isBalanced = difference < 0.01;

                // تحديث رسالة التوازن
                const resultAlert = document.getElementById('balance-result-alert');
                const differenceBadge = document.getElementById('balance-difference-badge');

                if (resultAlert) {
                    if (isBalanced) {
                        resultAlert.className = 'alert alert-success shadow-sm border-0';
                        resultAlert.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="fs-3 me-3">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">${translations.result}</h5>
                            <p class="mb-0"><strong>${translations.balanced}</strong> ✓</p>
                        </div>
                    </div>
                `;
                    } else {
                        resultAlert.className = 'alert alert-warning shadow-sm border-0';
                        const formattedDifference = difference.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        resultAlert.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="fs-3 me-3">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">${translations.result}</h5>
                            <p class="mb-0">
                                <strong>${translations.unbalanced}</strong> -
                                ${translations.difference}
                                <span class="badge bg-danger ms-2">${formattedDifference}</span>
                            </p>
                        </div>
                    </div>
                `;
                    }
                }
            }

            // حساب الإجماليات عند تحميل الصفحة
            document.addEventListener('DOMContentLoaded', function() {
                calculateTotalAssets();
                calculateTotalLiabilitiesEquity();
            });
        </script>
    @endpush

    @push('styles')
        <style>
            /* تمييز الحسابات الأساسية */
            tr.account-basic {
                background-color: #f0f7ff !important;
                border-left: 3px solid #0d6efd;
            }

            tr.account-basic:hover {
                background-color: #e0efff !important;
            }

            tr.account-basic td {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            /* تمييز الحسابات الفرعية */
            tr.account-sub {
                background-color: #ffffff;
                border-left: 2px solid #e9ecef;
            }

            tr.account-sub:hover {
                background-color: #f8f9fa !important;
            }

            tr.account-sub td {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
                font-size: 0.9rem;
            }

            /* تمييز حسب المستوى */
            tr.account-row.level-0 {
                font-weight: 600;
            }

            tr.account-row.level-1 {
                padding-left: 1rem;
            }

            tr.account-row.level-2 {
                padding-left: 2rem;
            }

            tr.account-row.level-3 {
                padding-left: 3rem;
            }

            /* تحسين الأيقونات */
            tr.account-basic .fa-folder-open,
            tr.account-basic .fa-file-alt {
                color: #0d6efd !important;
            }

            tr.account-sub .fa-folder,
            tr.account-sub .fa-file {
                color: #6c757d !important;
            }

            /* تحسين الـ badge */
            .badge.bg-primary.bg-opacity-10 {
                background-color: rgba(13, 110, 253, 0.1) !important;
                border: 1px solid rgba(13, 110, 253, 0.3);
                font-weight: 500;
                padding: 0.25rem 0.5rem;
            }
        </style>
    @endpush
@endsection
