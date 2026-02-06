@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
@endsection
@section('title', __('activity-logs.activity_logs'))

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-3">
        
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">
                <i class="fas fa-history me-2"></i>{{ __('activity-logs.activity_logs') }}
            </h3>
          
        </div>

        
        <div class="card-body pb-0">
            <form method="GET" action="{{ route('progress.activity-logs.index') }}" id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('activity-logs.log_name') }}</label>
                    <select name="log_name" class="form-select form-select-sm">
                        <option value="">{{ __('activity-logs.all_logs') }}</option>
                        @foreach ($logNames as $logName)
                            <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                {{ $logName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">{{ __('activity-logs.event') }}</label>
                    <select name="event" class="form-select form-select-sm">
                        <option value="">{{ __('activity-logs.all_events') }}</option>
                        @foreach ($events as $event)
                            <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                {{ ucfirst($event) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('activity-logs.subject') }} {{ __('activity-logs.type') }}</label>
                    <select name="subject_type" class="form-select form-select-sm">
                        <option value="">{{ __('activity-logs.all_types') }}</option>
                        @foreach ($subjectTypes as $subjectType)
                            <option value="{{ $subjectType }}" {{ request('subject_type') == $subjectType ? 'selected' : '' }}>
                                {{ class_basename($subjectType) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">{{ __('activity-logs.from_date') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">{{ __('activity-logs.to_date') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                <div class="col-12 d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>{{ __('activity-logs.filter') }}
                    </button>
                    <a href="{{ route('progress.activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i>{{ __('activity-logs.clear') }}
                    </a>
                </div>
            </form>
        </div>

        
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th>#</th>
                        <th>{{ __('activity-logs.description') }}</th>
                        <th>{{ __('activity-logs.event') }}</th>
                        <th>{{ __('activity-logs.causer') }}</th>
                        <th>{{ __('activity-logs.subject') }}</th>
                        <th>{{ __('activity-logs.log_name') }}</th>
                        <th>{{ __('activity-logs.date') }}</th>
                        @can('activity-logs-view')
                            <th class="text-center">{{ __('general.actions') }}</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->id }}</td>
                            <td>{{ $activity->description }}</td>
                            <td>
                                <span class="badge
                                    {{ $activity->event == 'created' ? 'bg-success' :
                                       ($activity->event == 'updated' ? 'bg-warning text-dark' :
                                       ($activity->event == 'deleted' ? 'bg-danger' : 'bg-info')) }}">
                                    {{ ucfirst($activity->event ?? 'custom') }}
                                </span>
                            </td>
                            <td>
                                {{ $activity->causer->name ?? __('activity-logs.system') }}
                            </td>
                            <td>
                                {{ $activity->subject ? class_basename($activity->subject) . ' #' . $activity->subject->id : '-' }}
                            </td>
                            <td><span class="badge bg-secondary">{{ $activity->log_name ?? '-' }}</span></td>
                            <td>
                                <div>{{ $activity->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $activity->created_at->format('H:i:s') }}</small>
                            </td>
                            @can('activity-logs-view')
                            <td class="text-center">
                                <a href="{{ route('progress.activity-logs.show', $activity) }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                {{ __('activity-logs.no_activities_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        
        <div class="card-footer bg-white d-flex justify-content-between">
            <div class="small text-muted">
                {{ __('general.showing') }} {{ $activities->firstItem() ?? 0 }} {{ __('general.to') }} {{ $activities->lastItem() ?? 0 }}
                {{ __('general.of') }} {{ $activities->total() }} {{ __('general.entries') }}
            </div>
            {{ $activities->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@push('styles')
    <style>
        .card-custom {
            box-shadow: 0 0 30px 0 rgba(82,63,105,0.05);
            border: 0;
            border-radius: 0.75rem;
        }

        .bg-hover-light:hover {
            background-color: #f8f9fa !important;
            transition: background-color 0.3s ease;
        }

        .symbol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }

        .symbol.symbol-40 {
            width: 40px;
            height: 40px;
        }

        .symbol-light-primary {
            background-color: #e1f0ff;
        }

        .symbol-light-dark {
            background-color: #e4e6ef;
        }

        .symbol-light-info {
            background-color: #e1f0ff;
        }

        .label {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .label-inline {
            display: inline-flex;
        }

        .label-lg {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .label-light-success {
            background-color: #e8fff3;
            color: #1bc5bd;
        }

        .label-light-warning {
            background-color: #fff4de;
            color: #ffa800;
        }

        .label-light-danger {
            background-color: #ffe2e5;
            color: #f64e60;
        }

        .label-light-primary {
            background-color: #e1f0ff;
            color: #3699ff;
        }

        .label-light-dark {
            background-color: #e4e6ef;
            color: #7e8299;
        }

        .table-head-custom thead th {
            border-bottom: 1px solid #ebedf3;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            color: #7e8299;
            padding: 1rem 0.75rem;
        }

        .table-borderless tbody tr td {
            border: 0;
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }

        .btn-clean {
            background: transparent;
            border: 0;
            padding: 0.5rem;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .btn-hover-light-primary:hover {
            background-color: #e1f0ff;
        }

        .pagination .page-item .page-link {
            border: 0;
            border-radius: 0.75rem;
            margin: 0 0.25rem;
            color: #7e8299;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background-color: #3699ff;
            color: #ffffff;
        }

        .pagination .page-item .page-link:hover {
            background-color: #f8f9fa;
            color: #3699ff;
        }

        .selectpicker {
            border-radius: 0.75rem !important;
        }

        
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .table-head-custom thead {
            background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .table-head-custom thead th {
            border: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 1.25rem 0.75rem;
            position: relative;
        }

        .table-head-custom thead th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background-color: #dee2e6;
        }

        .table-borderless tbody tr {
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s ease;
        }

        .table-borderless tbody tr:last-child {
            border-bottom: 0;
        }

        .table-borderless tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .table-borderless tbody tr td {
            padding: 1.25rem 0.75rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .symbol-label {
            font-weight: 600;
        }

        .label {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .text-hover-primary:hover {
            text-decoration: none;
            transform: translateX(3px);
            transition: all 0.3s ease;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }

        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-state i {
            opacity: 0.7;
        }

        
        @media (max-width: 1200px) {
            .table-responsive {
                font-size: 0.9rem;
            }

            .table-head-custom thead th {
                padding: 1rem 0.5rem;
            }

            .table-borderless tbody tr td {
                padding: 1rem 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                border: 1px solid #e9ecef;
            }

            .d-flex.flex-column {
                text-align: center;
            }

            .symbol {
                margin: 0 auto 0.5rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto-submit form when filters change
            $('#log_name, #event, #subject_type, #date_from, #date_to').change(function() {
                $('#filterForm').submit();
            });

            // Add loading indicator on form submit
            $('#filterForm').on('submit', function() {
                $('.card').append(
                    '<div class="overlay d-flex align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 1000; border-radius: 0.75rem;">' +
                    '<i class="fas fa-2x fa-sync-alt fa-spin text-primary"></i>' +
                    '</div>'
                );
            });

            // Initialize select picker if available
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker();
            }

            // إضافة تأثيرات تفاعلية إضافية
            $('.table-borderless tbody tr').hover(
                function() {
                    $(this).css('transform', 'translateY(-2px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
    </script>
@endpush
