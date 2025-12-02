@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Returns'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Returns')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            <!-- Header with Statistics -->
            <div class="row mb-3">
                <div class="col-md-8">
                    @can('create Returns')
                        <a href="{{ route('returns.create') }}" class="btn btn-main font-hold fw-bold">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('Add New Return') }}
                        </a>
                    @endcan
                </div>
                <div class="col-md-4">
                    <div class="row g-2">
                        @php
                            $stats = [
                                'pending' => $returns->where('status', 'pending')->count(),
                                'approved' => $returns->where('status', 'approved')->count(),
                                'total' => $returns->count(),
                            ];
                        @endphp
                        <div class="col-4">
                            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                                <div class="card-body p-2 text-center">
                                    <small class="text-muted d-block">{{ __('Pending') }}</small>
                                    <h5 class="mb-0 fw-bold text-warning">{{ $stats['pending'] }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                                <div class="card-body p-2 text-center">
                                    <small class="text-muted d-block">{{ __('Approved') }}</small>
                                    <h5 class="mb-0 fw-bold text-success">{{ $stats['approved'] }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-2 text-center">
                                    <small class="text-muted d-block">{{ __('Total') }}</small>
                                    <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-undo-alt me-2 text-primary"></i>
                            {{ __('Returns') }}
                        </h5>
                        <x-table-export-actions table-id="returns-table" filename="returns-table" :excel-label="__('Export Excel')"
                            :pdf-label="__('Export PDF')" :print-label="__('Print')" />
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="returns-table" class="table table-hover mb-0" style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>{{ __('Return Number') }}</th>
                                    <th>{{ __('Client') }}</th>
                                    <th class="text-center">{{ __('Return Date') }}</th>
                                    <th class="text-center">{{ __('Return Type') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Total Amount') }}</th>
                                    <th>{{ __('Created By') }}</th>
                                    @canany(['edit Returns', 'delete Returns'])
                                        <th class="text-center" style="width: 150px;">{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($returns as $return)
                                    <tr class="align-middle">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('returns.show', $return->id) }}"
                                                class="text-decoration-none fw-semibold text-end">
                                                <i class="fas fa-file-alt me-1 text-muted"></i>
                                                {{ $return->return_number }}
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <i class="fas fa-user me-1 text-muted"></i>
                                            {{ $return->client->cname ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                <i class="far fa-calendar me-1"></i>
                                                {{ $return->return_date->format('Y-m-d') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $typeConfig = [
                                                    'refund' => ['color' => 'primary', 'icon' => 'fa-money-bill-wave'],
                                                    'exchange' => ['color' => 'info', 'icon' => 'fa-exchange-alt'],
                                                    'credit_note' => [
                                                        'color' => 'warning',
                                                        'icon' => 'fa-file-invoice',
                                                    ],
                                                ];
                                                $type = $typeConfig[$return->return_type] ?? $typeConfig['refund'];
                                            @endphp
                                            <span class="badge bg-{{ $type['color'] }}">
                                                <i class="fas {{ $type['icon'] }} me-1"></i>
                                                {{ __(ucfirst(str_replace('_', ' ', $return->return_type))) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['color' => 'warning', 'icon' => 'fa-clock'],
                                                    'approved' => ['color' => 'success', 'icon' => 'fa-check-circle'],
                                                    'rejected' => ['color' => 'danger', 'icon' => 'fa-times-circle'],
                                                    'completed' => [
                                                        'color' => 'secondary',
                                                        'icon' => 'fa-flag-checkered',
                                                    ],
                                                ];
                                                $statusConf =
                                                    $statusConfig[$return->status] ?? $statusConfig['pending'];
                                            @endphp
                                            <span class="badge bg-{{ $statusConf['color'] }}">
                                                <i class="fas {{ $statusConf['icon'] }} me-1"></i>
                                                {{ __(ucfirst($return->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <strong
                                                class="text-primary">{{ number_format($return->total_amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-user-tie me-1"></i>
                                                {{ $return->createdBy->name ?? 'N/A' }}
                                            </small>
                                        </td>
                                        @canany(['edit Returns', 'delete Returns'])
                                            <td>
                                                <a class="btn btn-info btn-icon-square-sm text-white"
                                                    href="{{ route('returns.show', $return->id) }}" data-bs-toggle="tooltip"
                                                    title="{{ __('View') }}">
                                                    <i class="las la-eye"></i>
                                                </a>

                                                @can('edit Returns')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('returns.edit', $return->id) }}" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Returns')
                                                    <form action="{{ route('returns.destroy', $return->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this return?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <p class="mt-3 mb-0">{{ __('No returns added yet') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
@endsection
