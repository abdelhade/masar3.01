@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('depreciation.index') }}">{{ __('Depreciation Management') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Depreciation Report') }}</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        {{ __('Asset Depreciation Report') }}
                    </h2>
                    <div class="btn-group">
                        <a href="{{ route('depreciation.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ __('Back') }}
                        </a>
                        <button onclick="window.print()" class="btn btn-success">
                            <i class="fas fa-print me-2"></i>
                            {{ __('Print') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="GET" action="{{ route('depreciation.report') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Branch') }}</label>
                            <select name="branch_id" class="form-select">
                                <option value="">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('From Date') }}</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('To Date') }}</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('depreciation.report') }}" class="btn btn-outline-secondary">
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Total Assets') }}</h6>
                                <h4>{{ $items->count() }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Total Cost') }}</h6>
                                <h4>{{ number_format($items->sum('cost'), 2) }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Total Accumulated Depreciation') }}</h6>
                                <h4>{{ number_format($items->sum('accumulated_depreciation'), 2) }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Net Book Value') }}</h6>
                                <h4>{{ number_format($items->sum('cost') - $items->sum('accumulated_depreciation'), 2) }}
                                </h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calculator fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Purchase Date') }}</th>
                                <th>{{ __('Cost') }}</th>
                                <th>{{ __('Useful Life') }}</th>
                                <th>{{ __('Salvage Value') }}</th>
                                <th>{{ __('Annual Depreciation') }}</th>
                                <th>{{ __('Accumulated Depreciation') }}</th>
                                <th>{{ __('Maintenance Costs') }}</th>
                                <th>{{ __('Book Value') }}</th>
                                <th>{{ __('Depreciation Percentage') }}</th>
                                <th>{{ __('Remaining Years') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $item->name }}</strong>
                                            @if ($item->assetAccount)
                                                <small class="text-muted">
                                                    {{ $item->assetAccount->code }} - {{ $item->assetAccount->aname }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $item->branch->name ?? '-' }}</td>
                                    <td>{{ $item->purchase_date->format('Y-m-d') }}</td>
                                    <td>{{ number_format($item->cost, 2) }}</td>
                                    <td>{{ $item->useful_life }} {{ __('Years') }}</td>
                                    <td>{{ number_format($item->salvage_value, 2) }}</td>
                                    <td>{{ number_format($item->annual_depreciation, 2) }}</td>
                                    <td>{{ number_format($item->accumulated_depreciation, 2) }}</td>
                                    <td class="text-danger fw-bold">
                                        {{ number_format($item->getTotalMaintenanceCost(), 2) }}</td>
                                    <td>{{ number_format($item->getNetBookValue(), 2) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar"
                                                style="width: {{ $item->getDepreciationPercentage() }}%"
                                                role="progressbar">
                                                {{ number_format($item->getDepreciationPercentage(), 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $remainingYears = $item->getRemainingLife();
                                        @endphp
                                        <span class="badge {{ $remainingYears > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $remainingYears }} {{ __('Years') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3"></i>
                                            <p>{{ __('No data matches the specified criteria') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Report Footer -->
        <div class="mt-4 text-center text-muted small print-only">
            <p>{{ __('Asset Depreciation Report') }} - {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                .print-only {
                    display: block !important;
                    color: #000 !important;
                }

                .card {
                    border: 1px solid #000 !important;
                    box-shadow: none !important;
                }

                .table th,
                .table td {
                    border: 1px solid #000 !important;
                }

                .progress {
                    border: 1px solid #000;
                }

                .progress-bar {
                    background-color: #000 !important;
                    color: #fff !important;
                }
            }

            .print-only {
                display: none;
            }

            .progress {
                background-color: #e9ecef;
            }

            .progress-bar {
                background-color: #28a745;
                color: white;
                font-size: 12px;
                line-height: 20px;
            }
        </style>
    @endpush
@endsection
