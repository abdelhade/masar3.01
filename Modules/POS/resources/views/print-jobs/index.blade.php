@extends('pos::layouts.master')

@section('content')
<div class="print-jobs-container">
    <div class="page-header">
        <a href="{{ route('pos.settings') }}" class="back-link">
            <i class="las la-arrow-right"></i>
            <span>{{ __('pos.back_to_settings') }}</span>
        </a>
        <div class="page-title">
            <h1>{{ __('pos.print_jobs_log') }}</h1>
            <p class="page-subtitle">{{ __('pos.print_jobs_log_desc') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card" x-data="{ showFilters: true }">
        <div class="filters-header" @click="showFilters = !showFilters">
            <h3>
                <i class="las la-filter"></i>
                {{ __('pos.filters') }}
            </h3>
            <i class="las" :class="showFilters ? 'la-angle-up' : 'la-angle-down'"></i>
        </div>
        
        <form method="GET" action="{{ route('print-jobs.index') }}" x-show="showFilters" x-transition>
            <div class="filters-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from" class="form-label">{{ __('pos.date_from') }}</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to" class="form-label">{{ __('pos.date_to') }}</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="printer_station_id" class="form-label">{{ __('pos.printer_station') }}</label>
                            <select class="form-control" id="printer_station_id" name="printer_station_id">
                                <option value="">{{ __('pos.all_stations') }}</option>
                                @foreach($printerStations as $station)
                                <option value="{{ $station->id }}" {{ request('printer_station_id') == $station->id ? 'selected' : '' }}>
                                    {{ $station->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status" class="form-label">{{ __('pos.status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">{{ __('pos.all_statuses') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('pos.pending') }}</option>
                                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>{{ __('pos.success') }}</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>{{ __('pos.failed') }}</option>
                                <option value="retrying" {{ request('status') == 'retrying' ? 'selected' : '' }}>{{ __('pos.retrying') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-search"></i>
                        {{ __('pos.search') }}
                    </button>
                    <a href="{{ route('print-jobs.index') }}" class="btn btn-secondary">
                        <i class="las la-redo"></i>
                        {{ __('pos.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>


    <!-- Print Jobs Table -->
    @if($printJobs->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">
            <i class="las la-file-alt"></i>
        </div>
        <h3>{{ __('pos.no_print_jobs') }}</h3>
        <p>{{ __('pos.no_print_jobs_desc') }}</p>
    </div>
    @else
    <div class="jobs-table-container">
        <table class="table jobs-table">
            <thead>
                <tr>
                    <th>{{ __('pos.id') }}</th>
                    <th>{{ __('pos.printer_station') }}</th>
                    <th>{{ __('pos.transaction') }}</th>
                    <th>{{ __('pos.status') }}</th>
                    <th>{{ __('pos.attempts') }}</th>
                    <th>{{ __('pos.type') }}</th>
                    <th>{{ __('pos.created_at') }}</th>
                    <th>{{ __('pos.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($printJobs as $job)
                <tr x-data="{ showError: false }">
                    <td>
                        <span class="job-id">#{{ $job->id }}</span>
                    </td>
                    <td>
                        <div class="station-info">
                            <i class="las la-print"></i>
                            <span>{{ $job->printerStation->name ?? __('pos.deleted') }}</span>
                        </div>
                    </td>
                    <td>
                        @if($job->transaction)
                        <a href="{{ route('pos.show', $job->transaction_id) }}" class="transaction-link">
                            #{{ $job->transaction_id }}
                        </a>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($job->status === 'success')
                        <span class="badge badge-success">
                            <i class="las la-check-circle"></i>
                            {{ __('pos.success') }}
                        </span>
                        @elseif($job->status === 'failed')
                        <span class="badge badge-danger">
                            <i class="las la-times-circle"></i>
                            {{ __('pos.failed') }}
                        </span>
                        @elseif($job->status === 'retrying')
                        <span class="badge badge-warning">
                            <i class="las la-sync"></i>
                            {{ __('pos.retrying') }}
                        </span>
                        @else
                        <span class="badge badge-secondary">
                            <i class="las la-clock"></i>
                            {{ __('pos.pending') }}
                        </span>
                        @endif
                    </td>

                    <td>
                        <span class="attempts-badge">{{ $job->attempts }}</span>
                    </td>
                    <td>
                        @if($job->is_manual)
                        <span class="badge badge-info">
                            <i class="las la-hand-pointer"></i>
                            {{ __('pos.manual') }}
                        </span>
                        @else
                        <span class="badge badge-secondary">
                            <i class="las la-robot"></i>
                            {{ __('pos.automatic') }}
                        </span>
                        @endif
                    </td>
                    <td>
                        <div class="datetime">
                            <div class="date">{{ $job->created_at->format('Y-m-d') }}</div>
                            <div class="time">{{ $job->created_at->format('H:i:s') }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            @if($job->status === 'failed')
                            <form action="{{ route('print-jobs.retry', $job) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-sm btn-warning" 
                                        title="{{ __('pos.retry') }}">
                                    <i class="las la-redo"></i>
                                </button>
                            </form>
                            @endif
                            
                            @if($job->error_message)
                            <button type="button" 
                                    class="btn btn-sm btn-danger" 
                                    @click="showError = !showError"
                                    title="{{ __('pos.show_error') }}">
                                <i class="las la-exclamation-circle"></i>
                            </button>
                            @endif
                        </div>
                        
                        @if($job->error_message)
                        <div x-show="showError" x-transition class="error-details">
                            <strong>{{ __('pos.error_message') }}:</strong>
                            <p>{{ $job->error_message }}</p>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
        {{ $printJobs->links() }}
    </div>
    @endif
</div>
@endsection


@push('styles')
<style>
    .print-jobs-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 2rem;
        background: #ffffff;
        min-height: 100vh;
    }

    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--mint-green-200);
    }

    .back-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: var(--mint-green-500);
    }

    .back-link i {
        font-size: 1.25rem;
    }

    .page-title {
        flex: 1;
    }

    .page-title h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.25rem 0;
    }

    .page-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .filters-card {
        background: white;
        border: 2px solid var(--mint-green-200);
        border-radius: 12px;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .filters-header {
        padding: 1rem 1.5rem;
        background: var(--mint-green-50);
        border-bottom: 2px solid var(--mint-green-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: background 0.2s;
    }

    .filters-header:hover {
        background: var(--mint-green-100);
    }

    .filters-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--mint-green-500);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filters-header i {
        font-size: 1.25rem;
    }

    .filters-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 0.875rem;
        font-size: 0.9375rem;
        font-family: 'Cairo', sans-serif;
        border: 2px solid var(--mint-green-200);
        border-radius: 8px;
        transition: all 0.2s;
        background: white;
        color: #111827;
    }

    .form-control:focus {
        border-color: var(--mint-green-400);
        box-shadow: 0 0 0 3px rgba(42, 184, 141, 0.1);
        outline: none;
    }

    .filters-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }


    .btn {
        font-family: 'Cairo', sans-serif;
        font-weight: 500;
        border-radius: 8px;
        padding: 0.625rem 1.25rem;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn i {
        font-size: 1.125rem;
    }

    .btn-primary {
        background: var(--mint-green-500);
        color: white;
    }

    .btn-primary:hover {
        background: var(--mint-green-400);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(42, 184, 141, 0.3);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--mint-green-50);
        border: 2px dashed var(--mint-green-300);
        border-radius: 12px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: white;
        border: 3px solid var(--mint-green-300);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: var(--mint-green-500);
        font-size: 2.5rem;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        font-size: 1rem;
        color: #6b7280;
    }

    .jobs-table-container {
        background: white;
        border: 2px solid var(--mint-green-200);
        border-radius: 12px;
        overflow: hidden;
    }

    .jobs-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .jobs-table thead {
        background: var(--mint-green-50);
    }

    .jobs-table th {
        padding: 1rem 1rem;
        text-align: right;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--mint-green-500);
        border-bottom: 2px solid var(--mint-green-200);
        white-space: nowrap;
    }

    .jobs-table td {
        padding: 1rem 1rem;
        border-bottom: 1px solid var(--mint-green-100);
        color: #111827;
        vertical-align: top;
    }

    .jobs-table tbody tr:hover {
        background: var(--mint-green-50);
    }

    .jobs-table tbody tr:last-child td {
        border-bottom: none;
    }


    .job-id {
        font-weight: 600;
        color: var(--mint-green-500);
        font-family: 'Courier New', monospace;
    }

    .station-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .station-info i {
        font-size: 1.125rem;
        color: var(--mint-green-500);
    }

    .transaction-link {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
        font-family: 'Courier New', monospace;
    }

    .transaction-link:hover {
        text-decoration: underline;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8125rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .badge i {
        font-size: 1rem;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .badge-secondary {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #d1d5db;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    .attempts-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        background: var(--mint-green-100);
        color: var(--mint-green-500);
        border-radius: 50%;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .datetime {
        font-size: 0.875rem;
    }

    .datetime .date {
        font-weight: 500;
        color: #374151;
    }

    .datetime .time {
        color: #6b7280;
        font-size: 0.8125rem;
    }

    .text-muted {
        color: #9ca3af;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .action-buttons form {
        margin: 0;
    }

    .error-details {
        margin-top: 0.75rem;
        padding: 0.75rem;
        background: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .error-details strong {
        display: block;
        color: #991b1b;
        margin-bottom: 0.375rem;
    }

    .error-details p {
        color: #7f1d1d;
        margin: 0;
        word-break: break-word;
    }

    .pagination-container {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }


    /* Dark Mode */
    body.dark-mode .print-jobs-container {
        background: #111827;
    }

    body.dark-mode .page-header {
        border-bottom-color: #374151;
    }

    body.dark-mode .back-link {
        color: #9ca3af;
    }

    body.dark-mode .back-link:hover {
        color: #d1d5db;
    }

    body.dark-mode .page-title h1 {
        color: #f9fafb;
    }

    body.dark-mode .page-subtitle {
        color: #9ca3af;
    }

    body.dark-mode .filters-card {
        background: #1f2937;
        border-color: #374151;
    }

    body.dark-mode .filters-header {
        background: #374151;
        border-bottom-color: #4b5563;
    }

    body.dark-mode .filters-header:hover {
        background: #4b5563;
    }

    body.dark-mode .filters-header h3 {
        color: #d1d5db;
    }

    body.dark-mode .form-label {
        color: #d1d5db;
    }

    body.dark-mode .form-control {
        background: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    body.dark-mode .form-control:focus {
        border-color: #6b7280;
        background: #374151;
    }

    body.dark-mode .empty-state {
        background: #1f2937;
        border-color: #374151;
    }

    body.dark-mode .empty-icon {
        background: #374151;
        border-color: #4b5563;
        color: #d1d5db;
    }

    body.dark-mode .empty-state h3 {
        color: #f9fafb;
    }

    body.dark-mode .empty-state p {
        color: #9ca3af;
    }

    body.dark-mode .jobs-table-container {
        background: #1f2937;
        border-color: #374151;
    }

    body.dark-mode .jobs-table thead {
        background: #374151;
    }

    body.dark-mode .jobs-table th {
        color: #d1d5db;
        border-bottom-color: #4b5563;
    }

    body.dark-mode .jobs-table td {
        color: #f9fafb;
        border-bottom-color: #374151;
    }

    body.dark-mode .jobs-table tbody tr:hover {
        background: #374151;
    }

    body.dark-mode .job-id {
        color: #d1d5db;
    }

    body.dark-mode .station-info i {
        color: #d1d5db;
    }

    body.dark-mode .transaction-link {
        color: #60a5fa;
    }

    body.dark-mode .badge-success {
        background: #064e3b;
        color: #6ee7b7;
        border-color: #065f46;
    }

    body.dark-mode .badge-danger {
        background: #7f1d1d;
        color: #fca5a5;
        border-color: #991b1b;
    }

    body.dark-mode .badge-warning {
        background: #78350f;
        color: #fcd34d;
        border-color: #92400e;
    }

    body.dark-mode .badge-secondary {
        background: #374151;
        color: #9ca3af;
        border-color: #4b5563;
    }

    body.dark-mode .badge-info {
        background: #1e3a8a;
        color: #93c5fd;
        border-color: #1e40af;
    }

    body.dark-mode .attempts-badge {
        background: #374151;
        color: #d1d5db;
    }

    body.dark-mode .datetime .date {
        color: #d1d5db;
    }

    body.dark-mode .datetime .time {
        color: #9ca3af;
    }

    body.dark-mode .text-muted {
        color: #6b7280;
    }

    body.dark-mode .error-details {
        background: #7f1d1d;
        border-color: #991b1b;
    }

    body.dark-mode .error-details strong {
        color: #fca5a5;
    }

    body.dark-mode .error-details p {
        color: #fecaca;
    }

    @media (max-width: 768px) {
        .print-jobs-container {
            padding: 1rem;
        }

        .jobs-table-container {
            overflow-x: auto;
        }

        .jobs-table {
            min-width: 1200px;
        }

        .filters-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
