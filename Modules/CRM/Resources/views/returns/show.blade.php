@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Return Details'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Returns'), 'url' => route('returns.index')],
            ['label' => __('Details')],
        ],
    ])

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Return Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header  bg-opacity-10 border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fw-bold">
                                <i class="fas fa-undo-alt me-2"></i>
                                {{ __('Return') }} #{{ $return->return_number }}
                            </h4>
                            <small class="text-muted">
                                <i class="far fa-calendar me-1"></i>
                                {{ $return->return_date->format('Y-m-d') }}
                            </small>
                        </div>
                        <div class="text-end">
                            @php
                                $typeConfig = [
                                    'refund' => ['color' => 'primary', 'icon' => 'fa-money-bill-wave'],
                                    'exchange' => ['color' => 'info', 'icon' => 'fa-exchange-alt'],
                                    'credit_note' => ['color' => 'warning', 'icon' => 'fa-file-invoice'],
                                ];
                                $type = $typeConfig[$return->return_type] ?? $typeConfig['refund'];
                            @endphp
                            <span class="badge bg-{{ $type['color'] }} fs-6">
                                <i class="fas {{ $type['icon'] }} me-1"></i>
                                {{ __(ucfirst(str_replace('_', ' ', $return->return_type))) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Info Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-user fa-2x text-primary me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Client') }}</small>
                                    <strong>{{ $return->client->cname ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i
                                    class="fas fa-info-circle fa-2x text-{{ $return->status === 'pending' ? 'warning' : ($return->status === 'approved' ? 'success' : 'danger') }} me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Status') }}</small>
                                    <span
                                        class="badge bg-{{ $return->status === 'pending' ? 'warning' : ($return->status === 'approved' ? 'success' : ($return->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                        {{ __(ucfirst($return->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-user-plus fa-2x text-info me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Created By') }}</small>
                                    <strong>{{ $return->createdBy->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-user-check fa-2x text-success me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Approved By') }}</small>
                                    <strong>{{ $return->approvedBy->name ?? __('Not approved yet') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($return->original_invoice_number)
                        <div class="alert alert-info border-0">
                            <i class="fas fa-file-invoice me-2"></i>
                            <strong>{{ __('Original Invoice') }}:</strong> {{ $return->original_invoice_number }}
                            @if ($return->original_invoice_date)
                                <span
                                    class="ms-2 text-muted">({{ $return->original_invoice_date->format('Y-m-d') }})</span>
                            @endif
                        </div>
                    @endif

                    @if ($return->reason)
                        <div class="border-top pt-3 mb-3">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-comment-dots me-2 text-primary"></i>
                                {{ __('Reason') }}
                            </h6>
                            <p class="text-muted">{{ $return->reason }}</p>
                        </div>
                    @endif

                    @if ($return->notes)
                        <div class="border-top pt-3 mb-3">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-sticky-note me-2 text-warning"></i>
                                {{ __('Notes') }}
                            </h6>
                            <p class="text-muted">{{ $return->notes }}</p>
                        </div>
                    @endif

                    @can('edit Returns')
                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('returns.edit', $return) }}" class="btn btn-primary">
                                <i class="las la-edit me-1"></i> {{ __('Edit Return') }}
                            </a>
                            @can('delete Returns')
                                <form action="{{ route('returns.destroy', $return) }}" method="POST"
                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this return?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="las la-trash me-1"></i> {{ __('Delete') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endcan
                </div>
            </div>

            <!-- Items Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-box me-2 text-primary"></i>
                        {{ __('Return Items') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>{{ __('Item') }}</th>
                                    <th class="text-center">{{ __('Quantity') }}</th>
                                    <th class="text-end">{{ __('Unit Price') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                    <th>{{ __('Condition') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($return->items as $item)
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <i class="fas fa-cube me-2 text-muted"></i>
                                            <strong>{{ $item->item->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">
                                            <strong
                                                class="text-primary">{{ number_format($item->total_price, 2) }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $item->item_condition ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>{{ __('Total Amount') }}:</strong></td>
                                    <td class="text-end">
                                        <h5 class="mb-0 text-primary fw-bold">{{ number_format($return->total_amount, 2) }}
                                        </h5>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-tasks me-2 text-primary"></i>
                        {{ __('Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if ($return->status === 'pending')
                        @can('edit Returns')
                            <div class="d-grid gap-2">
                                <form action="{{ route('returns.approve', $return) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="las la-check-circle me-1"></i> {{ __('Approve Return') }}
                                    </button>
                                </form>
                                <form action="{{ route('returns.reject', $return) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="las la-times-circle me-1"></i> {{ __('Reject Return') }}
                                    </button>
                                </form>
                            </div>
                        @endcan
                    @else
                        <div class="alert alert-{{ $return->status === 'approved' ? 'success' : 'danger' }} border-0">
                            <i class="fas fa-{{ $return->status === 'approved' ? 'check' : 'times' }}-circle me-2"></i>
                            {{ __('This return has been') }} <strong>{{ __(strtolower($return->status)) }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }
    </style>
@endsection
