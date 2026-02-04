@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Purchases Totals Report') }}</h2>
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
                        <label for="group_by" class="form-label fw-bold">{{ __('Group By') }}:</label>
                        <select id="group_by" class="form-select" wire:model.live="groupBy">
                            <option value="day">{{ __('Day') }}</option>
                            <option value="week">{{ __('Week') }}</option>
                            <option value="month">{{ __('Month') }}</option>
                            <option value="supplier">{{ __('Supplier') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100 h-100" wire:click="generateReport">
                            <i class="fas fa-chart-pie me-2"></i>{{ __('Generate Report') }}
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <div class="card bg-info text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-file-invoice fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Invoices Count') }}</h6>
                                <h4 class="fw-bold mb-0">{{ $grandTotalInvoices ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Quantity') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($grandTotalQuantity ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Total Purchases') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($grandTotalPurchases ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-calculator fa-2x mb-2 opacity-75"></i>
                                <h6 class="fw-bold">{{ __('Grand Total Net Purchases') }}</h6>
                                <h4 class="fw-bold mb-0">{{ number_format($grandTotalNetPurchases ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center fw-bold">
                                    {{ $groupBy == 'supplier' ? __('Supplier') : __('Period') }}
                                </th>
                                <th class="text-end fw-bold">{{ __('Invoices Count') }}</th>
                                <th class="text-end fw-bold">{{ __('Total Quantity') }}</th>
                                <th class="text-end fw-bold text-primary">{{ __('Total Purchases') }}</th>
                                <th class="text-end fw-bold text-warning">{{ __('Total Discount') }}</th>
                                <th class="text-end fw-bold text-info">{{ __('Net Purchases') }}</th>
                                <th class="text-end fw-bold text-success">{{ __('Average Invoice') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchasesTotals ?? collect() as $total)
                                <tr class="{{ $total->net_purchases > 0 ? 'table-light' : 'table-secondary' }}">
                                    <td class="fw-semibold">
                                        @if ($groupBy == 'supplier')
                                            <i class="fas fa-user me-2 text-info"></i>
                                            {{ $total->supplier_name ?? '---' }}
                                        @else
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                            {{ $total->period_name ?? '---' }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info">{{ $total->invoices_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($total->total_quantity ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold text-primary fs-6">
                                        {{ number_format($total->total_purchases ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-warning">
                                        {{ number_format($total->total_discount ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-info fs-6">
                                        {{ number_format($total->net_purchases ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ number_format($total->average_invoice ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-chart-line fa-2x mb-3 d-block"></i>
                                            {{ __('No Data Available') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th class="text-end fw-bold fs-5">{{ __('Grand Total') }}</th>
                                <th class="text-end fw-bold fs-5">{{ $grandTotalInvoices ?? 0 }}</th>
                                <th class="text-end fw-bold fs-5">{{ number_format($grandTotalQuantity ?? 0, 2) }}</th>
                                <th class="text-end fw-bold text-primary fs-5">
                                    {{ number_format($grandTotalPurchases ?? 0, 2) }}</th>
                                <th class="text-end fw-bold text-warning fs-5">
                                    {{ number_format($grandTotalDiscount ?? 0, 2) }}</th>
                                <th class="text-end fw-bold text-info fs-5">
                                    {{ number_format($grandTotalNetPurchases ?? 0, 2) }}</th>
                                <th class="text-end fw-bold text-success fs-5">
                                    {{ number_format($grandAverageInvoice ?? 0, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if (isset($purchasesTotals) && $purchasesTotals->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $purchasesTotals->links() }}
                    </div>
                @endif

                <!-- Analytics Summary -->
                @if (isset($purchasesTotals) && $purchasesTotals->count() > 0)
                    <div class="row mt-4 g-3">
                        <div class="col-md-3">
                            <div class="alert alert-info shadow-sm">
                                <i class="fas fa-calendar-week fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Total Periods') }}:</strong> {{ $totalPeriods ?? 0 }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success shadow-sm">
                                <i class="fas fa-trophy fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Highest Purchases') }}:</strong>
                                {{ number_format($highestPurchases ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning shadow-sm">
                                <i class="fas fa-chart-line fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Lowest Purchases') }}:</strong>
                                {{ number_format($lowestPurchases ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-primary shadow-sm">
                                <i class="fas fa-calculator fa-2x float-start me-2 mb-2"></i>
                                <strong>{{ __('Average Purchases') }}:</strong>
                                {{ number_format($averagePurchases ?? 0, 2) }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
