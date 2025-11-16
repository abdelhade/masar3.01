@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
@include('components.sidebar.reports')
@endsection

@section('content')
@php
$fromDateFormatted = \Carbon\Carbon::parse($fromDate)->format('d/m/Y');
$toDateFormatted = \Carbon\Carbon::parse($toDate)->format('d/m/Y');
@endphp

<div id="profit-loss-report" class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-chart-line me-2"></i>{{ __('تقرير الأرباح والخسائر') }}
                    </h3>
                    <small class="text-dark">
                        <i class="far fa-calendar-alt me-1"></i>{{ __('الفترة:') }}
                        {{ $fromDateFormatted }} {{ __('إلى') }} {{ $toDateFormatted }}
                    </small>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="expandAll()" title="{{ __('فتح الكل') }}">
                        <i class="fas fa-plus-circle me-1"></i>{{ __('فتح الكل') }}
                    </button>
                    <button class="btn btn-outline-secondary" onclick="collapseAll()" title="{{ __('طي الكل') }}">
                        <i class="fas fa-minus-circle me-1"></i>{{ __('طي الكل') }}
                    </button>
                    <button class="btn btn-outline-dark" onclick="window.print()" title="{{ __('طباعة') }}">
                        <i class="fas fa-print me-1"></i>{{ __('طباعة') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body bg-light">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-3 col-lg-2">
                    <label for="from_date" class="form-label fw-semibold">{{ __('من تاريخ') }}</label>
                    <input type="date" id="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="to_date" class="form-label fw-semibold">{{ __('إلى تاريخ') }}</label>
                    <input type="date" id="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-3 col-lg-2">
                    <button class="btn btn-primary w-100" onclick="updateDateRange()">
                        <i class="fas fa-sync-alt me-1"></i>{{ __('تحديث التقرير') }}
                    </button>
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-2 col-lg-2">
                    <div class="form-check form-switch ps-0 d-flex align-items-center ">
                        <input class="form-check-input" type="checkbox" role="switch" id="hide_zero_accounts">
                        <b class="form-check-label fw-semibold mb-0" for="hide_zero_accounts">
                            {{ __('إخفاء الحسابات ذات الرصيد الصفري') }}
                        </b>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-success shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-money-bill-wave fs-3 text-success mb-2"></i>
                            <h6 class="text-muted mb-1">{{ __('إجمالي الإيرادات') }}</h6>
                            <h3 class="text-success mb-0">{{ number_format($totalRevenue, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-receipt fs-3 text-danger mb-2"></i>
                            <h6 class="text-muted mb-1">{{ __('إجمالي المصروفات') }}</h6>
                            <h3 class="text-danger mb-0">{{ number_format($totalExpenses, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-{{ $netProfit >= 0 ? 'success' : 'warning' }} shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-balance-scale fs-3 text-{{ $netProfit >= 0 ? 'success' : 'warning' }} mb-2"></i>
                            <h6 class="text-muted mb-1">{{ $netProfit >= 0 ? __('صافي الربح') : __('صافي الخسارة') }}</h6>
                            <h3 class="text-{{ $netProfit >= 0 ? 'success' : 'warning' }} mb-0">
                                {{ number_format(abs($netProfit), 2) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-arrow-up me-2"></i>{{ __('الإيرادات') }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="py-2">{{ __('الحساب') }}</th>
                                            <th class="text-end py-2">{{ __('الرصيد') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($revenueAccounts as $account)
                                        @include('reports::accounts-reports.partials.account-row-recursive', ['account' => $account, 'level' => 0])
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-3">{{ __('لا توجد إيرادات في الفترة المحددة') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-primary">
                                        <tr class="fw-bold">
                                            <th class="py-3">{{ __('إجمالي الإيرادات') }}</th>
                                            <th class="text-end py-3">{{ number_format($totalRevenue, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-arrow-down me-2"></i>{{ __('المصروفات') }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="py-2">{{ __('الحساب') }}</th>
                                            <th class="text-end py-2">{{ __('الرصيد') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($expenseAccounts as $account)
                                        @include('reports::accounts-reports.partials.account-row-recursive', ['account' => $account, 'level' => 0])
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-3">{{ __('لا توجد مصروفات في الفترة المحددة') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-danger">
                                        <tr class="fw-bold">
                                            <th class="py-3">{{ __('إجمالي المصروفات') }}</th>
                                            <th class="text-end py-3">{{ number_format($totalExpenses, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert {{ $netProfit >= 0 ? 'alert-success' : 'alert-warning' }} shadow-sm border-0">
                        <div class="d-flex align-items-center">
                            <div class="fs-3 me-3">
                                <i class="fas fa-{{ $netProfit >= 0 ? 'smile-beam text-success' : 'frown text-warning' }}"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ $netProfit >= 0 ? __('النتيجة: ربح') : __('النتيجة: خسارة') }}</h5>
                                <p class="mb-0">
                                    <strong>{{ __('إجمالي الإيرادات:') }}</strong> {{ number_format($totalRevenue, 2) }}
                                    |
                                    <strong>{{ __('إجمالي المصروفات:') }}</strong> {{ number_format($totalExpenses, 2) }}
                                    |
                                    <strong>{{ $netProfit >= 0 ? __('صافي الربح:') : __('صافي الخسارة:') }}</strong>
                                    {{ number_format(abs($netProfit), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

    #profit-loss-report .table tbody tr:hover {
        background-color: #f0f8ff !important;
        transition: all 0.2s;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #profit-loss-report,
        #profit-loss-report * {
            visibility: visible !important;
        }

        #profit-loss-report {
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

    .zero-filter-hidden {
        display: none !important;
    }
</style>

<script>
    const dateRangeAlertMessage = "{{ addslashes(__('الرجاء اختيار التاريخ من وإلى')) }}";

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

        applyZeroFilter();
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

        applyZeroFilter();
    }

    function expandAll() {
        document.querySelectorAll('.account-row').forEach(row => {
            row.classList.remove('d-none');
        });

        document.querySelectorAll('.collapse-toggle').forEach(button => {
            updateToggleIcon(button, true);
            button.setAttribute('aria-expanded', 'true');
        });

        applyZeroFilter();
    }

    function isZeroBranch(row, memo) {
        const key = row.dataset.accountId;
        if (memo.has(key)) {
            return memo.get(key);
        }

        const balance = parseFloat(row.dataset.balance ?? '0');
        const isZero = Math.abs(balance) < 0.0001;
        const hasChildren = row.dataset.hasChildren === '1';

        if (!hasChildren) {
            memo.set(key, isZero);
            return isZero;
        }

        const childRows = Array.from(document.querySelectorAll(`.account-row[data-parent-id="${key}"]`));
        if (childRows.length === 0) {
            memo.set(key, isZero);
            return isZero;
        }

        const result = isZero && childRows.every(child => isZeroBranch(child, memo));
        memo.set(key, result);
        return result;
    }

    function applyZeroFilter() {
        const hideZeroSwitch = document.getElementById('hide_zero_accounts');
        const hideZero = hideZeroSwitch ? hideZeroSwitch.checked : false;
        const memo = new Map();

        document.querySelectorAll('.account-row').forEach(row => {
            if (row.dataset.level === '0') {
                row.classList.remove('zero-filter-hidden');
                return;
            }

            if (hideZero && isZeroBranch(row, memo)) {
                row.classList.add('zero-filter-hidden');
            } else {
                row.classList.remove('zero-filter-hidden');
            }
        });
    }

    function updateDateRange() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;

        if (fromDate && toDate) {
            const url = new URL(window.location);
            url.searchParams.set('from_date', fromDate);
            url.searchParams.set('to_date', toDate);
            window.location.href = url.toString();
        } else {
            alert(dateRangeAlertMessage);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        collapseAll();
        applyZeroFilter();

        const hideZeroSwitch = document.getElementById('hide_zero_accounts');
        if (hideZeroSwitch) {
            hideZeroSwitch.addEventListener('change', applyZeroFilter);
        }
    });
</script>
@endsection