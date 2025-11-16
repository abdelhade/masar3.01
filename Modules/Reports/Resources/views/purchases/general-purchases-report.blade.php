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
                    <h4 class="card-title">{{ __('reports.general_purchases_report') }}</h4>
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
                            <label for="supplier_id" class="form-label">{{ __('reports.supplier') }}</label>
                            <select wire:model="supplier_id" class="form-select" id="supplier_id">
                                <option value="">{{ __('reports.all_suppliers') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->aname }}</option>
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
                                    <h6 class="card-title">{{ __('reports.total_purchases') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalPurchases, 2) }}</h4>
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
                                    <h6 class="card-title">{{ __('reports.net_purchases') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalNetPurchases, 2) }}</h4>
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
                                    <th>{{ __('reports.invoice_number') }}</th>
                                    <th>{{ __('reports.supplier') }}</th>
                                    <th class="text-end">{{ __('reports.items_count') }}</th>
                                    <th class="text-end">{{ __('reports.total_quantity') }}</th>
                                    <th class="text-end">{{ __('reports.total_purchases') }}</th>
                                    <th class="text-end">{{ __('reports.total_discount') }}</th>
                                    <th class="text-end">{{ __('reports.net_purchases') }}</th>
                                    <th>{{ __('reports.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->pro_date ? \Carbon\Carbon::parse($purchase->pro_date)->format('Y-m-d') : '---' }}</td>
                                    <td>{{ $purchase->pro_num ?? '---' }}</td>
                                    <td>{{ $purchase->acc1Head->aname ?? '---' }}</td>
                                    <td class="text-end">{{ $purchase->items_count ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($purchase->total_quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format($purchase->total_purchases, 2) }}</td>
                                    <td class="text-end">{{ number_format($purchase->discount ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($purchase->net_purchases, 2) }}</td>
                                    <td>
                                        @if($purchase->status == 'completed')
                                            <span class="badge bg-success">{{ __('reports.completed') }}</span>
                                        @elseif($purchase->status == 'pending')
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
                    @if($purchases->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $purchases->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 