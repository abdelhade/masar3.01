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
                    <h3 class="mb-1"><i class="fas fa-balance-scale me-2"></i>{{ __('الميزانية العمومية') }}</h3>
                    <small class="text-dark"><i class="far fa-calendar-alt me-1"></i>{{ __('حتى تاريخ:') }} {{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</small>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="expandAll()" title="فتح الكل">
                        <i class="fas fa-plus-circle me-1"></i>{{ __('فتح الكل') }}
                    </button>
                    <button class="btn btn-outline-secondary" onclick="collapseAll()" title="طي الكل">
                        <i class="fas fa-minus-circle me-1"></i>{{ __('طي الكل') }}
                    </button>
                    <button class="btn btn-outline-dark" onclick="window.print()" title="طباعة">
                        <i class="fas fa-print me-1"></i>{{ __('طباعة') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body bg-light">
            <div class="row g-3">
                <!-- الأصول -->
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header text-white">
                            <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>{{ __('الأصول') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="py-2">{{ __('الحساب') }}</th>
                                            <th class="text-end py-2">{{ __('المبلغ') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assets as $asset)
                                            @include('reports::accounts-reports.partials.account-row-recursive', ['account' => $asset, 'level' => 0])
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-primary">
                                        <tr class="fw-bold">
                                            <th class="py-3">{{ __('إجمالي الأصول') }}</th>
                                            <th class="text-end py-3">{{ number_format($totalAssets, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الخصوم وحقوق الملكية -->
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header text-white">
                            <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>{{ __('الخصوم وحقوق الملكية') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="py-2">{{ __('الحساب') }}</th>
                                            <th class="text-end py-2">{{ __('المبلغ') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- الخصوم --}}
                                        @foreach($liabilities as $liability)
                                            @include('reports::accounts-reports.partials.account-row-recursive', ['account' => $liability, 'level' => 0])
                                        @endforeach
                                        
                                        {{-- حقوق الملكية --}}
                                        @foreach($equity as $eq)
                                            @include('reports::accounts-reports.partials.account-row-recursive', ['account' => $eq, 'level' => 0])
                                        @endforeach
                                        
                                        {{-- صافي الربح/الخسارة --}}
                                        <tr class="table-info border-top border-2">
                                            <th class="py-2"><i class="fas fa-chart-line me-2"></i>{{ __('صافي الربح/الخسارة') }}</th>
                                            <th class="text-end py-2 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($netProfit, 2) }}
                                            </th>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr class="fw-bold">
                                            <th class="py-3">{{ __('إجمالي الخصوم وحقوق الملكية') }}</th>
                                            <th class="text-end py-3">{{ number_format($totalLiabilitiesEquity, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ملخص النتيجة -->
            <div class="row mt-4">
                <div class="col-12">
                    @php
                        $difference = abs($totalAssets - $totalLiabilitiesEquity);
                        $isBalanced = $difference < 0.01;
                    @endphp
                    
                    <div class="alert {{ $isBalanced ? 'alert-success' : 'alert-warning' }} shadow-sm border-0">
                        <div class="d-flex align-items-center">
                            <div class="fs-3 me-3">
                                @if($isBalanced)
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                @endif
                            </div>
                            <div>
                                <h5 class="mb-1">{{ __('النتيجة:') }}</h5>
                                @if($isBalanced)
                                    <p class="mb-0"><strong>{{ __('الميزانية متوازنة') }}</strong> ✓</p>
                                @else
                                    <p class="mb-0">
                                        <strong>{{ __('الميزانية غير متوازنة') }}</strong> - 
                                        {{ __('الفرق:') }} <span class="badge bg-warning">{{ number_format($difference, 2) }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ملخص الإحصائيات -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card border-primary shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-bar fs-3 text-primary mb-2"></i>
                            <h6 class="text-muted mb-1">{{ __('إجمالي الأصول') }}</h6>
                            <h4 class="text-primary mb-0">{{ number_format($totalAssets, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-file-invoice-dollar fs-3 text-danger mb-2"></i>
                            <h6 class="text-muted mb-1">{{ __('إجمالي الخصوم') }}</h6>
                            <h4 class="text-danger mb-0">{{ number_format($totalLiabilities, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fs-3 text-warning mb-2"></i>
                            <h6 class="text-muted mb-1">{{ __('حقوق الملكية') }}</h6>
                            <h4 class="text-warning mb-0">{{ number_format($totalEquity, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-{{ $netProfit >= 0 ? 'success' : 'danger' }} shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-{{ $netProfit >= 0 ? 'arrow-up' : 'arrow-down' }} fs-3 text-{{ $netProfit >= 0 ? 'success' : 'danger' }} mb-2"></i>
                            <h6 class="text-muted mb-1">{{ __('صافي الربح/الخسارة') }}</h6>
                            <h4 class="text-{{ $netProfit >= 0 ? 'success' : 'danger' }} mb-0">{{ number_format($netProfit, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* التنسيق الشجري للحسابات */
    .level-0 { 
        font-weight: 600; 
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
    }
    .level-1 { 
        background-color: #ffffff;
    }
    .level-2 { 
        font-size: 0.95em;
        background-color: #fafafa;
    }
    .level-3 { 
        font-size: 0.9em;
        color: #666;
        background-color: #f5f5f5;
    }
    .level-4 { 
        font-size: 0.85em;
        color: #777;
        font-style: italic;
    }

    .account-row[data-level="0"] td:first-child {
        padding-inline-start: 0 !important;
        padding-left: 0 !important;
    }

    .account-row[data-level="1"] td:first-child {
        padding-inline-start: 30px !important;
        padding-left: 30px !important;
    }

    .account-row[data-level="2"] td:first-child {
        padding-inline-start: 50px !important;
        padding-left: 50px !important;
    }

    .account-row[data-level="3"] td:first-child {
        padding-inline-start: 70px !important;
        padding-left: 70px !important;
    }

    .account-row[data-level="4"] td:first-child {
        padding-inline-start: 90px !important;
        padding-left: 90px !important;
    }
    
    /* تحسين المظهر العام */
    .table tbody tr:hover {
        background-color: #f0f8ff !important;
        transition: all 0.2s;
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    /* للطباعة */
    @media print {
        body * {
            visibility: hidden !important;
        }
        #balance-sheet-report,
        #balance-sheet-report * {
            visibility: visible !important;
        }
        #balance-sheet-report {
            position: absolute;
            inset: 0;
            margin: 0 !important;
            width: 100%;
        }
        .btn,
        .sidebar,
        .topbar {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
    
    /* أيقونة الطي/الفتح */
    .collapse-toggle {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 6px;
        border: 1px solid #ced4da;
        background-color: #f8f9fa;
        color: #0d6efd;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .collapse-toggle .toggle-icon {
        font-size: 1rem;
    }

    .collapse-toggle.collapsed {
        color: #0d6efd;
        background-color: #f8f9fa;
    }

    .collapse-toggle:not(.collapsed) {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .collapse-toggle:hover {
        color: #fff;
        background-color: #0b5ed7;
        border-color: #0b5ed7;
    }

    .placeholder-toggle {
        width: 28px;
        height: 28px;
        display: inline-block;
    }
</style>

<script>
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
    }

    function expandAll() {
        document.querySelectorAll('.account-row').forEach(row => {
            row.classList.remove('d-none');
        });

        document.querySelectorAll('.collapse-toggle').forEach(button => {
            updateToggleIcon(button, true);
            button.setAttribute('aria-expanded', 'true');
        });
    }
</script>
@endsection
