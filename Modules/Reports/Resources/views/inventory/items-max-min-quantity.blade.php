@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('reports.items_max_min_quantity'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('reports.items_max_min_quantity')]],
    ])

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fs-3 fw-bold text-dark">{{ __('reports.items_max_min_quantity') }}</h1>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-end fw-semibold text-uppercase small px-4 py-3">{{ __('reports.item_code') }}</th>

                                <th scope="col" class="text-end fw-semibold text-uppercase small px-4 py-3">{{ __('reports.item_name') }}</th>

                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">{{ __('reports.current_quantity') }}</th>

                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">{{ __('reports.min_quantity') }}</th>

                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">{{ __('reports.max_quantity') }}</th>

                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">{{ __('reports.required_compensation') }}</th>

                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">{{ __('reports.status') }}</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr
                                    class="@if ($item['status'] == 'below_min') table-danger @elseif ($item['status'] == 'above_max') table-primary @else table-success @endif">
                                    <td class="text-end px-4 py-3">{{ $item['code'] }}</td>
                                    <td class="text-end px-4 py-3">{{ $item['name'] }}</td>

                                    <td class="text-center px-4 py-3">
                                        <span
                                            class="fw-bold @if ($item['status'] == 'below_min') text-danger @elseif ($item['status'] == 'above_max') text-primary @else text-success @endif">
                                            {{ number_format($item['current_quantity'], 2) }}
                                        </span>
                                    </td>

                                    <td class="text-center px-4 py-3">{{ number_format($item['min_order_quantity'], 2) }}
                                    </td>
                                    <td class="text-center px-4 py-3">{{ number_format($item['max_order_quantity'], 2) }}
                                    </td>

                                    <td class="text-center px-4 py-3">
                                        @if ($item['required_compensation'] > 0)
                                            <span
                                                class="fw-bold @if ($item['status'] == 'below_min') text-danger @else text-primary @endif">
                                                {{ number_format($item['required_compensation'], 2) }}
                                                @if ($item['status'] == 'below_min')
                                                    <small class="d-block text-muted">({{ __('reports.low_stock') }})</small>
                                                @else
                                                    <small class="d-block text-muted">({{ __('reports.overstock') }})</small>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-success fw-bold">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center px-4 py-3">
                                        @if ($item['status'] == 'below_min')
                                            <span class="badge bg-danger rounded-pill">
                                                <i class="bi bi-arrow-down me-1"></i> {{ __('reports.below_min') }}
                                            </span>
                                        @elseif ($item['status'] == 'above_max')
                                            <span class="badge bg-primary rounded-pill">
                                                <i class="bi bi-arrow-up me-1"></i> {{ __('reports.above_max') }}
                                            </span>
                                        @else
                                            <span class="badge bg-success rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> {{ __('reports.within_limits') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $items->links() }}
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title">{{ __('reports.summary_report') }}</h6>
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="text-danger fw-bold fs-4">
                                    {{ collect($items)->where('status', 'below_min')->count() }}</div>
                                <small class="text-muted">{{ __('reports.below_min') }}</small>
                            </div>
                            <div class="col-3">
                                <div class="text-primary fw-bold fs-4">
                                    {{ collect($items)->where('status', 'above_max')->count() }}</div>
                                <small class="text-muted">{{ __('reports.above_max') }}</small>
                            </div>
                            <div class="col-3">
                                <div class="text-success fw-bold fs-4">
                                    {{ collect($items)->where('status', 'within_limits')->count() }}</div>
                                <small class="text-muted">{{ __('reports.within_limits') }}</small>
                            </div>

                            <div class="col-3">
                                <div class="text-success fw-bold fs-4">
                                    {{ count($items) }}
                                </div>
                                <small class="text-muted">{{ __('reports.total_items') }}</small>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table th,
        .table td {
            vertical-align: middle;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 9999px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f3f5;
        }

        .btn-group .btn {
            transition: all 0.2s ease;
        }

        .btn-group .btn:hover {
            transform: translateY(-1px);
        }

        /* تحسين ألوان الصفوف */
        .table-danger {
            background-color: rgba(220, 53, 69, 0.1) !important;
            border-color: rgba(220, 53, 69, 0.2) !important;
        }

        .table-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
            border-color: rgba(13, 110, 253, 0.2) !important;
        }

        .table-success {
            background-color: rgba(25, 135, 84, 0.1) !important;
            border-color: rgba(25, 135, 84, 0.2) !important;
        }

        .table-hover tbody tr:hover {
            --bs-table-accent-bg: rgba(0, 0, 0, 0.075);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection



