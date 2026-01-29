@extends('admin.dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">{{ __('Depreciation Item Details') }}: #{{ $item->id }}</h4>
                        <div class="d-flex gap-2">
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print"></i> {{ __('Print') }}
                            </button>
                            <a href="{{ route('depreciation.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card printable-content">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> {{ __('Depreciation Item Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($item->getAttributes() as $key => $value)
                                @if (!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                                    <div class="col-md-6 mb-3">
                                        <label
                                            class="form-label fw-bold">{{ ucfirst(str_replace('_', ' ', $key)) }}:</label>
                                        <div class="form-control-static">
                                            @if ($value)
                                                @if (in_array($key, ['purchase_date', 'depreciation_start_date']) && $value)
                                                    {{ \Carbon\Carbon::parse($value)->format('Y-m-d') }}
                                                @elseif(
                                                    (is_numeric($value) && strpos($key, 'cost') !== false) ||
                                                        strpos($key, 'value') !== false ||
                                                        strpos($key, 'depreciation') !== false)
                                                    {{ number_format($value, 2) }}
                                                @else
                                                    {{ is_array($value) ? json_encode($value) : $value }}
                                                @endif
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-danger">{{ __('Total Maintenance Cost') }}:</label>
                                <div class="form-control-static fw-bold text-danger">
                                    {{ number_format($item->getTotalMaintenanceCost(), 2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance History Section -->
                        <div class="mt-4">
                            <h5 class="border-bottom pb-2 mb-3"><i
                                    class="fas fa-tools me-2"></i>{{ __('Maintenance History') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Cost') }}</th>
                                            <th>{{ __('Notes') }}</th>
                                            <th class="no-print">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($item->maintenances as $maintenance)
                                            <tr>
                                                <td>{{ $maintenance->date?->format('Y-m-d') }}</td>
                                                <td>{{ __($maintenance->maintenance_type) }}</td>
                                                <td>
                                                    @php
                                                        $statusLabels = [
                                                            '0' => __('Pending'),
                                                            '1' => __('In Progress'),
                                                            '2' => __('Completed'),
                                                            '3' => __('Cancelled'),
                                                        ];
                                                        $status = (string) ($maintenance->status ?? '0');
                                                    @endphp
                                                    {{ $statusLabels[$status] ?? $status }}
                                                </td>
                                                <td>{{ number_format($maintenance->total_cost, 2) }}</td>
                                                <td>{{ Str::limit($maintenance->notes, 50) }}</td>
                                                <td class="no-print">
                                                    <a href="{{ route('maintenances.show', $maintenance) }}"
                                                        class="btn btn-xs btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    {{ __('No maintenance history found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .form-control-static {
                padding: 0.5rem;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                min-height: 2.5rem;
                display: flex;
                align-items: center;
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                .card {
                    border: 1px solid #000 !important;
                    box-shadow: none !important;
                }

                .card-header {
                    background: #f1f1f1 !important;
                    color: #000 !important;
                }

                body {
                    font-size: 12px;
                }

                .form-control-static {
                    background: #fff !important;
                    border: 1px solid #000 !important;
                }
            }
        </style>
    @endpush
@endsection
