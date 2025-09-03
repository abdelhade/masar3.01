@extends('admin.dashboard')

@section('title', __('general.progress_report') . ' - ' . $project->name)

@section('content')
    <div class="container-fluid">
        <!-- Header (Hidden when printing) -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h2 class="mb-1">{{ __('general.progress_report') }}</h2>
                <h5 class="text-muted">{{ $project->name }} - {{ \Carbon\Carbon::now()->format('d-M-Y') }}</h5>
            </div>
            <div>`
                {{-- <a href="{{ route('projects.progress.export', ['project' => $project->id, 'from_date' => $fromDate, 'to_date' => $toDate]) }}"
                    class="btn btn-success me-2">
                    <i class="fas fa-file-excel me-2"></i>{{ __('general.export_excel') }}
                </a> --}}
                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('general.back') }}
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('projects.progress/state', $project->id) }}"
            class="row g-2 align-items-end mb-4 no-print">
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('general.from_date') }}</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('general.to_date') }}</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate) }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>{{ __('general.filter') }}
                </button>
                <a href="{{ route('projects.progress/state', $project->id) }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-1"></i>{{ __('general.reset') }}
                </a>
            </div>
        </form>

        <!-- Working Zone -->
        <div class="working-zone-display no-print mb-4 p-3 border rounded">
            <i class="fas fa-map-marker-alt me-2"></i>
            <strong>{{ __('general.working_zone') }}:</strong> {{ $project->working_zone ?? __('general.all') }}
        </div>

        <!-- Report Period -->
        <div class="alert alert-info no-print mb-4">
            <i class="fas fa-calendar-alt me-2"></i>
            <strong>{{ __('general.report_period') }}:</strong>
            {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }}
            {{ __('general.to') }}
            {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
        </div>

        <!-- Performance Indicators -->
        <div class="row no-print mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('general.performance_indicators') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-4">
                            <div class="d-flex align-items-center">
                                <span class="key-color me-2" style="background-color: #dcfce7;"></span>
                                <span>{{ __('general.above_expected') }}</span>
                                <i class="fas fa-arrow-up text-success ms-2"></i>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="key-color me-2" style="background-color: #fee2e2;"></span>
                                <span>{{ __('general.below_expected') }}</span>
                                <i class="fas fa-arrow-down text-danger ms-2"></i>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="key-color me-2" style="background-color: #fef3c7;"></span>
                                <span>{{ __('general.equal_expected') }}</span>
                                <i class="fas fa-equals text-warning ms-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Table -->
        <div class="card printable-area">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 progress-table">
                        <thead>
                            <tr class="table-header">
                                <th rowspan="2">SL</th>
                                <th rowspan="2">{{ __('general.activity_description') }}</th>
                                <th rowspan="2">{{ __('general.total_quantity') }}</th>
                                <th rowspan="2">{{ __('general.unit') }}</th>
                                <th colspan="4">{{ __('general.previous') }}</th>
                                <th colspan="4">{{ __('general.current') }}</th>
                                <th colspan="4">{{ __('general.completed') }}</th>
                                <th colspan="2">{{ __('general.remaining') }}</th>
                                <th rowspan="2">{{ __('general.target_completion_date') }}</th>
                            </tr>
                            <tr class="table-header">
                                <th class="prev-planned">{{ __('general.planned_qty') }}</th>
                                <th class="prev-planned-percent">{{ __('general.planned_percent') }}</th>
                                <th class="prev-actual">{{ __('general.actual_qty') }}</th>
                                <th class="prev-actual">{{ __('general.actual_percent') }}</th>

                                <th class="curr-planned">{{ __('general.planned_qty') }}</th>
                                <th class="curr-planned-percent">{{ __('general.planned_percent') }}</th>
                                <th class="curr-actual">{{ __('general.actual_qty') }}</th>
                                <th class="curr-actual">{{ __('general.actual_percent') }}</th>

                                <th class="comp-planned">{{ __('general.planned_qty') }}</th>
                                <th class="comp-planned-percent">{{ __('general.planned_percent') }}</th>
                                <th class="comp-actual">{{ __('general.actual_qty') }}</th>
                                <th class="comp-actual">{{ __('general.actual_percent') }}</th>

                                <th class="remaining-cell">{{ __('general.qty_remaining') }}</th>
                                <th class="remaining-cell">{{ __('general.percent_remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($project->items as $index => $item)
                                @php
                                    // Get progress data for the period from daily progress form
                                    $periodProgress = $item->period_progress ?? 0;

                                    // Get progress before the from_date from daily progress form
                                    $previousProgress = $item->previous_progress ?? 0;

                                    // Total completed until to_date
                                    $totalCompleted = $item->total_completed ?? 0;
                                    $remaining = max($item->total_quantity - $totalCompleted, 0);

                                    // Calculate planned values
                                    $startDate = \Carbon\Carbon::parse($project->start_date)->startOfDay();
                                    $endDate = $project->end_date ? \Carbon\Carbon::parse($project->end_date) : null;
                                    $totalDays = $startDate && $endDate ? $startDate->diffInDays($endDate) : 1;

                                    $fromDateObj = \Carbon\Carbon::parse($fromDate);
                                    $toDateObj = \Carbon\Carbon::parse($toDate);

                                    // Days in the selected period
                                    $daysInPeriod = $fromDateObj->diffInDays($toDateObj) + 1;

                                    // Days from project start to end of period
                                    $daysUntilToDate =
                                        $startDate && $toDateObj
                                            ? $startDate->diffInDays(min($toDateObj, $endDate ?? $toDateObj))
                                            : 0;

                                    // Days from project start to beginning of period
                                    $daysUntilFromDate =
                                        $startDate && $fromDateObj
                                            ? $startDate->diffInDays(max($fromDateObj->copy()->subDay(), $startDate))
                                            : 0;

                                    // Planned quantity for the period
                                    $plannedPeriodQuantity =
                                        $totalDays > 0
                                            ? round(($daysInPeriod / $totalDays) * $item->total_quantity, 2)
                                            : 0;

                                    // Planned quantity until to_date
                                    $plannedTotalQuantity =
                                        $totalDays > 0
                                            ? round(($daysUntilToDate / $totalDays) * $item->total_quantity, 2)
                                            : 0;

                                    // Planned quantity until from_date
                                    $plannedUntilFromDate =
                                        $totalDays > 0
                                            ? round(($daysUntilFromDate / $totalDays) * $item->total_quantity, 2)
                                            : 0;

                                    // Percentages
                                    $periodProgressPercentage =
                                        $item->total_quantity > 0
                                            ? min(round(($periodProgress / $item->total_quantity) * 100, 2), 100)
                                            : 0;

                                    $previousProgressPercentage =
                                        $item->total_quantity > 0
                                            ? min(round(($previousProgress / $item->total_quantity) * 100, 2), 100)
                                            : 0;

                                    $totalCompletedPercentage =
                                        $item->total_quantity > 0
                                            ? min(round(($totalCompleted / $item->total_quantity) * 100, 2), 100)
                                            : 0;

                                    $remainingPercentage =
                                        $item->total_quantity > 0
                                            ? round(($remaining / $item->total_quantity) * 100, 2)
                                            : 0;

                                    $plannedPeriodPercentage =
                                        $item->total_quantity > 0
                                            ? min(round(($plannedPeriodQuantity / $item->total_quantity) * 100, 2), 100)
                                            : 0;

                                    $plannedTotalPercentage =
                                        $item->total_quantity > 0
                                            ? min(round(($plannedTotalQuantity / $item->total_quantity) * 100, 2), 100)
                                            : 0;

                                    $plannedUntilFromDatePercentage =
                                        $item->total_quantity > 0
                                            ? min(round(($plannedUntilFromDate / $item->total_quantity) * 100, 2), 100)
                                            : 0;

                                    // Performance comparison
                                    $periodQtyClass = match (true) {
                                        $periodProgress > $plannedPeriodQuantity => 'above-expected',
                                        $periodProgress < $plannedPeriodQuantity => 'below-expected',
                                        default => 'equal-expected',
                                    };

                                    $periodQtyIcon = match (true) {
                                        $periodProgress > $plannedPeriodQuantity => 'fa-arrow-up',
                                        $periodProgress < $plannedPeriodQuantity => 'fa-arrow-down',
                                        default => 'fa-equals',
                                    };

                                    $periodQtyColor = match (true) {
                                        $periodProgress > $plannedPeriodQuantity => 'text-success',
                                        $periodProgress < $plannedPeriodQuantity => 'text-danger',
                                        default => 'text-warning',
                                    };
                                @endphp

                                <tr>
                                    <td class="sl-cell">{{ $index + 1 }}</td>
                                    <td class="activity-cell">{{ $item->workItem->name }}</td>
                                    <td class="quantity-cell">{{ number_format($item->total_quantity, 2) }}</td>
                                    <td class="unit-cell">{{ strtoupper($item->workItem->unit) }}</td>

                                    <!-- Previous (until from_date) -->
                                    <td class="prev-planned">{{ number_format($plannedUntilFromDate, 2) }}</td>
                                    <td class="prev-planned-percent">
                                        {{ $plannedUntilFromDatePercentage }}%
                                    </td>
                                    <td class="prev-actual">
                                        {{ number_format($previousProgress, 2) }}
                                    </td>
                                    <td class="prev-actual">
                                        {{ $previousProgressPercentage }}%
                                    </td>

                                    <!-- Current (from_date to to_date) -->
                                    <td class="curr-planned">{{ number_format($plannedPeriodQuantity, 2) }}</td>
                                    <td class="curr-planned-percent">{{ $plannedPeriodPercentage }}%</td>
                                    <td class="curr-actual {{ $periodQtyClass }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ number_format($periodProgress, 2) }}
                                            <i class="fas {{ $periodQtyIcon }} {{ $periodQtyColor }} ms-1"></i>
                                        </div>
                                    </td>
                                    <td class="curr-actual {{ $periodQtyClass }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ $periodProgressPercentage }}%
                                            <i class="fas {{ $periodQtyIcon }} {{ $periodQtyColor }} ms-1"></i>
                                        </div>
                                    </td>

                                    <!-- Completed (total until to_date) -->
                                    <td class="comp-planned">{{ number_format($plannedTotalQuantity, 2) }}</td>
                                    <td class="comp-planned-percent">{{ $plannedTotalPercentage }}%</td>
                                    <td class="comp-actual">{{ number_format($totalCompleted, 2) }}</td>
                                    <td class="comp-actual">{{ $totalCompletedPercentage }}%</td>

                                    <!-- Remaining -->
                                    <td class="remaining-cell">{{ number_format($remaining, 2) }}</td>
                                    <td class="remaining-cell">{{ $remainingPercentage }}%</td>

                                    <td class="target-date-cell">
                                        {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d-M-y') : __('general.na') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- User Color Controls -->
        <div class="no-print mb-4 p-3 border rounded">
            <h5>{{ __('general.customize_column_colors') }}</h5>
            <table class="table table-borderless mt-2">
                <tbody>
                    <tr>
                        <td><label>{{ __('general.sl_header') }}</label></td>
                        <td><input type="color" id="slHeaderColor" value="#fde68a"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.activity_header') }}</label></td>
                        <td><input type="color" id="activityHeaderColor" value="#fcd34d"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.total_qty_header') }}</label></td>
                        <td><input type="color" id="totalQtyHeaderColor" value="#f87171"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                    <tr>
                        <td><label>{{ __('general.unit_header') }}</label></td>
                        <td><input type="color" id="unitHeaderColor" value="#34d399"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.previous_header') }}</label></td>
                        <td><input type="color" id="prevHeaderColor" value="#60a5fa"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.current_header') }}</label></td>
                        <td><input type="color" id="currHeaderColor" value="#a78bfa"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                    <tr>
                        <td><label>{{ __('general.completed_header') }}</label></td>
                        <td><input type="color" id="compHeaderColor" value="#fbbf24"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.remaining_header') }}</label></td>
                        <td><input type="color" id="remainingHeaderColor" value="#9ca3af"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.target_date_header') }}</label></td>
                        <td><input type="color" id="targetHeaderColor" value="#f472b6"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                    <tr>
                        <td><label>{{ __('general.sl_column') }}</label></td>
                        <td><input type="color" id="slColor" value="#fef3c7"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.activity_column') }}</label></td>
                        <td><input type="color" id="activityColor" value="#fce7f3"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.total_qty_column') }}</label></td>
                        <td><input type="color" id="totalQtyColor" value="#e0f2fe"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                    <tr>
                        <td><label>{{ __('general.unit_column') }}</label></td>
                        <td><input type="color" id="unitColor" value="#d1fae5"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.previous_planned') }}</label></td>
                        <td><input type="color" id="prevPlannedColor" value="#fef9c3"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.previous_actual') }}</label></td>
                        <td><input type="color" id="prevActualColor" value="#dcfce7"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                    <tr>
                        <td><label>{{ __('general.current_planned') }}</label></td>
                        <td><input type="color" id="currPlannedColor" value="#ede9fe"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.current_actual') }}</label></td>
                        <td><input type="color" id="currActualColor" value="#dbeafe"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.completed_planned') }}</label></td>
                        <td><input type="color" id="compPlannedColor" value="#ffedd5"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                    <tr>
                        <td><label>{{ __('general.completed_actual') }}</label></td>
                        <td><input type="color" id="compActualColor" value="#fbcfe8"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.remaining') }}</label></td>
                        <td><input type="color" id="remainingColor" value="#f3f4f6"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.target_date') }}</label></td>
                        <td><input type="color" id="targetColor" value="#fde68a"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                    <tr>
                        <td><label>{{ __('general.previous_planned_percent') }}</label></td>
                        <td><input type="color" id="prevPlannedPercentColor" value="#fef3c7"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.current_planned_percent') }}</label></td>
                        <td><input type="color" id="currPlannedPercentColor" value="#ede9fe"
                                class="form-control form-control-color p-0"></td>
                        <td><label>{{ __('general.completed_planned_percent') }}</label></td>
                        <td><input type="color" id="compPlannedPercentColor" value="#ffedd5"
                                class="form-control form-control-color p-0"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .progress-table th,
        .progress-table td {
            border: 1px solid #d1d5db;
            text-align: center;
            padding: 8px 6px;
        }

        .progress-table tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .working-zone-display {
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #1e40af;
        }

        .activity-cell {
            text-align: right;
            padding-right: 10px;
        }

        .above-expected {
            background-color: #dcfce7 !important;
        }

        .below-expected {
            background-color: #fee2e2 !important;
        }

        .equal-expected {
            background-color: #fef3c7 !important;
        }

        .key-color {
            width: 20px;
            height: 20px;
            display: inline-block;
            border-radius: 4px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            applyColors();
        });

        function applyColors() {
            const colors = {
                sl: document.getElementById('slColor').value,
                activity: document.getElementById('activityColor').value,
                totalQty: document.getElementById('totalQtyColor').value,
                unit: document.getElementById('unitColor').value,
                prevPlanned: document.getElementById('prevPlannedColor').value,
                prevActual: document.getElementById('prevActualColor').value,
                currPlanned: document.getElementById('currPlannedColor').value,
                currActual: document.getElementById('currActualColor').value,
                compPlanned: document.getElementById('compPlannedColor').value,
                compActual: document.getElementById('compActualColor').value,
                remaining: document.getElementById('remainingColor').value,
                target: document.getElementById('targetColor').value,
                prevPlannedPercent: document.getElementById('prevPlannedPercentColor').value,
                currPlannedPercent: document.getElementById('currPlannedPercentColor').value,
                compPlannedPercent: document.getElementById('compPlannedPercentColor').value
            };
            const headerColors = {
                sl: document.getElementById('slHeaderColor').value,
                activity: document.getElementById('activityHeaderColor').value,
                totalQty: document.getElementById('totalQtyHeaderColor').value,
                unit: document.getElementById('unitHeaderColor').value,
                prev: document.getElementById('prevHeaderColor').value,
                curr: document.getElementById('currHeaderColor').value,
                comp: document.getElementById('compHeaderColor').value,
                remaining: document.getElementById('remainingHeaderColor').value,
                target: document.getElementById('targetHeaderColor').value,
            };

            // Apply colors to thead
            document.querySelectorAll('.progress-table thead tr:first-child th').forEach((th, index) => {
                switch (index) {
                    case 0:
                        th.style.backgroundColor = headerColors.sl;
                        break;
                    case 1:
                        th.style.backgroundColor = headerColors.activity;
                        break;
                    case 2:
                        th.style.backgroundColor = headerColors.totalQty;
                        break;
                    case 3:
                        th.style.backgroundColor = headerColors.unit;
                        break;
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                        th.style.backgroundColor = headerColors.prev;
                        break;
                    case 8:
                    case 9:
                    case 10:
                    case 11:
                        th.style.backgroundColor = headerColors.curr;
                        break;
                    case 12:
                    case 13:
                    case 14:
                    case 15:
                        th.style.backgroundColor = headerColors.comp;
                        break;
                    case 16:
                    case 17:
                        th.style.backgroundColor = headerColors.remaining;
                        break;
                    case 18:
                        th.style.backgroundColor = headerColors.target;
                        break;
                }
            });

            document.querySelectorAll('.sl-cell').forEach(el => el.style.backgroundColor = colors.sl);
            document.querySelectorAll('.activity-cell').forEach(el => el.style.backgroundColor = colors.activity);
            document.querySelectorAll('.quantity-cell').forEach(el => el.style.backgroundColor = colors.totalQty);
            document.querySelectorAll('.unit-cell').forEach(el => el.style.backgroundColor = colors.unit);
            document.querySelectorAll('.prev-planned').forEach(el => el.style.backgroundColor = colors.prevPlanned);
            document.querySelectorAll('.prev-actual').forEach(el => el.style.backgroundColor = colors.prevActual);
            document.querySelectorAll('.curr-planned').forEach(el => el.style.backgroundColor = colors.currPlanned);
            document.querySelectorAll('.curr-actual').forEach(el => el.style.backgroundColor = colors.currActual);
            document.querySelectorAll('.comp-planned').forEach(el => el.style.backgroundColor = colors.compPlanned);
            document.querySelectorAll('.comp-actual').forEach(el => el.style.backgroundColor = colors.compActual);
            document.querySelectorAll('.remaining-cell').forEach(el => el.style.backgroundColor = colors.remaining);
            document.querySelectorAll('.target-date-cell').forEach(el => el.style.backgroundColor = colors.target);
            document.querySelectorAll('.prev-planned-percent').forEach(el => el.style.backgroundColor = colors
                .prevPlannedPercent);
            document.querySelectorAll('.curr-planned-percent').forEach(el => el.style.backgroundColor = colors
                .currPlannedPercent);
            document.querySelectorAll('.comp-planned-percent').forEach(el => el.style.backgroundColor = colors
                .compPlannedPercent);
        }

        document.querySelectorAll(
            '#slHeaderColor,#activityHeaderColor,#totalQtyHeaderColor,#unitHeaderColor,#prevHeaderColor,#currHeaderColor,#compHeaderColor,#remainingHeaderColor,#targetHeaderColor'
        ).forEach(input => input.addEventListener('input', applyColors));
    </script>
@endsection
