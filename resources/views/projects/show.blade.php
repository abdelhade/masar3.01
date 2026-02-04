@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.projects')
@endsection

@section('content')
    @push('styles')
        <style>
            .project-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 15px;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }

            .project-info-card {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                border-left: 4px solid #667eea;
                transition: transform 0.3s ease;
            }

            .project-info-card:hover {
                transform: translateY(-2px);
            }

            .info-item {
                display: flex;
                align-items: center;
                margin-bottom: 0.8rem;
                padding: 0.5rem 0;
            }

            .info-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-left: 1rem;
                font-size: 1.2rem;
                color: white;
            }

            .status-badge {
                padding: 0.5rem 1rem;
                border-radius: 25px;
                font-weight: bold;
                font-size: 0.9rem;
                text-align: center;
                display: inline-block;
                min-width: 120px;
            }

            .status-active {
                background: linear-gradient(45deg, #28a745, #20c997);
                color: white;
            }

            .status-completed {
                background: linear-gradient(45deg, #007bff, #0056b3);
                color: white;
            }

            .status-pending {
                background: linear-gradient(45deg, #ffc107, #e0a800);
                color: #212529;
            }

            .section-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                margin-bottom: 2rem;
                overflow: hidden;
                transition: transform 0.3s ease;
            }

            .section-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            .section-header {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 1.5rem;
                border-bottom: 1px solid #dee2e6;
                display: flex;
                align-items: center;
            }

            .section-icon {
                width: 50px;
                height: 50px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-left: 1rem;
                font-size: 1.5rem;
                color: white;
            }

            .table-custom {
                border-radius: 8px;
                overflow: hidden;
            }

            .table-custom thead th {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 1rem;
                font-weight: 600;
            }

            .table-custom tbody tr {
                transition: background-color 0.3s ease;
            }

            .table-custom tbody tr:hover {
                background-color: #f8f9fa;
            }

            .amount-positive {
                color: #28a745;
                font-weight: bold;
                font-size: 1.1rem;
            }

            .amount-negative {
                color: #dc3545;
                font-weight: bold;
                font-size: 1.1rem;
            }

            .btn-view {
                background: linear-gradient(45deg, #007bff, #0056b3);
                border: none;
                border-radius: 25px;
                padding: 0.5rem 1.5rem;
                color: white;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-view:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
                color: white;
            }

            .summary-row {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                font-weight: bold;
            }

            .summary-row td {
                padding: 1rem;
                font-size: 1.1rem;
            }

            @media (max-width: 768px) {
                .project-header {
                    padding: 1rem;
                }

                .section-card {
                    margin-bottom: 1rem;
                }

                .table-responsive {
                    font-size: 0.9rem;
                }
            }
        </style>
    @endpush

    <div class="project-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-3">
                    <i class="fas fa-project-diagram me-3"></i>
                    {{ __('Project') }} {{ $project->name }}
                </h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ $project->description }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                @if ($project->status == 'active')
                    <span class="status-badge status-active">
                        <i class="fas fa-play-circle me-2"></i>
                        {{ __('Active') }}
                    </span>
                @elseif($project->status == 'completed')
                    <span class="status-badge status-completed">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ __('Completed') }}
                    </span>
                @else
                    <span class="status-badge status-pending">
                        <i class="fas fa-clock me-2"></i>
                        {{ __('Pending') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- بيانات عامة عن المشروع -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="project-info-card">
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(45deg, #28a745, #20c997);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <strong>{{ __('Start Date') }}:</strong>
                            <span class="ms-2">{{ $project->start_date }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(45deg, #ffc107, #e0a800);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <strong>{{ __('Expected End Date') }}:</strong>
                            <span class="ms-2">{{ $project->end_date }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(45deg, #007bff, #0056b3);">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div>
                            <strong>{{ __('Actual End Date') }}:</strong>
                            <span class="ms-2">{{ $project->actual_end_date ?? __('Not Completed Yet') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="project-info-card">
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(45deg, #6f42c1, #5a2d91);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <strong>{{ __('Created By') }}:</strong>
                            <span class="ms-2">{{ $project->createdBy->name ?? '--' }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(45deg, #fd7e14, #e55a00);">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <strong>{{ __('Updated By') }}:</strong>
                            <span class="ms-2">{{ $project->updatedBy->name ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget vs Receipts Comparison -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="project-info-card">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-icon" style="background: linear-gradient(45deg, #17a2b8, #138496);">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div>
                                    <strong>{{ __('Estimated Budget') }}:</strong>
                                    <h5 class="ms-2 mb-0 text-primary">{{ number_format($project->budget, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            @php
                                $totalReceipts = $vouchers->whereIn('pro_type', [1, 32])->sum('pro_value');
                                $totalPayments = $vouchers->whereIn('pro_type', [2, 33, 101])->sum('pro_value');
                                $budgetDiff = $project->budget + $totalReceipts - $totalPayments;
                                $percent = $project->budget > 0 ? ($totalPayments / $project->budget) * 100 : 0;
                            @endphp
                            <div class="info-item">
                                <div class="info-icon" style="background: linear-gradient(45deg, #28a745, #20c997);">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div>
                                    <strong>{{ __('Total Receipts') }}:</strong>
                                    <h5 class="ms-2 mb-0 text-success">{{ number_format($totalReceipts, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-icon"
                                    style="background: {{ $budgetDiff < 0 ? 'linear-gradient(45deg, #dc3545, #c82333)' : 'linear-gradient(45deg, #ffc107, #e0a800)' }};">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                <div>
                                    <strong>{{ $budgetDiff < 0 ? __('Budget Exceeded') : __('Remaining Budget') }}:</strong>
                                    <h5 class="ms-2 mb-0 {{ $budgetDiff < 0 ? 'text-danger' : 'text-warning' }}">
                                        {{ number_format(abs($budgetDiff), 2) }}
                                        <small class="text-muted" style="font-size: 0.8rem;">
                                            ({{ number_format($percent, 1) }}%)
                                        </small>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar {{ $percent > 100 ? 'bg-danger' : 'bg-success' }}" role="progressbar"
                            style="width: {{ min($percent, 100) }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0"
                            aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations -->
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(45deg, #28a745, #20c997);">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5 class="card-title mb-0">{{ __('Operations') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-tasks me-2"></i>{{ __('Operation') }}</th>
                                        <th><i class="fas fa-money-bill-wave me-2"></i>{{ __('Amount') }}</th>
                                        <th><i class="fas fa-calendar me-2"></i>{{ __('Voucher Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($operations as $operation)
                                        <tr>
                                            <td>{{ $operation->type->ptext }}</td>
                                            <td class="amount-positive">{{ number_format($operation->pro_value, 2) }}</td>
                                            <td>{{ $operation->pro_date }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <br>{{ __('No Operations') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(45deg, #fd7e14, #e55a00);">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h5 class="card-title mb-0">{{ __('Equipment') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-tools me-2"></i>{{ __('Equipment') }}</th>
                                        <th><i class="fas fa-calendar-plus me-2"></i>{{ __('Rental Start') }}</th>
                                        <th><i class="fas fa-calendar-minus me-2"></i>{{ __('Rental End') }}</th>
                                        <th><i class="fas fa-money-bill-wave me-2"></i>{{ __('Amount') }}</th>
                                        <th><i class="fas fa-cog me-2"></i>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($equipmentOperations as $equipmentOp)
                                        <tr>
                                            <td>{{ $equipmentOp['equipment']->aname }}</td>
                                            <td>{{ $equipmentOp['operation']->start_date }}</td>
                                            <td>{{ $equipmentOp['operation']->end_date }}</td>
                                            <td class="amount-positive">
                                                {{ number_format($equipmentOp['operation']->pro_value, 2) }}
                                            </td>
                                            <td>
                                                <a href="{{ route('rentals.edit', $equipmentOp['operation']->id) }}"
                                                    class="btn btn-view">
                                                    <i class="fas fa-eye me-1"></i>{{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-truck fa-2x mb-3"></i>
                                                <br>{{ __('No Equipment') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vouchers -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(45deg, #6f42c1, #5a2d91);">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h5 class="card-title mb-0">{{ __('Receipts & Payments') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file-invoice me-2"></i>{{ __('Voucher Type') }}</th>
                                        <th><i class="fas fa-calendar me-2"></i>{{ __('Voucher Date') }}</th>
                                        <th><i class="fas fa-arrow-up text-success me-2"></i>{{ __('Receipts') }}</th>
                                        <th><i class="fas fa-arrow-down text-danger me-2"></i>{{ __('Payments') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($vouchers as $voucher)
                                        <tr>
                                            <td>
                                                @if (in_array($voucher->pro_type, [1, 32]))
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-arrow-up me-1"></i>{{ $voucher->type->ptext }}
                                                    </span>
                                                @elseif(in_array($voucher->pro_type, [2, 33, 101]))
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-arrow-down me-1"></i>{{ $voucher->type->ptext }}
                                                    </span>
                                                @else
                                                    {{ $voucher->type->ptext ?? __('Unspecified') }}
                                                @endif
                                            </td>
                                            <td>{{ $voucher->pro_date }}</td>
                                            <td class="amount-positive">
                                                {{ in_array($voucher->pro_type, [1, 32]) ? number_format($voucher->pro_value, 2) : '0.00' }}
                                            </td>
                                            <td class="amount-negative">
                                                {{ in_array($voucher->pro_type, [2, 33, 101]) ? number_format($voucher->pro_value, 2) : '0.00' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-receipt fa-2x mb-3"></i>
                                                <br>{{ __('No Vouchers') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="summary-row">
                                        <td><i class="fas fa-calculator me-2"></i>{{ __('Total') }}</td>
                                        <td></td>
                                        <td class="amount-positive">
                                            {{ number_format($vouchers->whereIn('pro_type', [1, 32])->sum('pro_value'), 2) }}
                                        </td>
                                        <td class="amount-negative">
                                            {{ number_format($vouchers->whereIn('pro_type', [2, 33, 101])->sum('pro_value'), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
