@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        .tree-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .tree-item {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            margin: 8px 0;
            background: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tree-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
        }

        .tree-item.basic-account {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .nested {
            margin-right: 25px;
            margin-top: 10px;
            border-right: 2px dashed #dee2e6;
            padding-right: 15px;
        }

        .hidden {
            display: none;
        }

        .toggle-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #007bff;
            border: none;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .toggle-icon:hover {
            background: #0056b3;
            transform: translateY(-50%) scale(1.1);
        }

        .no-toggle-icon {
            width: 28px;
            display: inline-block;
        }

        .account-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            flex: 1;
            padding-left: 40px;
        }

        .account-code {
            font-weight: bold;
            color: #495057;
            font-size: 0.95rem;
        }

        .account-name {
            color: #212529;
            font-size: 1.1rem;
        }

        .account-balance {
            color: #28a745;
            font-weight: 600;
            font-size: 0.9rem;
            margin-right: auto;
        }

        .account-balance.negative {
            color: #dc3545;
        }

        .children-count {
            background: #17a2b8;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .basic-badge {
            background: #007bff;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
        }

        .table-container table {
            font-size: 0.9rem;
        }

        .table-container table th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 10;
        }

        .search-box {
            margin-bottom: 15px;
        }

        .search-box input {
            border-radius: 20px;
            padding: 8px 15px;
        }

        .btn-edit-sm {
            padding: 2px 8px;
            font-size: 0.8rem;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .tree-item {
                page-break-inside: avoid;
                border: 1px solid #ddd;
            }

            .toggle-icon {
                display: none;
            }

            .col-lg-4 {
                display: none !important;
            }

            .col-lg-8 {
                width: 100% !important;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="card">
            <div class="card-head">
                <h1>شجرة الحسابات</h1>
            </div>
            <div class="card-body">
                <div class="mb-3 no-print">
                    <button type="button" id="expand-all" class="btn btn-primary btn-lg">
                        <i class="fas fa-expand-alt me-1"></i>{{ __('فتح الكل') }}
                    </button>
                    <button type="button" id="collapse-all" class="btn btn-secondary btn-lg">
                        <i class="fas fa-compress-alt me-1"></i>{{ __('طي الكل') }}
                    </button>
                    <button type="button" id="print-tree" class="btn btn-success btn-lg">
                        <i class="fas fa-print me-1"></i>{{ __('طباعة') }}
                    </button>
                </div>

                <div class="row">
                    <!-- Tree Section (2/3) -->
                    <div class="col-lg-8">
                        <div id="printed-area" class="tree-container">
                            <h5 class="mb-3"><i class="fas fa-sitemap me-2"></i>شجرة الحسابات</h5>
                            <ul>
                                @foreach ($accounts as $account)
                                    @include('reports::accounts-reports.partials.account-node', ['account' => $account])
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Table Section (1/3) -->
                    <div class="col-lg-4 no-print">
                        <div class="table-container">
                            <h5 class="mb-3"><i class="fas fa-list me-2"></i>قائمة الحسابات</h5>

                            <!-- Search Box -->
                            <div class="search-box">
                                <input type="text" id="searchInput" class="form-control" placeholder="ابحث عن حساب...">
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الكود</th>
                                            <th>اسم الحساب</th>
                                            <th>الرصيد</th>
                                            <th>عمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="accountsTableBody">
                                        @php
                                            function displayAccounts($accounts, $level = 0)
                                            {
                                                foreach ($accounts as $account) {
                                                    $balance = number_format($account->balance, 2);
                                                    $balanceClass =
                                                        $account->balance < 0 ? 'text-danger' : 'text-success';
                                                    $indent = str_repeat('—', $level);

                                                    echo '<tr>';
                                                    echo "<td><strong>{$account->code}</strong></td>";
                                                    echo "<td class='text-start'>{$indent} {$account->aname}</td>";
                                                    echo "<td class='{$balanceClass} fw-bold'>{$balance}</td>";
                                                    echo '<td>';

                                                    if ($account->editable == 1) {
                                                        echo "<button class='btn btn-warning btn-edit-sm' onclick='editAccount({$account->id})' title='تعديل'>";
                                                        echo "<i class='fas fa-edit'></i>";
                                                        echo '</button>';
                                                    }

                                                    echo '</td>';
                                                    echo '</tr>';

                                                    if ($account->children?->count() ?? 0) {
                                                        displayAccounts($account->children, $level + 1);
                                                    }
                                                }
                                            }
                                            displayAccounts($accounts);
                                        @endphp
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // إخفاء جميع العناصر المتداخلة عند تحميل الصفحة
            const nestedLists = document.querySelectorAll('.nested');
            const toggles = document.querySelectorAll('.toggle-icon');

            nestedLists.forEach(function(nested) {
                nested.classList.add('hidden');
            });

            toggles.forEach(function(toggle) {
                const icon = toggle.querySelector('i');
                icon.className = 'fas fa-plus';
            });

            // التعامل مع أزرار الفتح والإغلاق
            toggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const parent = this.closest('li');
                    const nested = parent.querySelector('.nested');
                    const icon = this.querySelector('i');

                    if (nested.classList.contains('hidden')) {
                        nested.classList.remove('hidden');
                        icon.className = 'fas fa-minus';
                    } else {
                        nested.classList.add('hidden');
                        icon.className = 'fas fa-plus';
                    }
                });
            });

            // زر فتح الكل
            const expandAllBtn = document.getElementById('expand-all');
            if (expandAllBtn) {
                expandAllBtn.addEventListener('click', function() {
                    nestedLists.forEach(function(nested) {
                        nested.classList.remove('hidden');
                    });
                    toggles.forEach(function(toggle) {
                        const icon = toggle.querySelector('i');
                        icon.className = 'fas fa-minus';
                    });
                });
            }

            // زر طي الكل
            const collapseAllBtn = document.getElementById('collapse-all');
            if (collapseAllBtn) {
                collapseAllBtn.addEventListener('click', function() {
                    nestedLists.forEach(function(nested) {
                        nested.classList.add('hidden');
                    });
                    toggles.forEach(function(toggle) {
                        const icon = toggle.querySelector('i');
                        icon.className = 'fas fa-plus';
                    });
                });
            }
        });

        // زر الطباعة
        document.getElementById('print-tree').addEventListener('click', function() {
            window.print();
        });

        // البحث في الجدول
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#accountsTableBody tr');

            tableRows.forEach(function(row) {
                const code = row.cells[0].textContent.toLowerCase();
                const name = row.cells[1].textContent.toLowerCase();

                if (code.includes(searchValue) || name.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        function editAccount(accountId) {
            window.location.href = `/accounts/${accountId}/edit`;
        }
    </script>
@endsection
