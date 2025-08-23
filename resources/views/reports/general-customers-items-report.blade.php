@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>{{ __('reports.customers_items_report') }}</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="from_date">{{ __('reports.from_date') }}:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">{{ __('reports.to_date') }}:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
                <div class="col-md-3">
                    <label for="customer_id">{{ __('reports.customer') }}:</label>
                    <select id="customer_id" class="form-control" wire:model="customerId">
                        <option value="">{{ __('reports.all_customers') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">{{ __('reports.generate_report') }}</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('reports.item_code') }}</th>
                            <th>{{ __('reports.item_name') }}</th>
                            <th>{{ __('reports.unit') }}</th>
                            <th class="text-end">{{ __('reports.quantity_sold') }}</th>
                            <th class="text-end">{{ __('reports.total_sales') }}</th>
                            <th class="text-end">{{ __('reports.average_price') }}</th>
                            <th class="text-end">{{ __('reports.invoices_count') }}</th>
                            <th class="text-end">{{ __('reports.sales_percentage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerItems as $item)
                        <tr>
                            <td>{{ $item->item_code ?? '---' }}</td>
                            <td>{{ $item->item_name ?? '---' }}</td>
                            <td>{{ $item->unit_name ?? '---' }}</td>
                            <td class="text-end">{{ number_format($item->total_quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->total_sales, 2) }}</td>
                            <td class="text-end">{{ number_format($item->average_price, 2) }}</td>
                            <td class="text-end">{{ $item->invoices_count }}</td>
                            <td class="text-end">{{ number_format($item->sales_percentage, 2) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('reports.no_data_available') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="3">{{ __('reports.total') }}</th>
                            <th class="text-end">{{ number_format($totalQuantity, 2) }}</th>
                            <th class="text-end">{{ number_format($totalSales, 2) }}</th>
                            <th class="text-end">{{ number_format($averagePrice, 2) }}</th>
                            <th class="text-end">{{ $totalInvoices }}</th>
                            <th class="text-end">100%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($customerItems->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $customerItems->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>{{ __('reports.total_items_sold') }}:</strong> {{ $totalItems }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>{{ __('reports.top_selling_item') }}:</strong> {{ $topSellingItem ?? '---' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>{{ __('reports.average_quantity_per_item') }}:</strong> {{ number_format($averageQuantityPerItem, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>{{ __('reports.total_sales') }}:</strong> {{ number_format($totalSales, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 