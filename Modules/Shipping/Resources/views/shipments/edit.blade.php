@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Shipments'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Shipments'), 'url' => route('shipments.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Shipment') }}</h2>
                </div>
                <div class="card-body">
                    @can('edit Shipments')
                        <form action="{{ route('shipments.update', $shipment) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="tracking_number">{{ __('Tracking Number') }}</label>
                                    <input type="text" class="form-control" id="tracking_number" name="tracking_number"
                                        placeholder="{{ __('Enter tracking number') }}"
                                        value="{{ old('tracking_number', $shipment->tracking_number) }}">
                                    @error('tracking_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="shipping_company_id">{{ __('Shipping Company') }}</label>
                                    <select class="form-control" id="shipping_company_id" name="shipping_company_id">
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ old('shipping_company_id', $shipment->shipping_company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shipping_company_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="customer_name">{{ __('Customer Name') }}</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                                        placeholder="{{ __('Enter customer name') }}"
                                        value="{{ old('customer_name', $shipment->customer_name) }}">
                                    @error('customer_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="customer_address">{{ __('Customer Address') }}</label>
                                    <textarea class="form-control" id="customer_address" name="customer_address" placeholder="{{ __('Full address') }}">{{ old('customer_address', $shipment->customer_address) }}</textarea>
                                    @error('customer_address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="weight">{{ __('Weight (kg)') }}</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.01"
                                        placeholder="{{ __('Enter weight') }}" value="{{ old('weight', $shipment->weight) }}">
                                    @error('weight')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="status">{{ __('Status') }}</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="pending"
                                            {{ old('status', $shipment->status) == 'pending' ? 'selected' : '' }}>
                                            {{ __('Pending') }}
                                        </option>
                                        <option value="in_transit"
                                            {{ old('status', $shipment->status) == 'in_transit' ? 'selected' : '' }}>
                                            {{ __('In Transit') }}
                                        </option>
                                        <option value="delivered"
                                            {{ old('status', $shipment->status) == 'delivered' ? 'selected' : '' }}>
                                            {{ __('Delivered') }}
                                        </option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-start mt-4">
                                <button type="submit" class="btn btn-main me-2">
                                    <i class="las la-save"></i> {{ __('Save') }}
                                </button>
                                <a href="{{ route('shipments.index') }}" class="btn btn-danger">
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
