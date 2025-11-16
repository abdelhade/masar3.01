@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('styles')
    <style>
        .card-head {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .stats-card {
            border-right: 4px solid;
        }

        .stats-card.primary {
            border-color: #007bff;
        }

        .stats-card.success {
            border-color: #28a745;
        }

        .stats-card.warning {
            border-color: #ffc107;
        }

        .stats-card.info {
            border-color: #17a2b8;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-head">
                <h2>تقرير المبيعات حسب المندوب</h2>
            </div>
            <div class="card-body">
                <!-- فلاتر البحث -->
                <form method="GET" action="{{ route('reports.sales.representative') }}">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="representative_id">المندوب:</label>
                            <select name="representative_id" id="representative_id" class="form-control">
                                <option value="">-- جميع المندوبين --</option>
                                @foreach ($representatives as $rep)
                                    <option value="{{ $rep->id }}"
                                        {{ request('representative_id') == $rep->id ? 'selected' : '' }}>
                                        {{ $rep->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="from_date">من تاريخ:</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
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
                            <a href="{{ route('reports.sales.representative') }}" class="btn btn-secondary form-control">
                                <i class="fas fa-redo"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>

                <!-- الجدول -->
                <div class="table-responsive">
                    <table id="salesRepTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>كود المندوب</th>
                                <th>اسم المندوب</th>
                                <th>عدد الفواتير</th>
                                <th>إجمالي المبيعات</th>
                                <th>إجمالي الخصم</th>
                                <th>صافي المبيعات</th>
                                <th>متوسط الفاتورة</th>
                                <th>% من الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salesByRep as $index => $rep)
                                @php
                                    $averageInvoice =
                                        $rep->invoices_count > 0 ? $rep->net_sales / $rep->invoices_count : 0;
                                    $percentage = $grandNetSales > 0 ? ($rep->net_sales / $grandNetSales) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $salesByRep->firstItem() + $index }}</td>
                                    <td>{{ $rep->emp_id }}</td>
                                    <td>
                                        <strong>{{ $rep->representative->name ?? 'غير محدد' }}</strong>
                                    </td>
                                    <td class="text-end">{{ number_format($rep->invoices_count, 0) }}</td>
                                    <td class="text-end">{{ number_format($rep->total_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($rep->total_discount, 2) }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($rep->net_sales, 2) }}</strong>
                                    </td>
                                    <td class="text-end">{{ number_format($averageInvoice, 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-info">{{ number_format($percentage, 2) }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3" class="text-center">الإجمالي</th>
                                <th class="text-end">{{ number_format($totalInvoices, 0) }}</th>
                                <th class="text-end">{{ number_format($grandTotalSales, 2) }}</th>
                                <th class="text-end">{{ number_format($grandTotalDiscount, 2) }}</th>
                                <th class="text-end">{{ number_format($grandNetSales, 2) }}</th>
                                <th class="text-end">{{ number_format($averageInvoiceValue, 2) }}</th>
                                <th class="text-end">100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($salesByRep->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $salesByRep->appends(request()->query())->links() }}
                    </div>
                @endif

                <!-- ملخص التقرير -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="alert alert-info stats-card info">
                            <strong>إجمالي المندوبين:</strong><br>
                            <h3>{{ $totalReps }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success stats-card success">
                            <strong>أعلى مندوب مبيعاً:</strong><br>
                            <h5>{{ $topRepName }}</h5>
                            <small>{{ number_format($topRepSales, 2) }} جنيه</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning stats-card warning">
                            <strong>متوسط المبيعات للمندوب:</strong><br>
                            <h4>{{ number_format($averageSalesPerRep, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary stats-card primary">
                            <strong>متوسط الفواتير للمندوب:</strong><br>
                            <h4>{{ number_format($averageInvoicesPerRep, 1) }}</h4>
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
            $('#salesRepTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json',
                    search: "بحث:",
                    searchPlaceholder: "ابحث في التقرير..."
                },
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [{
                        extend: 'colvis',
                        text: 'إظهار/إخفاء الأعمدة',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> طباعة',
                        className: 'btn btn-info btn-sm',
                        title: 'تقرير المبيعات حسب المندوب',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'تقرير المبيعات حسب المندوب',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'تقرير المبيعات حسب المندوب',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> نسخ',
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
                    [6, 'desc']
                ], // ترتيب حسب صافي المبيعات
                columnDefs: [{
                        targets: [3, 4, 5, 6, 7, 8],
                        className: 'text-end'
                    },
                    {
                        targets: 0,
                        orderable: false
                    }
                ],
                responsive: true,
                footerCallback: function(row, data, start, end, display) {
                    // يمكنك إضافة حسابات إضافية هنا إذا لزم الأمر
                }
            });
        });
    </script>
@endsection
