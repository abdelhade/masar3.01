@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Cost Centers List') }}</h2>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="as_of_date">{{ __('Until Date:') }}</label>
                        <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                    </div>
                    <div class="col-md-3">
                        <label for="cost_center_type">{{ __('Cost Center Type:') }}</label>
                        <select id="cost_center_type" class="form-control" wire:model="costCenterType">
                            <option value="">{{ __('All') }}</option>
                            <option value="department">{{ __('Department') }}</option>
                            <option value="project">{{ __('Project') }}</option>
                            <option value="branch">{{ __('Branch') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search">{{ __('Search:') }}</label>
                        <input type="text" id="search" class="form-control" wire:model="search"
                            placeholder="{{ __('Cost center name...') }}">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary mt-4"
                            wire:click="generateReport">{{ __('Generate Report') }}</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Cost Center Code') }}</th>
                                <th>{{ __('Cost Center Name') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Manager') }}</th>
                                <th class="text-end">{{ __('Total Expenses') }}</th>
                                <th class="text-end">{{ __('Total Revenues') }}</th>
                                <th class="text-end">{{ __('Net Cost') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($costCenters as $center)
                                <tr>
                                    <td>{{ $center->code ?? '---' }}</td>
                                    <td>{{ $center->name ?? '---' }}</td>
                                    <td>{{ $center->type ?? '---' }}</td>
                                    <td>{{ $center->manager->name ?? '---' }}</td>
                                    <td class="text-end">{{ number_format($center->total_expenses, 2) }}</td>
                                    <td class="text-end">{{ number_format($center->total_revenues, 2) }}</td>
                                    <td class="text-end">{{ number_format($center->net_cost, 2) }}</td>
                                    <td>
                                        @if ($center->is_active)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No data available.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="4">{{ __('Total') }}</th>
                                <th class="text-end">{{ number_format($totalExpenses, 2) }}</th>
                                <th class="text-end">{{ number_format($totalRevenues, 2) }}</th>
                                <th class="text-end">{{ number_format($totalNetCost, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($costCenters->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $costCenters->links() }}
                    </div>
                @endif

                <!-- Summary -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>{{ __('Total Cost Centers:') }}</strong> {{ $totalCostCenters }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>{{ __('Active Cost Centers:') }}</strong> {{ $activeCostCenters }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>{{ __('Average Cost Per Center:') }}</strong>
                            {{ number_format($averageCostPerCenter, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <strong>{{ __('Total Net Cost:') }}</strong> {{ number_format($totalNetCost, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
