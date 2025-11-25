@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Orders'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Orders'), 'url' => route('orders.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Order') }}</h2>
                </div>
                <div class="card-body">
                    @can('edit Orders')
                        <form action="{{ route('orders.update', $order) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="order_number">{{ __('Order Number') }}</label>
                                    <input type="text" class="form-control" id="order_number" name="order_number"
                                        placeholder="{{ __('Enter order number') }}"
                                        value="{{ old('order_number', $order->order_number) }}">
                                    @error('order_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="driver_id">{{ __('Driver') }}</label>
                                    <select class="form-control" id="driver_id" name="driver_id">
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}"
                                                {{ old('driver_id', $order->driver_id) == $driver->id ? 'selected' : '' }}>
                                                {{ $driver->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('driver_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="shipment_id">{{ __('Shipment') }}</label>
                                    <select class="form-control" id="shipment_id" name="shipment_id">
                                        @foreach ($shipments as $shipment)
                                            <option value="{{ $shipment->id }}"
                                                {{ old('shipment_id', $order->shipment_id) == $shipment->id ? 'selected' : '' }}>
                                                {{ $shipment->tracking_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shipment_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="customer_name">{{ __('Customer Name') }}</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                                        placeholder="{{ __('Enter customer name') }}"
                                        value="{{ old('customer_name', $order->customer_name) }}">
                                    @error('customer_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="customer_address">{{ __('Customer Address') }}</label>
                                    <textarea class="form-control" id="customer_address" name="customer_address" placeholder="{{ __('Full address') }}">{{ old('customer_address', $order->customer_address) }}</textarea>
                                    @error('customer_address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="delivery_status">{{ __('Delivery Status') }}</label>
                                    <select class="form-control" id="delivery_status" name="delivery_status">
                                        <option value="pending"
                                            {{ old('delivery_status', $order->delivery_status) == 'pending' ? 'selected' : '' }}>
                                            {{ __('Pending') }}
                                        </option>
                                        <option value="assigned"
                                            {{ old('delivery_status', $order->delivery_status) == 'assigned' ? 'selected' : '' }}>
                                            {{ __('Assigned') }}
                                        </option>
                                        <option value="in_transit"
                                            {{ old('delivery_status', $order->delivery_status) == 'in_transit' ? 'selected' : '' }}>
                                            {{ __('In Transit') }}
                                        </option>
                                        <option value="delivered"
                                            {{ old('delivery_status', $order->delivery_status) == 'delivered' ? 'selected' : '' }}>
                                            {{ __('Delivered') }}
                                        </option>
                                    </select>
                                    @error('delivery_status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-start mt-4">
                                <button type="submit" class="btn btn-main me-2">
                                    <i class="las la-save"></i> {{ __('Save') }}
                                </button>
                                <a href="{{ route('orders.index') }}" class="btn btn-danger">
                                    <i class="las la-times"></i> {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
