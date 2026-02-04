@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="mb-0 fw-bold">{{ __('Purchasing & Suppliers Dashboard') }}</h4>
                <p class="text-muted small">
                    {{ __('Supplier evaluation, delayed orders, average purchase prices, top 5 suppliers by delivery time') }}
                </p>
            </div>
        </div>

        {{-- Delayed Orders --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-warning shadow-sm">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <span class="fw-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ __('Delayed Orders List') }}
                        </span>
                        <a href="{{ route('reports.purchasing.delayed-orders') }}" class="btn btn-sm btn-dark">
                            <i class="fas fa-list me-1"></i>{{ __('Show All') }}
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if ($delayedOrders->isEmpty())
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-75"></i>
                                <p class="mb-0 text-muted fw-semibold">{{ __('No delayed purchase orders') }}</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-warning">
                                        <tr>
                                            <th class="fw-bold">{{ __('Order Number') }}</th>
                                            <th class="fw-bold">{{ __('Supplier') }}</th>
                                            <th class="fw-bold">{{ __('Expected Delivery') }}</th>
                                            <th class="fw-bold">{{ __('Days Late') }}</th>
                                            <th class="fw-bold text-center">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($delayedOrders->take(10) as $order)
                                            @php
                                                $daysLate = $order->expected_delivery_date
                                                    ? now()
                                                        ->startOfDay()
                                                        ->diffInDays(
                                                            \Carbon\Carbon::parse(
                                                                $order->expected_delivery_date,
                                                            )->startOfDay(),
                                                            false,
                                                        )
                                                    : 0;
                                            @endphp
                                            <tr class="table-warning">
                                                <td class="fw-semibold">
                                                    <span
                                                        class="badge bg-warning text-dark fs-6">{{ $order->pro_id ?? $order->id }}</span>
                                                </td>
                                                <td class="fw-semibold">
                                                    {{ $order->acc1Head->aname ?? '—' }}
                                                </td>
                                                <td class="fw-bold">
                                                    {{ $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('Y-m-d') : '—' }}
                                                </td>
                                                <td class="fw-bold text-danger">
                                                    <i class="fas fa-clock me-1"></i>{{ $daysLate }}
                                                    {{ __('days') }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('invoice.view', $order->id) }}"
                                                        class="btn btn-xs btn-outline-primary"
                                                        title="{{ __('View Details') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Top 5 Suppliers On-Time (Last 6 Months) --}}
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-gradient-success text-white">
                        <i class="fas fa-trophy me-2"></i>
                        <strong>{{ __('Top 5 On-Time Suppliers') }} ({{ __('Last 6 Months') }})</strong>
                    </div>
                    <div class="card-body">
                        @if ($topSuppliersOnTime->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">
                                    {{ __('Insufficient data (invoices linked to POs with expected delivery dates required)') }}
                                </p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($topSuppliersOnTime as $idx => $s)
                                    <div
                                        class="list-group-item list-group-item-action px-3 py-2 {{ $idx < 3 ? 'bg-light' : '' }}">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-success fs-6">{{ $idx + 1 }}</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold">{{ $s->supplier_name }}</h6>
                                                    <small class="text-muted">{{ $s->total_deliveries }}
                                                        {{ __('deliveries') }}</small>
                                                </div>
                                            </div>
                                            <span class="badge fs-6 bg-success shadow-sm">
                                                {{ $s->on_time_rate }}%
                                                <small
                                                    class="d-block">{{ $s->on_time_deliveries }}/{{ $s->total_deliveries }}</small>
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Average Purchase Prices per Product (Last 6 Months) --}}
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-gradient-info text-white">
                        <i class="fas fa-chart-line me-2"></i>
                        <strong>{{ __('Average Purchase Prices') }} ({{ __('Last 6 Months') }})</strong>
                    </div>
                    <div class="card-body p-0">
                        @if ($averagePricePerProduct->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-shopping-cart fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0 fw-semibold">{{ __('No purchases in period') }}</p>
                            </div>
                        @else
                            <div class="table-responsive" style="max-height: 350px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="fw-bold">{{ __('Item') }}</th>
                                            <th class="fw-bold text-end">{{ __('Avg Price') }}</th>
                                            <th class="fw-bold text-end">{{ __('Invoices') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider">
                                        @foreach ($averagePricePerProduct->take(15) as $row)
                                            <tr>
                                                <td class="fw-semibold">{{ Str::limit($row->item_name, 25) }}</td>
                                                <td class="text-end fw-bold text-primary">
                                                    {{ number_format($row->average_price, 2) }}
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-info">{{ $row->invoices_count }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 bg-light small text-muted border-top">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('Showing first 15 items. Use Items Purchases Report with 6-month filter for details') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0 fw-bold">
                            <i class="fas fa-link me-2 text-primary"></i>{{ __('Quick Links') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="btn-group-vertical btn-group-lg d-md-none flex-wrap">
                            <a href="{{ route('reports.general-purchases-report') }}" class="btn btn-outline-primary mb-2">
                                <i class="fas fa-file-invoice-dollar me-2"></i>{{ __('Purchases Report') }}
                            </a>
                            <a href="{{ route('reports.general-suppliers-total-report') }}"
                                class="btn btn-outline-primary mb-2">
                                <i class="fas fa-chart-pie me-2"></i>{{ __('Suppliers Totals Report') }}
                            </a>
                            <a href="{{ route('reports.general-purchases-items-report') }}"
                                class="btn btn-outline-primary mb-2">
                                <i class="fas fa-boxes me-2"></i>{{ __('Items Purchases Report') }}
                            </a>
                            @if (function_exists('route') && \Route::has('quality.suppliers.index'))
                                <a href="{{ route('quality.suppliers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-star me-2"></i>{{ __('Supplier Quality Evaluation') }}
                                </a>
                            @endif
                        </div>
                        <div class="d-none d-md-flex gap-2 flex-wrap">
                            <a href="{{ route('reports.general-purchases-report') }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-invoice-dollar me-2"></i>{{ __('Purchases Report') }}
                            </a>
                            <a href="{{ route('reports.general-suppliers-total-report') }}"
                                class="btn btn-outline-primary">
                                <i class="fas fa-chart-pie me-2"></i>{{ __('Suppliers Totals Report') }}
                            </a>
                            <a href="{{ route('reports.general-purchases-items-report') }}"
                                class="btn btn-outline-primary">
                                <i class="fas fa-boxes me-2"></i>{{ __('Items Purchases Report') }}
                            </a>
                            @if (function_exists('route') && \Route::has('quality.suppliers.index'))
                                <a href="{{ route('quality.suppliers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-star me-2"></i>{{ __('Supplier Quality') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
