@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    <style>
        .tree-table tbody tr {
            transition: all 0.3s ease;
        }

        .tree-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .tree-table tbody tr.level-0 {
            background-color: #f1f3f5;
            font-weight: bold;
        }

        .tree-table tbody tr.level-1 {
            background-color: #f8f9fa;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .toggle-icon.collapsed {
            transform: rotate(-90deg);
        }

        .account-row.hidden {
            display: none;
        }

        .tree-table th {
            background-color: #e9ecef;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">تقرير الأرباح والخسائر - عرض شجري</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="from_date" class="form-label fw-bold">من تاريخ:</label>
                                <input type="date" id="from_date" class="form-control" value="{{ $fromDate }}"
                                    onchange="updateDateRange()">
                            </div>
                            <div class="col-md-4">
                                <label for="to_date" class="form-label fw-bold">إلى تاريخ:</label>
                                <input type="date" id="to_date" class="form-control" value="{{ $toDate }}"
                                    onchange="updateDateRange()">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary me-2" onclick="updateDateRange()">
                                    <i class="fas fa-search"></i> تحديث التقرير
                                </button>
                                <button class="btn btn-secondary" onclick="expandAll()">
                                    <i class="fas fa-expand"></i> فتح الكل
                                </button>
                                <button class="btn btn-secondary ms-2" onclick="collapseAll()">
                                    <i class="fas fa-compress"></i> إغلاق الكل
                                </button>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title fw-bold">إجمالي الإيرادات</h5>
                                        <h1 class="text-white">{{ number_format($totalRevenue, 2) }}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title fw-bold">إجمالي المصروفات</h5>
                                        <h1 class="text-white">{{ number_format($totalExpenses, 2) }}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card {{ $netProfit >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title fw-bold">{{ $netProfit >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}
                                        </h5>
                                        <h1 class="text-white">{{ number_format(abs($netProfit), 2) }}</h1>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title fw-bold mb-0">
                                            <i class="fas fa-chart-line"></i> الإيرادات
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover tree-table">
                                                <thead class="table-success">
                                                    <tr>
                                                        <th class="fw-bold" width="15%">كود الحساب</th>
                                                        <th class="fw-bold" width="40%">اسم الحساب</th>
                                                        <th class="fw-bold text-end" width="20%">رصيد الحساب</th>
                                                        <th class="fw-bold text-end" width="25%">الإجمالي مع الفروع</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($revenueAccounts as $account)
                                                        <x-reports::account-tree-row :account="$account" :level="0"
                                                            type="revenue" />
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">لا توجد إيرادات في الفترة
                                                                المحددة</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                                <tfoot class="table-success">
                                                    <tr>
                                                        <th colspan="3" class="text-start fw-bold">إجمالي الإيرادات:</th>
                                                        <th class="text-end fw-bold text-success">
                                                            {{ number_format($totalRevenue, 2) }}</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Expenses Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title fw-bold mb-0">
                                            <i class="fas fa-chart-line"></i> المصروفات
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover tree-table">
                                                <thead class="table-danger">
                                                    <tr>
                                                        <th class="fw-bold" width="15%">كود الحساب</th>
                                                        <th class="fw-bold" width="40%">اسم الحساب</th>
                                                        <th class="fw-bold text-end" width="20%">رصيد الحساب</th>
                                                        <th class="fw-bold text-end" width="25%">الإجمالي مع الفروع</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($expenseAccounts as $account)
                                                        <x-reports::account-tree-row :account="$account" :level="0"
                                                            type="expense" />
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">لا توجد مصروفات في الفترة
                                                                المحددة</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                                <tfoot class="table-danger">
                                                    <tr>
                                                        <th colspan="3" class="text-start fw-bold">إجمالي المصروفات:</th>
                                                        <th class="text-end fw-bold text-danger">
                                                            {{ number_format($totalExpenses, 2) }}</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert {{ $netProfit >= 0 ? 'alert-success' : 'alert-warning' }}">
                                    <h5 class="fw-bold">
                                        <i
                                            class="fas {{ $netProfit >= 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                                        {{ $netProfit >= 0 ? 'النتيجة: ربح' : 'النتيجة: خسارة' }}
                                    </h5>
                                    <p class="mb-0">
                                        <strong>إجمالي الإيرادات:</strong> {{ number_format($totalRevenue, 2) }} جنيه |
                                        <strong>إجمالي المصروفات:</strong> {{ number_format($totalExpenses, 2) }} جنيه |
                                        <strong>{{ $netProfit >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}:</strong>
                                        {{ number_format(abs($netProfit), 2) }} جنيه
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleChildren(icon, accountId) {
            const row = icon.closest('tr');
            const level = parseInt(row.dataset.level);
            let nextRow = row.nextElementSibling;

            // toggle icon
            if (icon.classList.contains('fa-minus-square')) {
                icon.classList.remove('fa-minus-square');
                icon.classList.add('fa-plus-square');
            } else {
                icon.classList.remove('fa-plus-square');
                icon.classList.add('fa-minus-square');
            }

            // toggle children rows
            while (nextRow && parseInt(nextRow.dataset.level) > level) {
                const nextLevel = parseInt(nextRow.dataset.level);

                if (nextLevel === level + 1) {
                    // Direct children
                    nextRow.classList.toggle('hidden');

                    // If hiding, also hide all descendants
                    if (nextRow.classList.contains('hidden')) {
                        const childIcon = nextRow.querySelector('.toggle-icon');
                        if (childIcon && childIcon.classList.contains('fa-minus-square')) {
                            childIcon.classList.remove('fa-minus-square');
                            childIcon.classList.add('fa-plus-square');
                        }
                        hideDescendants(nextRow);
                    }
                }

                nextRow = nextRow.nextElementSibling;
            }
        }

        function hideDescendants(parentRow) {
            const level = parseInt(parentRow.dataset.level);
            let nextRow = parentRow.nextElementSibling;

            while (nextRow && parseInt(nextRow.dataset.level) > level) {
                nextRow.classList.add('hidden');
                const childIcon = nextRow.querySelector('.toggle-icon');
                if (childIcon && childIcon.classList.contains('fa-minus-square')) {
                    childIcon.classList.remove('fa-minus-square');
                    childIcon.classList.add('fa-plus-square');
                }
                nextRow = nextRow.nextElementSibling;
            }
        }

        function expandAll() {
            document.querySelectorAll('.account-row.hidden').forEach(row => {
                row.classList.remove('hidden');
            });
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                icon.classList.remove('fa-plus-square');
                icon.classList.add('fa-minus-square');
            });
        }

        function collapseAll() {
            // Hide all rows except level 0
            document.querySelectorAll('.account-row').forEach(row => {
                if (parseInt(row.dataset.level) > 0) {
                    row.classList.add('hidden');
                }
            });

            // Reset all icons to collapsed state
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                icon.classList.remove('fa-minus-square');
                icon.classList.add('fa-plus-square');
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
                alert('الرجاء اختيار التاريخ من والى');
            }
        }
    </script>
@endsection
