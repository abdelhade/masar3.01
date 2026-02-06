@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h2 class="mb-0">
                        <i class="fas fa-award me-2"></i>
                        {{ __('Quality Management System (QMS)') }}
                    </h2>
                    <p class="mb-0 mt-2">{{ __('Comprehensive dashboard to track all quality and inspection operations') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Inspections -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Total Inspections') }}</h6>
                            <h3 class="mb-0">{{ $totalInspections }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> {{ __('Success Rate') }}: {{ number_format($passRate, 1) }}%
                            </small>
                        </div>
                        <div class="text-primary" style="font-size: 3rem;">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NCRs -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Non-Conformance Reports') }}</h6>
                            <h3 class="mb-0">{{ $totalNCRs }}</h3>
                            <small class="text-danger">
                                <i class="fas fa-exclamation-circle"></i> {{ __('Open') }}: {{ $openNCRs }}
                            </small>
                        </div>
                        <div class="text-danger" style="font-size: 3rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CAPA -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Corrective Actions') }}</h6>
                            <h3 class="mb-0">{{ $activeCapas }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-clock"></i> {{ __('Overdue') }}: {{ $overdueCapas }}
                            </small>
                        </div>
                        <div class="text-warning" style="font-size: 3rem;">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batches -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">{{ __('Active Batches') }}</h6>
                            <h3 class="mb-0">{{ $activeBatches }}</h3>
                            <small class="text-info">
                                <i class="fas fa-hourglass-half"></i> {{ __('Expiring Soon') }}: {{ $expiringSoonBatches }}
                            </small>
                        </div>
                        <div class="text-info" style="font-size: 3rem;">
                            <i class="fas fa-barcode"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="row">
        <!-- Recent Inspections -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        {{ __('Recent Inspections') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Inspection Number') }}</th>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Result') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInspections as $inspection)
                                <tr>
                                    <td>
                                        <a href="{{ route('quality.inspections.show', $inspection) }}">
                                            {{ $inspection->inspection_number }}
                                        </a>
                                    </td>
                                    <td>{{ $inspection->item->name ?? '---' }}</td>
                                    <td>
                                        @if($inspection->result == 'pass')
                                            <span class="badge bg-success">{{ __('Pass') }}</span>
                                        @elseif($inspection->result == 'fail')
                                            <span class="badge bg-danger">{{ __('Fail') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('Conditional') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $inspection->inspection_date->format('Y-m-d') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('No inspections') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent NCRs -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __('Recent Non-Conformance Reports') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('NCR Number') }}</th>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Severity') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentNCRs as $ncr)
                                <tr>
                                    <td>{{ $ncr->ncr_number }}</td>
                                    <td>{{ $ncr->item->name ?? '---' }}</td>
                                    <td>
                                        @if($ncr->severity == 'critical')
                                            <span class="badge bg-danger">{{ __('Critical') }}</span>
                                        @elseif($ncr->severity == 'major')
                                            <span class="badge bg-warning">{{ __('Major') }}</span>
                                        @else
                                            <span class="badge bg-info">{{ __('Minor') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ncr->status == 'open' ? 'danger' : 'success' }}">
                                            {{ $ncr->status == 'open' ? __('Open') : __('Closed') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('No reports') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ __('Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('quality.inspections.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-plus-circle me-2"></i>
                                {{ __('New Inspection') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ url('/quality/ncrs/create') }}" class="btn btn-danger w-100">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ __('New NCR Report') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ url('/quality/batches') }}" class="btn btn-info w-100">
                                <i class="fas fa-barcode me-2"></i>
                                {{ __('Manage Batches') }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('quality.reports') }}" class="btn btn-success w-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                {{ __('View Reports') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

