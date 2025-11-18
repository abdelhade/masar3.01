@extends('admin.dashboard')
@section('sidebar')
    @include('components.sidebar.manufacturing')
@endsection
@section('content')
    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="font-family-cairo fw-bold">
                <i class="las la-industry"></i>
                {{ __('Manufacturing Statistics') }}
            </h1>
            <div>
                <a href="{{ route('manufacturing.index') }}" class="btn btn-secondary font-family-cairo fw-bold">
                    <i class="las la-arrow-right"></i> {{ __('Back') }}
                </a>
                <a href="{{ route('manufacturing.statistics') }}" class="btn btn-primary font-family-cairo fw-bold">
                    <i class="las la-sync"></i> {{ __('Refresh') }}
                </a>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show font-family-cairo fw-bold" role="alert">
                <i class="las la-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Cards Row 1: Overview --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">
                                    {{ __('Total Manufacturing Operations') }}</h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-primary">
                                    {{ number_format($statistics['totalManufacturing']) }}</h2>
                                <small class="text-muted font-family-cairo">{{ __('operation') }}</small>
                            </div>
                            <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-industry"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">{{ __('Total Cost') }}</h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-success">
                                    {{ number_format($statistics['totalCost'], 2) }}</h2>
                                <small class="text-muted font-family-cairo">{{ __('EGP') }}</small>
                            </div>
                            <div class="text-success" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">{{ __('Average Operation Cost') }}
                                </h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-info">
                                    {{ number_format($statistics['avgCost'], 2) }}</h2>
                                <small class="text-muted font-family-cairo">{{ __('EGP') }}</small>
                            </div>
                            <div class="text-info" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-calculator"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-family-cairo fw-bold mb-2">{{ __('Highest Cost') }}</h6>
                                <h2 class="font-family-cairo fw-bold mb-0 text-warning">
                                    {{ number_format($statistics['maxCost'], 2) }}</h2>
                                <small class="text-muted font-family-cairo">{{ __('EGP') }}</small>
                            </div>
                            <div class="text-warning" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-arrow-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cards Row 2: Current Period --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-calendar-day text-primary mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">{{ __('Current Month Operations') }}</h6>
                        <h3 class="font-family-cairo fw-bold text-primary mb-1">
                            {{ number_format($statistics['currentMonthManufacturing']) }}</h3>
                        <small class="text-muted font-family-cairo d-block mb-2">{{ __('operation') }}</small>
                        @if ($statistics['countChange'] != 0)
                            <span
                                class="badge {{ $statistics['countChange'] > 0 ? 'bg-success' : 'bg-danger' }} font-family-cairo">
                                <i class="las {{ $statistics['countChange'] > 0 ? 'la-arrow-up' : 'la-arrow-down' }}"></i>
                                {{ abs($statistics['countChange']) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-coins text-success mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">{{ __('Current Month Cost') }}</h6>
                        <h3 class="font-family-cairo fw-bold text-success mb-1">
                            {{ number_format($statistics['currentMonthCost'], 2) }}</h3>
                        <small class="text-muted font-family-cairo d-block mb-2">{{ __('EGP') }}</small>
                        @if ($statistics['costChange'] != 0)
                            <span
                                class="badge {{ $statistics['costChange'] > 0 ? 'bg-success' : 'bg-danger' }} font-family-cairo">
                                <i class="las {{ $statistics['costChange'] > 0 ? 'la-arrow-up' : 'la-arrow-down' }}"></i>
                                {{ abs($statistics['costChange']) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-calendar-alt text-info mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">{{ __('Current Year Operations') }}</h6>
                        <h3 class="font-family-cairo fw-bold text-info mb-0">
                            {{ number_format($statistics['currentYearManufacturing']) }}</h3>
                        <small class="text-muted font-family-cairo">{{ __('operation') }}</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="las la-hand-holding-usd text-warning mb-2" style="font-size: 2.5rem;"></i>
                        <h6 class="text-muted font-family-cairo fw-bold mb-2">{{ __('Current Year Cost') }}</h6>
                        <h3 class="font-family-cairo fw-bold text-warning mb-0">
                            {{ number_format($statistics['currentYearCost'], 2) }}</h3>
                        <small class="text-muted font-family-cairo">{{ __('EGP') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            {{-- Top Raw Materials --}}
            <div class="col-xl-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-trophy text-warning"></i>
                            {{ __('Most Used Raw Materials') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($statistics['topRawMaterials'] as $index => $material)
                            <div class="mb-3 p-2 rounded {{ $index == 0 ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $index == 0 ? 'bg-warning' : 'bg-secondary' }} me-2">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="font-family-cairo fw-bold">{{ $material['name'] }}</span>
                                    </div>
                                    <span class="badge bg-success font-family-cairo fw-bold">
                                        {{ number_format($material['total'], 2) }} {{ __('EGP') }}
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-gradient"
                                        style="width: {{ $statistics['totalCost'] > 0 ? ($material['total'] / $statistics['totalCost']) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <small class="text-muted font-family-cairo">
                                    {{ $material['count'] }} {{ __('operation') }} -
                                    {{ $statistics['totalCost'] > 0 ? number_format(($material['total'] / $statistics['totalCost']) * 100, 1) : 0 }}%
                                </small>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted font-family-cairo fw-bold">{{ __('No data available') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Cost Ranges --}}
            <div class="col-xl-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-chart-bar text-info"></i> {{ __('Cost Distribution by Value') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($statistics['costRanges'] as $range)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-family-cairo fw-bold">
                                        <i class="las la-tag text-primary me-1"></i>
                                        {{ $range['range'] }}
                                    </span>
                                    <div>
                                        <span class="badge bg-info font-family-cairo fw-bold me-1">
                                            {{ $range['count'] }} {{ __('operation') }}
                                        </span>
                                        <span class="badge bg-success font-family-cairo fw-bold">
                                            {{ number_format($range['total'], 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-gradient"
                                        style="width: {{ $statistics['totalCost'] > 0 ? ($range['total'] / $statistics['totalCost']) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <small class="text-muted font-family-cairo">
                                    {{ $statistics['totalCost'] > 0 ? number_format(($range['total'] / $statistics['totalCost']) * 100, 1) : 0 }}%
                                    {{ __('of total') }}
                                </small>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted font-family-cairo fw-bold">{{ __('No data available') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Trend --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-chart-line text-primary"></i> {{ __('Manufacturing Trend (Last 6 Months)') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($statistics['monthlyManufacturing'] as $month)
                                <div class="col-md-2 mb-3">
                                    <div class="text-center p-3 border rounded">
                                        <h6 class="font-family-cairo fw-bold text-muted mb-2">{{ $month['month_ar'] }}
                                        </h6>
                                        <h4 class="font-family-cairo fw-bold text-primary mb-1">
                                            {{ number_format($month['count']) }}</h4>
                                        <small
                                            class="text-muted font-family-cairo d-block mb-2">{{ __('operation') }}</small>
                                        <h5 class="font-family-cairo fw-bold text-success mb-0">
                                            {{ number_format($month['value'], 2) }}</h5>
                                        <small class="text-muted font-family-cairo">{{ __('EGP') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Branch Statistics --}}
        @if (count($statistics['branchStats']) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="font-family-cairo fw-bold mb-0">
                                <i class="las la-store text-success"></i> {{ __('Branch Statistics') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="font-family-cairo fw-bold">#</th>
                                            <th class="font-family-cairo fw-bold">{{ __('Branch Name') }}</th>
                                            <th class="font-family-cairo fw-bold text-center">
                                                {{ __('Number of Operations') }}</th>
                                            <th class="font-family-cairo fw-bold text-center">{{ __('Total Cost') }}</th>
                                            <th class="font-family-cairo fw-bold text-center">{{ __('Percentage') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($statistics['branchStats'] as $index => $branch)
                                            <tr>
                                                <td class="font-family-cairo fw-bold">{{ $index + 1 }}</td>
                                                <td class="font-family-cairo fw-bold">{{ $branch['branch_name'] }}</td>
                                                <td class="font-family-cairo fw-bold text-center">
                                                    <span
                                                        class="badge bg-primary">{{ number_format($branch['count']) }}</span>
                                                </td>
                                                <td class="font-family-cairo fw-bold text-center">
                                                    <span
                                                        class="badge bg-success">{{ number_format($branch['total'], 2) }}</span>
                                                </td>
                                                <td class="font-family-cairo fw-bold text-center">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar"
                                                            style="width: {{ $statistics['totalCost'] > 0 ? ($branch['total'] / $statistics['totalCost']) * 100 : 0 }}%">
                                                            {{ $statistics['totalCost'] > 0 ? number_format(($branch['total'] / $statistics['totalCost']) * 100, 1) : 0 }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Manufacturing Operations --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="font-family-cairo fw-bold mb-0">
                            <i class="las la-clock text-warning"></i> {{ __('Recent Manufacturing Operations') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (count($statistics['recentManufacturing']) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="font-family-cairo fw-bold">#</th>
                                            <th class="font-family-cairo fw-bold">{{ __('Operation Number') }}</th>
                                            <th class="font-family-cairo fw-bold">{{ __('Account') }}</th>
                                            <th class="font-family-cairo fw-bold text-center">{{ __('Cost') }}</th>
                                            <th class="font-family-cairo fw-bold text-center">{{ __('Date') }}</th>
                                            <th class="font-family-cairo fw-bold">{{ __('Notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($statistics['recentManufacturing'] as $index => $operation)
                                            <tr>
                                                <td class="font-family-cairo fw-bold">{{ $index + 1 }}</td>
                                                <td class="font-family-cairo fw-bold">{{ $operation['pro_id'] }}</td>
                                                <td class="font-family-cairo fw-bold">{{ $operation['account_name'] }}
                                                </td>
                                                <td class="font-family-cairo fw-bold text-center">
                                                    {{ number_format($operation['value'], 2) }}</td>
                                                <td class="font-family-cairo fw-bold text-center">{{ $operation['date'] }}
                                                </td>
                                                <td class="font-family-cairo fw-bold">{{ $operation['info'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="las la-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted font-family-cairo fw-bold">{{ __('No data available') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
