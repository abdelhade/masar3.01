@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Shipments'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Shipments')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Shipments')
                <a href="{{ route('shipments.create') }}" type="button" class="btn btn-main font-hold fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="shipments-table" filename="shipments-table"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="shipments-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Tracking Number') }}</th>
                                    <th>{{ __('Shipping Company') }}</th>
                                    <th>{{ __('Customer Name') }}</th>
                                    <th>{{ __('Address') }}</th>
                                    <th>{{ __('Weight') }}</th>
                                    <th>{{ __('Cost') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @canany(['edit Shipments', 'delete Shipments'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shipments as $shipment)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $shipment->tracking_number }}</td>
                                        <td>{{ $shipment->shippingCompany->name }}</td>
                                        <td>{{ $shipment->customer_name }}</td>
                                        <td>{{ $shipment->customer_address }}</td>
                                        <td>{{ $shipment->weight }} {{ __('kg') }}</td>
                                        <td>{{ number_format($shipment->total_cost, 2) }}</td>
                                        <td>
                                            @if($shipment->priority == 'express')
                                                <span class="badge bg-danger">{{ __('Express') }}</span>
                                            @elseif($shipment->priority == 'urgent')
                                                <span class="badge bg-warning">{{ __('Urgent') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Normal') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($shipment->status == 'pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif ($shipment->status == 'processing')
                                                <span class="badge bg-info">{{ __('Processing') }}</span>
                                            @elseif ($shipment->status == 'in_transit')
                                                <span class="badge bg-primary">{{ __('In Transit') }}</span>
                                            @elseif ($shipment->status == 'out_for_delivery')
                                                <span class="badge bg-primary">{{ __('Out for Delivery') }}</span>
                                            @elseif ($shipment->status == 'delivered')
                                                <span class="badge bg-success">{{ __('Delivered') }}</span>
                                            @elseif ($shipment->status == 'returned')
                                                <span class="badge bg-danger">{{ __('Returned') }}</span>
                                            @else
                                                <span class="badge bg-dark">{{ __('Cancelled') }}</span>
                                            @endif
                                        </td>
                                        @canany(['edit Shipments', 'delete Shipments'])
                                            <td>
                                                <a class="btn btn-primary btn-icon-square-sm" 
                                                    href="{{ route('shipments.show', $shipment->id) }}" 
                                                    title="{{ __('View Details') }}">
                                                    <i class="las la-eye"></i>
                                                </a>
                                                @can('edit Shipments')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('shipments.edit', $shipment) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Shipments')
                                                    <form action="{{ route('shipments.destroy', $shipment) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this shipment?') }}');">
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
                                        <td colspan="10" class="text-center">
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

    {{ $shipments->links() }}
@endsection
