@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('styles')
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
                <h2>{{ __('Sales Items Report') }}</h2>
            </div>
            <div class="card-body">
                <!-- البحث فلاتر -->
                <form method="GET" action="{{ route('reports.sales.items') }}">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="from_date">{{ __('From Date') }}:</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ $fromDate ?? '' }}">
                        </div>
                        <div class="col-md-3">
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
                            <a href="{{ route('reports.sales.items') }}" class="btn btn-secondary form-control">
                                <i class="fas fa-redo"></i> {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>

                <!-- الجدول -->
                <div class="table-responsive">
                    <table id="salesTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Item Name') }}</th>
                                <th class="text-end">{{ __('Invoices Count') }}</th>
                                <th class="text-end">{{ __('Total Quantity') }}</th>
                                <th class="text-end">{{ __('Average Selling Price') }}</th>
                                <th class="text-end">{{ __('Total Cost') }}</th>
                                <th class="text-end">{{ __('Profit') }}</th>
                                <th class="text-end">{{ __('Profit Margin %') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesItems as $index => $item)
                                @php
                                    $averagePrice =
                                        $item->total_quantity > 0 ? $item->total_sales / $item->total_quantity : 0;
                                    $totalCost = ($item->item->average_cost ?? 0) * ($item->total_quantity ?? 0);
                                    $profit = $item->total_sales - $totalCost;
                                    $profitMargin = $item->total_sales > 0 ? ($profit / $item->total_sales) * 100 : 0;

                                    $rowClass = '';
                                    if ($profitMargin > 30) {
                                        $rowClass = 'table-success';
                                    } elseif ($profitMargin < 10 && $profitMargin >= 0) {
                                        $rowClass = 'table-warning';
                                    } elseif ($profitMargin < 0) {
                                        $rowClass = 'table-danger';
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $salesItems->firstItem() + $index }}</td>
                                    <td>{{ $item->item->code ?? $item->item_id }}</td>
                                    <td>{{ $item->item->name ?? '---' }}</td>
                                    <td class="text-end fw-bold">
                                        <span class="badge bg-info">{{ number_format($item->invoices_count, 0) }}</span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($item->total_quantity ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">
                                        {{ number_format($averagePrice, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-secondary">
                                        {{ number_format($totalCost, 2) }}
                                    </td>
                                    <td class="text-end fw-bold {{ $profit >= 0 ? 'text-success' : 'text-danger' }} fs-6">
                                        {{ number_format($profit, 2) }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        <span
                                            class="badge {{ $profitMargin >= 20 ? 'bg-success' : ($profitMargin >= 10 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ number_format($profitMargin, 2) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">{{ __('No sales data available for selected period') }}
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if (isset($salesItems) && $salesItems->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $salesItems->appends(request()->query())->links() }}
                    </div>
                @endif

                <!-- ملخص التقرير -->
                @if (isset($totalItems))
                    <div class="row mt-4 g-3">
                        <div class="col-md-3">
                            <div class="alert alert-info shadow-sm h-100">
                                <strong>{{ __('Total Items') }}:</strong> {{ $totalItems }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success shadow-sm h-100">
                                <strong>{{ __('Top Selling Item') }}:</strong> {{ $topSellingItem ?? '---' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning shadow-sm h-100">
                                <strong>{{ __('Average Quantity per Item') }}:</strong>
                                {{ number_format($averageQuantityPerItem ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-primary shadow-sm h-100">
                                <strong>{{ __('Total Sales') }}:</strong> {{ number_format($totalSales ?? 0, 2) }}
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
            $('#salesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json',
                    search: "Search:",
                    searchPlaceholder: "ابحث في التقرير..."
                },
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [{
                        extend: 'colvis',
                        text: __('Column Visibility'),
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'print',
                        text: __('Print'),
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: __('{{ __('PDF') }}'),
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excel',
                        text: __('{{ __('Excel') }}'),
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
                ], // ترتيب حسب قيمة المبيعات
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
