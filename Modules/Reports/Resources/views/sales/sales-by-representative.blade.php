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
                <h2>{{ __('Sales Report by Representative') }}</h2>
            </div>
            <div class="card-body">
                <!-- فلاتر البحث -->
                <form method="GET" action="{{ route('reports.sales.representative') }}">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="representative_id">{{ __('Representative') }}:</label>
                            <select name="representative_id" id="representative_id" class="form-control">
                                <option value="">{{ __('-- All Representatives --') }}</option>
                                @foreach ($representatives as $rep)
                                    <option value="{{ $rep->id }}"
                                        {{ request('representative_id') == $rep->id ? 'selected' : '' }}>
                                        {{ $rep->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="from_date">{{ __('From Date') }}:</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ $fromDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label for="to_date">{{ __('To Date') }}:</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                value="{{ $toDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">
                                <i class="fas fa-filter"></i> {{ __('Filter') }}
                            </button>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <a href="{{ route('reports.sales.representative') }}" class="btn btn-secondary form-control">
                                <i class="fas fa-redo"></i> {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>

                <!-- الجدول -->
                <div class="table-responsive">
                    <table id="salesRepTable" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Representative Code') }}</th>
                                <th>{{ __('Representative Name') }}</th>
                                <th class="text-end">{{ __('Invoices Count') }}</th>
                                <th class="text-end">{{ __('Total Sales') }}</th>
                                <th class="text-end">{{ __('Total Discount') }}</th>
                                <th class="text-end">{{ __('Net Sales') }}</th>
                                <th class="text-end">{{ __('Average Invoice') }}</th>
                                <th class="text-end">{{ __('Percentage of Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesByRep as $index => $rep)
                                @php
                                    $averageInvoice =
                                        $rep->invoices_count > 0 ? $rep->net_sales / $rep->invoices_count : 0;
                                    $percentage =
                                        isset($grandNetSales) && $grandNetSales > 0
                                            ? ($rep->net_sales / $grandNetSales) * 100
                                            : 0;
                                @endphp
                                <tr>
                                    <td>{{ $salesByRep->firstItem() + $index }}</td>
                                    <td>{{ $rep->emp_id ?? '---' }}</td>
                                    <td>
                                        <strong>{{ $rep->representative->name ?? __('Unspecified') }}</strong>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <span class="badge bg-info">{{ number_format($rep->invoices_count, 0) }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-primary">
                                        {{ number_format($rep->total_sales ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-warning">
                                        {{ number_format($rep->total_discount ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        {{ number_format($rep->net_sales ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($averageInvoice, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info">{{ number_format($percentage, 2) }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">{{ __('No sales data available for selected period') }}
                                        </p>
                                    </td>
                            @endforelse
                        </tbody>
                        @if (isset($grandNetSales))
                            <tfoot class="table-primary">
                                <tr>
                                    <th colspan="3" class="text-center fw-bold fs-5">{{ __('Grand Total') }}</th>
                                    <th class="text-end fw-bold fs-5">{{ number_format($totalInvoices ?? 0, 0) }}</th>
                                    <th class="text-end fw-bold text-primary fs-5">
                                        {{ number_format($grandTotalSales ?? 0, 2) }}</th>
                                    <th class="text-end fw-bold text-warning fs-5">
                                        {{ number_format($grandTotalDiscount ?? 0, 2) }}</th>
                                    <th class="text-end fw-bold text-success fs-5">{{ number_format($grandNetSales, 2) }}
                                    </th>
                                    <th class="text-end fw-bold fs-5">{{ number_format($averageInvoiceValue ?? 0, 2) }}
                                    </th>
                                    <th class="text-end fw-bold fs-5">100%</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Pagination -->
                @if (isset($salesByRep) && $salesByRep->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $salesByRep->appends(request()->query())->links() }}
                    </div>
                @endif

                <!-- ملخص التقرير -->
                @if (isset($totalReps))
                    <div class="row mt-4 g-3">
                        <div class="col-md-3">
                            <div class="alert alert-info shadow-sm h-100">
                                <strong>{{ __('Total Representatives') }}:</strong><br>
                                <h3 class="fw-bold text-info">{{ $totalReps }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success shadow-sm h-100">
                                <strong>{{ __('Top Selling Representative') }}:</strong><br>
                                <h5 class="fw-bold">{{ $topRepName ?? '---' }}</h5>
                                <small class="text-success">{{ number_format($topRepSales ?? 0, 2) }}
                                    {{ __('EGP') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning shadow-sm h-100">
                                <strong>{{ __('Average Sales per Representative') }}:</strong><br>
                                <h4 class="fw-bold">{{ number_format($averageSalesPerRep ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-primary shadow-sm h-100">
                                <strong>{{ __('Average Invoices per Representative') }}:</strong><br>
                                <h4 class="fw-bold">{{ number_format($averageInvoicesPerRep ?? 0, 1) }}</h4>
                            </div>
                        </div>
                    </div>
                @endif
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
