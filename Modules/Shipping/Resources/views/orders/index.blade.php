@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Orders'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Orders')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Orders')
                <a href="{{ route('orders.create') }}" type="button" class="btn btn-main font-hold fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="orders-table" filename="orders-table"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="orders-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Order Number') }}</th>
                                    <th>{{ __('Driver') }}</th>
                                    <th>{{ __('Shipment Number') }}</th>
                                    <th>{{ __('Customer Name') }}</th>
                                    <th>{{ __('Address') }}</th>
                                    <th>{{ __('Delivery Status') }}</th>
                                    @canany(['edit Orders', 'delete Orders'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->driver->name }}</td>
                                        <td>{{ $order->shipment->tracking_number }}</td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->customer_address }}</td>
                                        <td>
                                            @if ($order->delivery_status == 'pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif ($order->delivery_status == 'assigned')
                                                <span class="badge bg-info">{{ __('Assigned') }}</span>
                                            @elseif ($order->delivery_status == 'in_transit')
                                                <span class="badge bg-primary">{{ __('In Transit') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Delivered') }}</span>
                                            @endif
                                        </td>
                                        @canany(['edit Orders', 'delete Orders'])
                                            <td>
                                                @if ($order->delivery_status == 'delivered' && !$order->rating)
                                                    <a class="btn btn-warning btn-icon-square-sm"
                                                        href="{{ route('orders.rate-driver', $order->id) }}"
                                                        title="{{ __('Rate Driver') }}">
                                                        <i class="las la-star"></i>
                                                    </a>
                                                @endif
                                                @can('edit Orders')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('orders.edit', $order) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Orders')
                                                    <form action="{{ route('orders.destroy', $order) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this order?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No data available') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{ $orders->links() }}
@endsection
