@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        .table-positive {
            background-color: #d4edda !important;
        }

        .table-negative {
            background-color: #f8d7da !important;
        }

        .card-head {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-head">
                <h2>تقرير المشتريات أصناف</h2>
            </div>
            <div class="card-body">
                <!-- فلاتر البحث -->
                <form method="GET" action="{{ route('reports.purchases.items') }}">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="from_date">من تاريخ:</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date">إلى تاريخ:</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">
                                <i class="fas fa-filter"></i> فلتر
                            </button>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <a href="{{ route('reports.purchases.items') }}" class="btn btn-secondary form-control">
                                <i class="fas fa-redo"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>

                <!-- الجدول -->
                <div class="table-responsive">
                    <table id="purchasesTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الكود</th>
                                <th>اسم الصنف</th>
                                <th>ك المشتريات</th>
                                <th>ق المشتريات</th>
                                <th>متوسط الشراء</th>
                                <th>س الشراء</th>
                                <th>س ش متوسط</th>
                                <th>الربح</th>
                                <th>% الربح/ المشتريات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchasesItems as $index => $item)
                                @php
                                    // حساب متوسط السعر
                                    $averagePrice =
                                        $item->total_quantity > 0 ? $item->total_purchases / $item->total_quantity : 0;

                                    // تحديد اللون بناءً على متوسط السعر
                                    $rowClass = '';
                                    if ($averagePrice > 30) {
                                        $rowClass = 'table-positive';
                                    } elseif ($averagePrice < 30 && $averagePrice > 0) {
                                        $rowClass = 'table-negative';
                                    }

                                    // حساب نسبة المشتريات
                                    $percentage =
                                        $totalPurchases > 0 ? ($item->total_purchases / $totalPurchases) * 100 : 0;
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $purchasesItems->firstItem() + $index }}</td>
                                    <td>{{ $item->item->code ?? $item->item_id }}</td>
                                    <td>{{ $item->item->name ?? '---' }}</td>
                                    <td class="text-end">{{ number_format($item->invoices_count, 0) }}</td>
                                    <td class="text-end">{{ number_format($item->total_purchases, 2) }}</td>
                                    <td class="text-end">{{ number_format($averagePrice, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->total_quantity, 0) }}</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">0</td>
                                    <td class="text-end">{{ number_format($percentage, 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($purchasesItems->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $purchasesItems->appends(request()->query())->links() }}
                    </div>
                @endif

                <!-- ملخص التقرير -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>إجمالي الأصناف:</strong> {{ $totalItems }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>أعلى صنف مشترى:</strong> {{ $topPurchasedItem ?? '---' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>متوسط الكمية:</strong> {{ number_format($averageQuantityPerItem, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <strong>إجمالي المشتريات:</strong> {{ number_format($totalPurchases, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#purchasesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json',
                    search: "Search:",
                    searchPlaceholder: "ابحث في التقرير..."
                },
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [{
                        extend: 'colvis',
                        text: 'Column visibility',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'copy',
                        text: 'Copy',
                        className: 'btn btn-warning btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                pageLength: 25,
                paging: false, // نستخدم pagination من Laravel
                ordering: true,
                order: [
                    [4, 'desc']
                ], // ترتيب حسب قيمة المشتريات
                columnDefs: [{
                        targets: [3, 4, 5, 6, 7, 8, 9],
                        className: 'text-end'
                    },
                    {
                        targets: 0,
                        orderable: false
                    }
                ],
                responsive: true
            });
        });
    </script>
@endsection
