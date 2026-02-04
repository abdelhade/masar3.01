@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="mb-0">{{ __('Delayed Orders List') }}</h4>
                <p class="text-muted small">
                    {{ __('Purchase orders that exceeded expected delivery date and not yet received') }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        @if ($delayedOrders->isEmpty())
                            <p class="mb-0 p-4 text-muted">
                                {{ __('No delayed purchase orders found. Ensure "Expected Delivery Date" is entered in purchase orders and quotations') }}
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Order Number') }}</th>
                                            <th>{{ __('Supplier') }}</th>
                                            <th>{{ __('Expected Delivery Date') }}</th>
                                            <th>{{ __('Days Late') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($delayedOrders as $order)
                                            <tr>
                                                <td>{{ $order->pro_id ?? $order->id }}</td>
                                                <td>{{ $order->acc1Head->aname ?? '—' }}</td>
                                                <td>
                                                    {{ $order->expected_delivery_date
                                                        ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('Y-m-d')
                                                        : '—' }}
                                                </td>
                                                <td>
                                                    {{ $order->expected_delivery_date
                                                        ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($order->expected_delivery_date)->startOfDay(), false)
                                                        : '—' }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('invoice.view', $order->id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        {{ __('View Order') }}
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

        <div class="row mt-2">
            <div class="col-12">
                <a href="{{ route('reports.purchasing.dashboard') }}" class="btn btn-secondary">
                    {{ __('Back to Purchasing Dashboard') }}
                </a>
            </div>
        </div>
    </div>
@endsection
