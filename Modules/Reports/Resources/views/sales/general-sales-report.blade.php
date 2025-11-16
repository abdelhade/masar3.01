@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('reports.general_sales_report') }}</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="from_date" class="form-label">{{ __('reports.from_date') }}</label>
                            <input type="date" wire:model="from_date" class="form-control" id="from_date">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date" class="form-label">{{ __('reports.to_date') }}</label>
                            <input type="date" wire:model="to_date" class="form-control" id="to_date">
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">{{ __('reports.customer') }}</label>
                            <select wire:model="customer_id" class="form-select" id="customer_id">
                                <option value="">{{ __('reports.all_customers') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button wire:click="generateReport" class="btn btn-primary d-block">{{ __('reports.generate_report') }}</button>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_quantity') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalQuantity, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_sales') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalSales, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_discount') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalDiscount, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.net_sales') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalNetSales, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_invoices') }}</h6>
                                    <h4 class="card-text">{{ $totalInvoices }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.average_invoice_value') }}</h6>
                                    <h4 class="card-text">{{ number_format($averageInvoiceValue, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('reports.date') }}</th>
                                    <th>{{ __('reports.operation_number') }}</th>
                                    <th>{{ __('reports.customer') }}</th>
                                    <th class="text-end">{{ __('reports.quantity') }}</th>
                                    <th class="text-end">{{ __('reports.total_quantity') }}</th>
                                    <th class="text-end">{{ __('reports.total_sales') }}</th>
                                    <th class="text-end">{{ __('reports.total_discount') }}</th>
                                    <th class="text-end">{{ __('reports.net_sales') }}</th>
                                    <th>{{ __('reports.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->pro_date ? \Carbon\Carbon::parse($sale->pro_date)->format('Y-m-d') : '---' }}</td>
                                    <td>{{ $sale->pro_num ?? '---' }}</td>
                                    <td>{{ $sale->acc1Head->aname ?? '---' }}</td>
                                    <td class="text-end">{{ $sale->items_count ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($sale->total_quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->total_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->discount ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->net_sales, 2) }}</td>
                                    <td>
                                        @if($sale->status == 'completed')
                                            <span class="badge bg-success">{{ __('reports.completed') }}</span>
                                        @elseif($sale->status == 'pending')
                                            <span class="badge bg-warning">{{ __('reports.pending') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('reports.unspecified') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('reports.no_data_available') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($sales->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $sales->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 