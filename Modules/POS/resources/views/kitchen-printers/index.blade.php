@extends('pos::layouts.master')

@section('content')
<div class="kitchen-printers-container">
    <div class="page-header">
        <a href="{{ route('pos.settings') }}" class="back-link">
            <i class="las la-arrow-right"></i>
            <span>{{ __('pos.back_to_settings') }}</span>
        </a>
        <div class="page-title">
            <h1>{{ __('pos.kitchen_printer_stations') }}</h1>
            <p class="page-subtitle">{{ __('pos.manage_kitchen_printers_desc') }}</p>
        </div>
        <a href="{{ route('kitchen-printers.create') }}" class="btn btn-primary">
            <i class="las la-plus"></i>
            {{ __('pos.add_printer_station') }}
        </a>
    </div>

    @if($stations->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">
            <i class="las la-print"></i>
        </div>
        <h3>{{ __('pos.no_printer_stations') }}</h3>
        <p>{{ __('pos.no_printer_stations_desc') }}</p>
        <a href="{{ route('kitchen-printers.create') }}" class="btn btn-primary">
            <i class="las la-plus"></i>
            {{ __('pos.add_first_printer_station') }}
        </a>
    </div>
    @else
    <div class="printers-table-container">
        <table class="table printers-table">
            <thead>
                <tr>
                    <th>{{ __('pos.station_name') }}</th>
                    <th>{{ __('pos.printer_name') }}</th>
                    <th>{{ __('pos.status') }}</th>
                    <th>{{ __('pos.default_station') }}</th>
                    <th>{{ __('pos.sort_order') }}</th>
                    <th>{{ __('pos.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stations as $station)
                <tr>
                    <td>
                        <div class="station-name">
                            <i class="las la-print"></i>
                            <span>{{ $station->name }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="printer-name">{{ $station->printer_name }}</span>
                    </td>
                    <td>
                        @if($station->is_active)
                        <span class="badge badge-success">
                            <i class="las la-check-circle"></i>
                            {{ __('pos.active') }}
                        </span>
                        @else
                        <span class="badge badge-secondary">
                            <i class="las la-times-circle"></i>
                            {{ __('pos.inactive') }}
                        </span>
                        @endif
                    </td>
                    <td>
                        @if($station->is_default)
                        <span class="badge badge-primary">
                            <i class="las la-star"></i>
                            {{ __('pos.default') }}
                        </span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="sort-order">{{ $station->sort_order }}</span>
                    </td>
                    <td>
                        <div class="action-buttons" x-data="{ showMenu: false }">
                            <a href="{{ route('kitchen-printers.edit', $station) }}" 
                               class="btn btn-sm btn-info" 
                               title="{{ __('pos.edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <form action="{{ route('kitchen-printers.destroy', $station) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('{{ __('pos.confirm_delete_printer_station') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-sm btn-danger" 
                                        title="{{ __('pos.delete') }}">
                                    <i class="las la-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .kitchen-printers-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
        background: #ffffff;
        min-height: 100vh;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--mint-green-200);
        gap: 1rem;
        flex-wrap: wrap;
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

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .btn-info {
        background: #3b82f6;
        color: white;
    }

    .btn-info:hover {
        background: #2563eb;
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
        margin-top: 2rem;
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
        margin-bottom: 1.5rem;
    }

    .printers-table-container {
        background: white;
        border: 2px solid var(--mint-green-200);
        border-radius: 12px;
        overflow: hidden;
    }

    .printers-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .printers-table thead {
        background: var(--mint-green-50);
    }

    .printers-table th {
        padding: 1rem 1.5rem;
        text-align: right;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--mint-green-500);
        border-bottom: 2px solid var(--mint-green-200);
    }

    .printers-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--mint-green-100);
        color: #111827;
        vertical-align: middle;
    }

    .printers-table tbody tr:hover {
        background: var(--mint-green-50);
    }

    .printers-table tbody tr:last-child td {
        border-bottom: none;
    }

    .station-name {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    .station-name i {
        font-size: 1.25rem;
        color: var(--mint-green-500);
    }

    .printer-name {
        font-family: 'Courier New', monospace;
        background: var(--mint-green-50);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
        color: #374151;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .badge i {
        font-size: 1rem;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .badge-secondary {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #d1d5db;
    }

    .badge-primary {
        background: var(--mint-green-100);
        color: var(--mint-green-500);
        border: 1px solid var(--mint-green-300);
    }

    .text-muted {
        color: #9ca3af;
    }

    .sort-order {
        font-weight: 500;
        color: #6b7280;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .action-buttons form {
        margin: 0;
    }

    /* Dark Mode */
    body.dark-mode .kitchen-printers-container {
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

    body.dark-mode .printers-table-container {
        background: #1f2937;
        border-color: #374151;
    }

    body.dark-mode .printers-table thead {
        background: #374151;
    }

    body.dark-mode .printers-table th {
        color: #d1d5db;
        border-bottom-color: #4b5563;
    }

    body.dark-mode .printers-table td {
        color: #f9fafb;
        border-bottom-color: #374151;
    }

    body.dark-mode .printers-table tbody tr:hover {
        background: #374151;
    }

    body.dark-mode .station-name i {
        color: #d1d5db;
    }

    body.dark-mode .printer-name {
        background: #374151;
        color: #d1d5db;
    }

    body.dark-mode .badge-success {
        background: #064e3b;
        color: #6ee7b7;
        border-color: #065f46;
    }

    body.dark-mode .badge-secondary {
        background: #374151;
        color: #9ca3af;
        border-color: #4b5563;
    }

    body.dark-mode .badge-primary {
        background: #374151;
        color: #d1d5db;
        border-color: #4b5563;
    }

    body.dark-mode .text-muted {
        color: #6b7280;
    }

    body.dark-mode .sort-order {
        color: #9ca3af;
    }

    @media (max-width: 768px) {
        .kitchen-printers-container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .printers-table-container {
            overflow-x: auto;
        }

        .printers-table {
            min-width: 800px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
