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
                <h2>{{ __('Purchases Items Report') }}</h2>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" action="{{ route('reports.purchases.items') }}">
                    <div class="row mb-4 g-3">
                        <div class="col-md-3">
                            <label for="from_date" class="form-label fw-bold">{{ __('From Date') }}:</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date" class="form-label fw-bold">{{ __('To Date') }}:</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 h-100">
                                <i class="fas fa-filter me-2"></i>{{ __('Filter') }}
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('reports.purchases.items') }}" class="btn btn-outline-secondary w-100 h-100">
                                <i class="fas fa-redo me-2"></i>{{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <div class="card bg-info text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Items') }}</h6>
                                <h4 class="fw-bold mb-0">{{ $totalItems ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Purchases') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($totalPurchases ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Average Purchase Price') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($averagePrice ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-percentage fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Top Item %') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($topItemPercentage ?? 0, 1) }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table id="purchasesTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">{{ __('Code') }}</th>
                                <th>{{ __('Item Name') }}</th>
                                <th class="text-center">{{ __('Invoices Count') }}</th>
                                <th class="text-end fw-bold">{{ __('Total Purchases') }}</th>
                                <th class="text-end fw-bold">{{ __('Average Price') }}</th>
                                <th class="text-end fw-bold">{{ __('Total Quantity') }}</th>
                                <th class="text-end fw-bold">{{ __('Sales Quantity') }}</th>
                                <th class="text-end fw-bold text-success">{{ __('Profit') }}</th>
                                <th class="text-end fw-bold text-primary">{{ __('Profit %') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchasesItems as $index => $item)
                                @php
                                    $averagePrice =
                                        $item->total_quantity > 0 ? $item->total_purchases / $item->total_quantity : 0;
                                    $percentage =
                                        $totalPurchases > 0 ? ($item->total_purchases / $totalPurchases) * 100 : 0;
                                    $rowClass =
                                        $averagePrice > 30
                                            ? 'table-success'
                                            : ($averagePrice < 15
                                                ? 'table-danger'
                                                : '');
                                    $profitPercentage =
                                        $item->sales_quantity > 0
                                            ? (($item->total_sales ?? 0 - $item->total_purchases) /
                                                    $item->total_purchases) *
                                                100
                                            : 0;
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="text-center fw-bold">{{ $purchasesItems->firstItem() + $index }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info fs-6">{{ $item->item->code ?? $item->item_id }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="d-block">{{ $item->item->name ?? '---' }}</strong>
                                            <small class="text-muted">{{ $item->item->barcode ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-secondary">{{ number_format($item->invoices_count ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-primary fs-6">
                                        {{ number_format($item->total_purchases ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold bg-light rounded px-2 py-1">
                                        {{ number_format($averagePrice, 2) }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($item->total_quantity ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-info">
                                        {{ number_format($item->sales_quantity ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        {{ number_format($item->profit ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span
                                            class="badge {{ $profitPercentage > 20 ? 'bg-success' : ($profitPercentage < 0 ? 'bg-danger' : 'bg-warning') }}">
                                            {{ number_format($profitPercentage, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-75"></i>
                                            {{ __('No purchase items data available') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="4" class="text-end fw-bold fs-5">{{ __('Grand Total') }}</th>
                                <th class="text-end fw-bold text-primary fs-5">{{ number_format($totalPurchases ?? 0, 2) }}
                                </th>
                                <th class="text-end fw-bold fs-5">{{ number_format($averagePrice ?? 0, 2) }}</th>
                                <th class="text-end fw-bold fs-5">{{ number_format($totalQuantity ?? 0, 2) }}</th>
                                <th class="text-end fw-bold fs-5">{{ number_format($totalSalesQuantity ?? 0, 2) }}</th>
                                <th class="text-end fw-bold text-success fs-5">{{ number_format($totalProfit ?? 0, 2) }}
                                </th>
                                <th class="text-end fw-bold fs-5">{{ number_format($averageProfitPercentage ?? 0, 1) }}%
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($purchasesItems->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $purchasesItems->appends(request()->query())->links() }}
                    </div>
                @endif

                <!-- Analytics Summary -->
                @if (isset($purchasesItems) && $purchasesItems->count() > 0)
                    <div class="row mt-4 g-3">
                        <div class="col-md-3">
                            <div class="alert alert-info shadow-sm">
                                <i class="fas fa-boxes fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Total Items') }}:</strong> {{ $totalItems ?? 0 }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success shadow-sm">
                                <i class="fas fa-trophy fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Top Purchased Item') }}:</strong> {{ $topPurchasedItem ?? '---' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning shadow-sm">
                                <i class="fas fa-calculator fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Average Quantity per Item') }}:</strong>
                                {{ number_format($averageQuantityPerItem ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-primary shadow-sm">
                                <i class="fas fa-shopping-cart fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Total Purchases Value') }}:</strong>
                                {{ number_format($totalPurchases ?? 0, 2) }}
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
            $('#purchasesTable').DataTable({
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
