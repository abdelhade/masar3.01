@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Daily Purchases Report') }}</h2>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <label for="from_date" class="form-label fw-bold">{{ __('From Date') }}:</label>
                        <input type="date" id="from_date" class="form-control" wire:model.live="fromDate">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label fw-bold">{{ __('To Date') }}:</label>
                        <input type="date" id="to_date" class="form-control" wire:model.live="toDate">
                    </div>
                    <div class="col-md-3">
                        <label for="supplier_id" class="form-label fw-bold">{{ __('Supplier') }}:</label>
                        <select id="supplier_id" class="form-select" wire:model.live="supplierId">
                            <option value="">{{ __('All Suppliers') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->aname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100 h-100" wire:click="generateReport">
                            <i class="fas fa-chart-bar me-2"></i>{{ __('Generate Report') }}
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <div class="card bg-info text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-file-invoice fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Invoices') }}</h6>
                                <h4 class="fw-bold mb-0">{{ $totalInvoices ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Quantity') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($totalQuantity ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Purchases') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($totalPurchases ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-calculator fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Net Purchases') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($totalNetPurchases ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <th class="text-end fw-bold text-warning">{{ __('Discount') }}</th>
                                <th class="text-end fw-bold text-success">{{ __('Net Purchases') }}</th>
                                <th class="text-center">{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $purchase)
                                <tr>
                                    <td class="text-center fw-semibold">
                                        {{ $purchase->pro_date ? \Carbon\Carbon::parse($purchase->pro_date)->format('Y-m-d') : '---' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $purchase->pro_num ?? '---' }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $purchase->acc1Head->aname ?? __('Unspecified') }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary">{{ $purchase->items_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($purchase->total_quantity ?? 0, 2) }}
                                    </td>
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
                                            <a href="#" class="btn btn-outline-info" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-primary" title="{{ __('Edit') }}">
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
                                    <td colspan="10" class="text-center py-5">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-75"></i>
                                            {{ __('No purchases data available') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="4" class="text-end fw-bold">{{ __('Grand Total') }}</th>
                                <th class="text-end fw-bold fs-5">{{ number_format($totalQuantity ?? 0, 2) }}</th>
                                <th class="text-end fw-bold text-primary fs-5">
                                    {{ number_format($totalPurchases ?? 0, 2) }}</th>
                                <th class="text-end fw-bold text-warning fs-5">{{ number_format($totalDiscount ?? 0, 2) }}
                                </th>
                                <th class="text-end fw-bold text-success fs-5">
                                    {{ number_format($totalNetPurchases ?? 0, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if (isset($purchases) && $purchases->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $purchases->links() }}
                    </div>
                @endif

                <!-- Analytics Summary -->
                @if (isset($purchases) && $purchases->count() > 0)
                    <div class="row mt-4 g-3">
                        <div class="col-md-3">
                            <div class="alert alert-info shadow-sm">
                                <i class="fas fa-file-invoice fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Total Invoices') }}:</strong> {{ $totalInvoices ?? 0 }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success shadow-sm">
                                <i class="fas fa-calculator fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Average Invoice Value') }}:</strong>
                                {{ number_format($averageInvoiceValue ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning shadow-sm">
                                <i class="fas fa-tags fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Total Discounts') }}:</strong> {{ number_format($totalDiscount ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-primary shadow-sm">
                                <i class="fas fa-wallet fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Net Purchases') }}:</strong>
                                {{ number_format($totalNetPurchases ?? 0, 2) }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
