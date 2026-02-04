@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title fw-bold">{{ __('General Purchases Report') }}</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-4 g-3">
                            <div class="col-md-3">
                                <label for="from_date" class="form-label fw-bold">{{ __('From Date') }}</label>
                                <input type="date" wire:model.live="fromDate" class="form-control" id="from_date">
                            </div>
                            <div class="col-md-3">
                                <label for="to_date" class="form-label fw-bold">{{ __('To Date') }}</label>
                                <input type="date" wire:model.live="toDate" class="form-control" id="to_date">
                            </div>
                            <div class="col-md-3">
                                <label for="supplier_id" class="form-label fw-bold">{{ __('Supplier') }}</label>
                                <select wire:model.live="supplierId" class="form-select" id="supplier_id">
                                    <option value="">{{ __('All Suppliers') }}</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->aname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button wire:click="generateReport" class="btn btn-primary w-100">
                                    <i class="fas fa-chart-line me-2"></i>{{ __('Generate Report') }}
                                </button>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row mb-4 g-3">
                            <div class="col-md-2">
                                <div class="card bg-primary text-white shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-boxes fa-2x mb-2 opacity-75"></i>
                                        <h6 class="card-title fw-bold">{{ __('Total Quantity') }}</h6>
                                        <h4 class="fw-bold mb-0">{{ number_format($totalQuantity ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-success text-white shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-shopping-cart fa-2x mb-2 opacity-75"></i>
                                        <h6 class="card-title fw-bold">{{ __('Total Purchases') }}</h6>
                                        <h4 class="fw-bold mb-0">{{ number_format($totalPurchases ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-warning text-white shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-tags fa-2x mb-2 opacity-75"></i>
                                        <h6 class="card-title fw-bold">{{ __('Total Discount') }}</h6>
                                        <h4 class="fw-bold mb-0">{{ number_format($totalDiscount ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-info text-white shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calculator fa-2x mb-2 opacity-75"></i>
                                        <h6 class="card-title fw-bold">{{ __('Net Purchases') }}</h6>
                                        <h4 class="fw-bold mb-0">{{ number_format($totalNetPurchases ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-secondary text-white shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-invoice fa-2x mb-2 opacity-75"></i>
                                        <h6 class="card-title fw-bold">{{ __('Total Invoices') }}</h6>
                                        <h4 class="fw-bold mb-0">{{ $totalInvoices ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-dark text-white shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-bar fa-2x mb-2 opacity-75"></i>
                                        <h6 class="card-title fw-bold">{{ __('Average Invoice Value') }}</h6>
                                        <h4 class="fw-bold mb-0">{{ number_format($averageInvoiceValue ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">{{ __('Date') }}</th>
                                        <th class="text-center">{{ __('Invoice Number') }}</th>
                                        <th>{{ __('Supplier') }}</th>
                                        <th class="text-end fw-bold">{{ __('Items Count') }}</th>
                                        <th class="text-end fw-bold">{{ __('Total Quantity') }}</th>
                                        <th class="text-end fw-bold text-primary">{{ __('Total Purchases') }}</th>
                                        <th class="text-end fw-bold text-warning">{{ __('Total Discount') }}</th>
                                        <th class="text-end fw-bold text-success">{{ __('Net Purchases') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-center">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchases ?? collect() as $purchase)
                                        <tr>
                                            <td class="text-center fw-semibold">
                                                {{ $purchase->pro_date ? \Carbon\Carbon::parse($purchase->pro_date)->format('Y-m-d') : '---' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $purchase->pro_num ?? '---' }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $purchase->acc1Head?->aname ?? '---' }}</strong>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-secondary">{{ $purchase->items_count ?? 0 }}</span>
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ number_format($purchase->total_quantity ?? 0, 2) }}</td>
                                            <td class="text-end fw-bold text-primary fs-6">
                                                {{ number_format($purchase->total_purchases ?? 0, 2) }}
                                            </td>
                                            <td class="text-end fw-bold text-warning">
                                                {{ number_format($purchase->discount ?? 0, 2) }}
                                            </td>
                                            <td class="text-end fw-bold text-success fs-6">
                                                {{ number_format($purchase->net_purchases ?? 0, 2) }}
                                            </td>
                                            <td class="text-center">
                                                @if ($purchase->status == 'completed')
                                                    <span class="badge bg-success fs-6">{{ __('Completed') }}</span>
                                                @elseif($purchase->status == 'pending')
                                                    <span class="badge bg-warning fs-6">{{ __('Pending') }}</span>
                                                @elseif($purchase->status == 'cancelled')
                                                    <span class="badge bg-danger fs-6">{{ __('Cancelled') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unspecified') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="#" class="btn btn-outline-info"
                                                        title="{{ __('View') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-outline-primary"
                                                        title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-outline-success" title="{{ __('Print') }}">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                                    {{ __('No Data Available') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-primary">
                                    <tr>
                                        <th colspan="4" class="text-end fw-bold fs-5">{{ __('Grand Total') }}</th>
                                        <th class="text-end fw-bold fs-5">{{ number_format($totalQuantity ?? 0, 2) }}</th>
                                        <th class="text-end fw-bold text-primary fs-5">
                                            {{ number_format($totalPurchases ?? 0, 2) }}</th>
                                        <th class="text-end fw-bold text-warning fs-5">
                                            {{ number_format($totalDiscount ?? 0, 2) }}</th>
                                        <th class="text-end fw-bold text-success fs-5">
                                            {{ number_format($totalNetPurchases ?? 0, 2) }}</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if (isset($purchases) && $purchases->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $purchases->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
