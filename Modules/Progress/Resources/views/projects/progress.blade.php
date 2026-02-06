@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.projects.index') }}" class="text-muted text-decoration-none">
            {{ __('general.projects') }}
        </a>
    </li>
@endsection
@section('title', __('general.progress_report') . ' - ' . $project->name)

@section('content')
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h2 class="mb-1">{{ __('general.progress_report') }}</h2>
                <h5 class="text-muted">
                    {{ $project->name }} - 
                    @if(isset($asOfDate) && $asOfDate != \Carbon\Carbon::today()->format('Y-m-d'))
                        عرض التقدم حتى: {{ \Carbon\Carbon::parse($asOfDate)->format('d-M-Y') }}
                    @else
                        {{ \Carbon\Carbon::now()->format('d-M-Y') }}
                    @endif
                </h5>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-info me-2">
                    <i class="fas fa-print me-2"></i>{{ __('general.print') }}
                </button>
                <a href="{{ route('progress.projects.progress.export', ['project' => $project->id, 'from_date' => $fromDate, 'to_date' => $toDate, 'as_of_date' => request('as_of_date', \Carbon\Carbon::today()->format('Y-m-d'))]) }}"
                    class="btn btn-success me-2">
                    <i class="fas fa-file-excel me-2"></i>{{ __('general.export_excel') }}
                </a>
                <a href="{{ route('progress.projects.show', $project) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('general.back') }}
                </a>
            </div>
        </div>

        
        <form method="GET" action="{{ route('progress.projects.progress', $project->id) }}"
            class="row g-3 align-items-end mb-4 no-print">
            
            
            <input type="hidden" name="from_date" value="{{ request('from_date', $fromDate) }}">
            <input type="hidden" name="to_date" value="{{ request('to_date', $toDate) }}">

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">
                    <i class="fas fa-calendar-alt me-1"></i>عرض التقدم حتى تاريخ
                </label>
                <input type="date" name="as_of_date" class="form-control" 
                       value="{{ request('as_of_date', \Carbon\Carbon::today()->format('Y-m-d')) }}"
                       title="اختر التاريخ الذي تريد عرض التقدم عنده (افتراضي: اليوم)">
            </div>

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">{{ __('general.category') }}</label>
                <select name="category_id" class="form-select">
                    <option value="">{{ __('general.all') }}</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">المشروع الفرعي</label>
                <select name="subproject_name" class="form-select">
                    <option value="">{{ __('general.all') }}</option>
                    @foreach($subprojects ?? [] as $subproject)
                        <option value="{{ $subproject->name }}" {{ request('subproject_name') == $subproject->name ? 'selected' : '' }}>
                            {{ $subproject->name }}
                        </option>
                    @endforeach
                    <option value="null" {{ request('subproject_name') == 'null' ? 'selected' : '' }}>بدون مشروع فرعي</option>
                </select>
            </div>

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">{{ __('start_date') }} من</label>
                <input type="date" name="start_date_from" class="form-control" 
                       value="{{ request('start_date_from') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">{{ __('start_date') }} إلى</label>
                <input type="date" name="start_date_to" class="form-control" 
                       value="{{ request('start_date_to') }}">
            </div>

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">{{ __('end_date') }} من</label>
                <input type="date" name="end_date_from" class="form-control" 
                       value="{{ request('end_date_from') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">{{ __('end_date') }} إلى</label>
                <input type="date" name="end_date_to" class="form-control" 
                       value="{{ request('end_date_to') }}">
            </div>

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">أداء المتبقي</label>
                <select name="remaining_performance" class="form-select">
                    <option value="">{{ __('general.all') }}</option>
                    <option value="below" {{ request('remaining_performance') == 'above' ? 'selected' : '' }}>أقل من المتوقع</option>
                    <option value="equal" {{ request('remaining_performance') == 'equal' ? 'selected' : '' }}>متساوي</option>
                    <option value="above" {{ request('remaining_performance') == 'below' ? 'selected' : '' }}>أكبر من المتوقع</option>
                </select>
            </div>

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">{{ __('general.completed') }}</label>
                <select name="completed_status" class="form-select">
                    <option value="">{{ __('general.all') }}</option>
                    <option value="completed" {{ request('completed_status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                    <option value="in_progress" {{ request('completed_status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                    <option value="not_started" {{ request('completed_status') == 'not_started' ? 'selected' : '' }}>لم يبدأ</option>
                </select>
            </div>

            
            <div class="col-md-2">
                <label class="form-label fw-semibold">{{ __('general.search') }}</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="{{ __('general.item_name') }}" 
                       value="{{ request('search') }}">
            </div>

            
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>{{ __('general.filter') }}
                </button>
                <a href="{{ route('progress.projects.progress', $project->id) }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-1"></i>{{ __('general.reset') }}
                </a>
            </div>
        </form>

        
        <div class="mb-3 no-print">
            <button type="button" id="toggleSubprojectNotes" class="btn btn-outline-info">
                <i class="fas fa-eye me-1"></i>Show items by subproject and notes
            </button>
        </div>



  

        
        <div class="print-only mb-3">
            <h3 class="text-center mb-3">{{ $project->name }}</h3>
        </div>



        
        <div class="card printable-area">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 progress-table">
                        <thead>
                            <tr class="table-header">
                                <th rowspan="2" class="sl-header">SL</th>
                                <th rowspan="2" class="activity-header">{{ __('general.activity_description') }}</th>
                                <th rowspan="2" class="totalQty-header">{{ __('general.total_quantity') }}</th>
                                <th rowspan="2" class="unit-header">{{ __('general.unit') }}</th>
                                <th colspan="4" class="prev-header">{{ __('general.previous') }}</th>
                                <th colspan="4" class="curr-header">
                                    {{ __('general.current') }}
                                    @if ($isTodayHoliday ?? false)
                                        <span class="badge bg-danger ms-2" style="font-size: 0.75rem;">OFF</span>
                                    @endif
                                </th>
                                <th colspan="4" class="comp-header">{{ __('general.completed') }}</th>
                                <th colspan="4" class="remaining-header">{{ __('general.remaining') }}</th>
                                <th rowspan="2" class="start-date-header">{{ __('start_date') }}</th>
                                <th rowspan="2" class="target-header">{{ __('end_date') }}</th>
                                <th rowspan="2" class="progress-start-date-header">Progress Start Date</th>
                                <th rowspan="2" class="progress-end-date-header">Progress End Date</th>
                            </tr>
                            <tr class="table-header">
                                <th class="planned-cell">{{ __('general.planned_qty') }}</th>
                                <th class="planned-cell">{{ __('general.planned_percent') }}</th>
                                <th class="actual-cell">{{ __('general.actual_qty') }}</th>
                                <th class="actual-cell">{{ __('general.actual_percent') }}</th>

                                <th class="planned-cell">{{ __('general.planned_qty') }}</th>
                                <th class="planned-cell">{{ __('general.planned_percent') }}</th>
                                <th class="actual-cell">{{ __('general.actual_qty') }}</th>
                                <th class="actual-cell">{{ __('general.actual_percent') }}</th>

                                <th class="planned-cell">{{ __('general.planned_qty') }}</th>
                                <th class="planned-cell">{{ __('general.planned_percent') }}</th>
                                <th class="actual-cell">{{ __('general.actual_qty') }}</th>
                                <th class="actual-cell">{{ __('general.actual_percent') }}</th>

                                <th class="planned-cell">{{ __('general.planned_qty') }}</th>
                                <th class="planned-cell">{{ __('general.planned_percent') }}</th>
                                <th class="remaining-cell">{{ __('general.qty_remaining') }}</th>
                                <th class="remaining-cell">{{ __('general.percent_remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($project->items as $index => $item)
                                <tr>
                                    <td class="sl-cell">{{ $index + 1 }}</td>
                                    <td class="activity-cell">
                                        <div class="item-name">{{ $item->workItem->name }}</div>
                                        @if(!empty($item->subproject_name) || !empty($item->notes))
                                            <div class="item-extra-info" style="display: none;">
                                                @if(!empty($item->subproject_name))
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-sitemap me-1"></i>{{ $item->subproject_name }}
                                                    </small>
                                                @endif
                                                @if(!empty($item->notes))
                                                    <small class="text-info d-block">
                                                        <i class="fas fa-sticky-note me-1"></i>{{ $item->notes }}
                                                    </small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="quantity-cell">{{ number_format($item->total_quantity, 2) }}</td>
                                    <td class="unit-cell">{{ strtoupper($item->workItem->unit) }}</td>

                                    
                                    <td class="prev-planned planned-cell">{{ number_format($item->planned_until_from_date, 2) }}</td>
                                    <td class="prev-planned-percent planned-cell">{{ $item->planned_until_from_date_percentage }}%</td>
                                    <td class="prev-actual actual-cell {{ $item->previous_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ number_format($item->previous_progress, 2) }}
                                            @if ($item->planned_until_from_date > 0)
                                                <i class="fas {{ $item->previous_qty_icon }} {{ $item->previous_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="prev-actual actual-cell {{ $item->previous_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ $item->previous_progress_percentage }}%
                                            @if ($item->planned_until_from_date > 0)
                                                <i class="fas {{ $item->previous_qty_icon }} {{ $item->previous_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>

                                    
                                    <td class="curr-planned planned-cell">{{ number_format($item->planned_today, 2) }}</td>
                                    <td class="curr-planned-percent planned-cell">{{ $item->planned_today_percentage }}%</td>
                                    <td class="curr-actual actual-cell {{ $item->current_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ number_format($item->current_progress, 2) }}
                                            @if ($item->planned_today > 0)
                                                <i class="fas {{ $item->current_qty_icon }} {{ $item->current_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="curr-actual actual-cell {{ $item->current_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ $item->current_progress_percentage }}%
                                            @if ($item->planned_today > 0)
                                                <i class="fas {{ $item->current_qty_icon }} {{ $item->current_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>

                                    
                                    <td class="comp-planned planned-cell">{{ number_format($item->planned_total_quantity, 2) }}</td>
                                    <td class="comp-planned-percent planned-cell">{{ $item->planned_total_percentage }}%</td>
                                    <td class="comp-actual actual-cell {{ $item->completed_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ number_format($item->total_completed, 2) }}
                                            @if ($item->planned_total_quantity > 0)
                                                <i class="fas {{ $item->completed_qty_icon }} {{ $item->completed_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="comp-actual actual-cell {{ $item->completed_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            {{ $item->total_completed_percentage }}%
                                            @if ($item->planned_total_quantity > 0)
                                                <i class="fas {{ $item->completed_qty_icon }} {{ $item->completed_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>

                                    
                                    <td class="remaining-planned planned-cell">
                                        <span class="{{ $item->remaining_planned < 0 ? 'text-danger fw-bold' : '' }}">
                                            {{ number_format($item->remaining_planned ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="remaining-planned-percent planned-cell">
                                        <span class="{{ $item->remaining_planned_percentage < 0 ? 'text-danger fw-bold' : '' }}">
                                            {{ $item->remaining_planned_percentage ?? 0 }}%
                                        </span>
                                    </td>
                                    <td class="remaining-cell {{ $item->remaining_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="{{ $item->remaining < 0 ? 'text-danger fw-bold' : '' }}">
                                                {{ number_format($item->remaining, 2) }}
                                            </span>
                                            @if ($item->remaining_planned > 0)
                                                <i class="fas {{ $item->remaining_qty_icon }} {{ $item->remaining_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="remaining-cell {{ $item->remaining_qty_class }}">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="{{ $item->remaining_percentage < 0 ? 'text-danger fw-bold' : '' }}">
                                                {{ $item->remaining_percentage }}%
                                            </span>
                                            @if ($item->remaining_planned > 0)
                                                <i class="fas {{ $item->remaining_qty_icon }} {{ $item->remaining_qty_color }} ms-1"></i>
                                            @endif
                                        </div>
                                    </td>

                                    
                                    <td class="start-date-cell">
                                        @if ($item->start_date)
                                            {{ \Carbon\Carbon::parse($item->start_date)->format('d-M-y') }}
                                        @else
                                            {{ __('general.na') }}
                                        @endif
                                    </td>

                                    
                                    <td class="target-date-cell">
                                        @if ($item->end_date)
                                            {{ \Carbon\Carbon::parse($item->end_date)->format('d-M-y') }}
                                        @elseif($item->planned_end_date)
                                            {{ \Carbon\Carbon::parse($item->planned_end_date)->format('d-M-y') }}
                                        @else
                                            {{ __('general.na') }}
                                        @endif
                                    </td>

                                    
                                    <td class="progress-start-date-cell">
                                        @if ($item->progress_start_date)
                                            {{ \Carbon\Carbon::parse($item->progress_start_date)->format('d-M-y') }}
                                        @else
                                            {{ __('general.na') }}
                                        @endif
                                    </td>

                                    
                                    <td class="progress-end-date-cell">
                                        @if ($item->progress_end_date)
                                            {{ \Carbon\Carbon::parse($item->progress_end_date)->format('d-M-y') }}
                                        @else
                                            {{ __('general.na') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="no-print mb-4 p-3 border rounded">
            <h5>{{ __('general.customize_column_colors') }}</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">{{ __('general.header_colors') }}</h6>
                    <table class="table table-borderless table-sm">
                        <tbody>
                            <tr>
                                <td><label>{{ __('general.sl_header') }}</label></td>
                                <td><input type="color" id="slHeaderColor" value="#fde68a"
                                        class="form-control form-control-color p-0"></td>
                                <td><label>{{ __('general.activity_header') }}</label></td>
                                <td><input type="color" id="activityHeaderColor" value="#fcd34d"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
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
                            </tr>
                            <tr>
                                <td><label>{{ __('general.start_date') }}</label></td>
                                <td><input type="color" id="startDateHeaderColor" value="#34d399"
                                        class="form-control form-control-color p-0"></td>
                                <td><label>{{ __('general.target_header') }}</label></td>
                                <td><input type="color" id="targetHeaderColor" value="#f472b6"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
                                <td><label>Progress Start Date</label></td>
                                <td><input type="color" id="progressStartDateHeaderColor" value="#10b981"
                                        class="form-control form-control-color p-0"></td>
                                <td><label>Progress End Date</label></td>
                                <td><input type="color" id="progressEndDateHeaderColor" value="#3b82f6"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">{{ __('general.cell_colors') }}</h6>
                    <table class="table table-borderless table-sm">
                        <tbody>
                            <tr>
                                <td><label>{{ __('general.planned_cells') }}</label></td>
                                <td><input type="color" id="plannedColor" value="#fef9c3"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('general.actual_cells') }}</label></td>
                                <td><input type="color" id="actualColor" value="#dcfce7"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('general.sl_cell') }}</label></td>
                                <td><input type="color" id="slColor" value="#fef3c7"
                                        class="form-control form-control-color p-0"></td>
                                <td><label>{{ __('general.activity_cell') }}</label></td>
                                <td><input type="color" id="activityColor" value="#fce7f3"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('general.total_quantity_cell') }}</label></td>
                                <td><input type="color" id="totalQtyColor" value="#e0f2fe"
                                        class="form-control form-control-color p-0"></td>
                                <td><label>{{ __('general.unit_cell') }}</label></td>
                                <td><input type="color" id="unitColor" value="#d1fae5"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('general.remaining_cell') }}</label></td>
                                <td><input type="color" id="remainingColor" value="#f3f4f6"
                                        class="form-control form-control-color p-0"></td>
                                <td><label>{{ __('general.start_date') }}</label></td>
                                <td><input type="color" id="startDateColor" value="#d1fae5"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('general.target_cell') }}</label></td>
                                <td><input type="color" id="targetColor" value="#fde68a"
                                        class="form-control form-control-color p-0"></td>
                                <td><label>Progress Start Date</label></td>
                                <td><input type="color" id="progressStartDateColor" value="#d1fae5"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                            <tr>
                                <td><label>Progress End Date</label></td>
                                <td><input type="color" id="progressEndDateColor" value="#dbeafe"
                                        class="form-control form-control-color p-0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <style>
        
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                margin: 0;
                padding: 0;
                font-size: 8px;
                line-height: 1;
                width: 100%;
            }

            .container-fluid {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .print-only h3 {
                font-size: 14px;
                margin-bottom: 10px;
                font-weight: bold;
            }

            .progress-table {
                width: 100% !important;
                font-size: 6px !important;
                page-break-inside: auto;
                border-collapse: collapse;
                table-layout: auto;
            }

            .progress-table th,
            .progress-table td {
                padding: 2px 1px !important;
                border: 0.5px solid #000 !important;
                text-align: center;
                word-wrap: break-word;
                white-space: normal;
                overflow-wrap: break-word;
            }

            .progress-table thead {
                display: table-header-group;
            }

            .progress-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
                margin: 0;
                padding: 0;
            }

            .card-body {
                padding: 0 !important;
            }

            .printable-area {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .table-responsive {
                overflow: visible !important;
                width: 100%;
            }

            
            .above-expected {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .below-expected {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .equal-expected {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            
            .table-header th {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                font-weight: bold;
            }

            
            .progress-table tbody tr:hover {
                transform: none !important;
                box-shadow: none !important;
            }

            
            tr {
                page-break-inside: avoid;
            }

            
            .activity-cell {
                text-align: center;
                font-size: 6px;
            }

            
            .fas {
                font-size: 5px;
            }
        }

        
        .print-only {
            display: none;
        }

        .progress-table th,
        .progress-table td {
            border: 1px solid #d1d5db;
            text-align: center;
            padding: 8px 6px;
            font-size: 0.85rem;
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
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
            word-wrap: break-word;
            white-space: normal;
            min-width: 150px;
        }

        .activity-cell .item-name {
            font-weight: 500;
        }

        .activity-cell .item-extra-info {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #dee2e6;
        }

        .activity-cell .item-extra-info small {
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .above-expected {
            background-color: #dcfce7 !important;
        }

        .below-expected {
            background-color: #fee2e2 !important;
        }

        

        .key-color {
            width: 20px;
            height: 20px;
            display: inline-block;
            border-radius: 4px;
        }

        
        .table-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        
        .planned-cell {
            background-color: #fef9c3;
        }

        .actual-cell {
            background-color: #dcfce7;
        }

        
        @media (max-width: 992px) {
            .progress-table {
                font-size: 0.75rem;
            }

            .progress-table th,
            .progress-table td {
                padding: 4px 3px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            applyColors();

            // Add event listeners for color changes
            document.querySelectorAll('input[type="color"]').forEach(input => {
                input.addEventListener('input', applyColors);
            });

            // Toggle subproject and notes visibility
            const toggleButton = document.getElementById('toggleSubprojectNotes');
            let isShowing = false;

            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    const extraInfoElements = document.querySelectorAll('.item-extra-info');
                    isShowing = !isShowing;

                    if (isShowing) {
                        extraInfoElements.forEach(el => {
                            if (el.textContent.trim()) {
                                el.style.display = 'block';
                            }
                        });
                        toggleButton.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Hide items by subproject and notes';
                        toggleButton.classList.remove('btn-outline-info');
                        toggleButton.classList.add('btn-info');
                    } else {
                        extraInfoElements.forEach(el => {
                            el.style.display = 'none';
                        });
                        toggleButton.innerHTML = '<i class="fas fa-eye me-1"></i>Show items by subproject and notes';
                        toggleButton.classList.remove('btn-info');
                        toggleButton.classList.add('btn-outline-info');
                    }
                });
            }
        });

        function applyColors() {
            const colors = {
                sl: document.getElementById('slColor')?.value || '#fef3c7',
                activity: document.getElementById('activityColor')?.value || '#fce7f3',
                totalQty: document.getElementById('totalQtyColor')?.value || '#e0f2fe',
                unit: document.getElementById('unitColor')?.value || '#d1fae5',
                planned: document.getElementById('plannedColor').value,
                actual: document.getElementById('actualColor').value,
                remaining: document.getElementById('remainingColor')?.value || '#f3f4f6',
                startDate: document.getElementById('startDateColor')?.value || '#d1fae5',
                target: document.getElementById('targetColor')?.value || '#fde68a',
                progressStartDate: document.getElementById('progressStartDateColor')?.value || '#d1fae5',
                progressEndDate: document.getElementById('progressEndDateColor')?.value || '#dbeafe'
            };

            const headerColors = {
                sl: document.getElementById('slHeaderColor').value,
                activity: document.getElementById('activityHeaderColor').value,
                prev: document.getElementById('prevHeaderColor').value,
                curr: document.getElementById('currHeaderColor').value,
                comp: document.getElementById('compHeaderColor').value,
                remaining: document.getElementById('remainingHeaderColor').value,
                startDate: document.getElementById('startDateHeaderColor').value,
                target: document.getElementById('targetHeaderColor').value,
                progressStartDate: document.getElementById('progressStartDateHeaderColor')?.value || '#10b981',
                progressEndDate: document.getElementById('progressEndDateHeaderColor')?.value || '#3b82f6'
            };

            // Apply header + cell colors by class
            document.querySelectorAll('.sl-cell, .sl-header')
                .forEach(el => el.style.backgroundColor = colors.sl);
            document.querySelectorAll('.activity-cell, .activity-header')
                .forEach(el => el.style.backgroundColor = colors.activity);
            document.querySelectorAll('.quantity-cell, .totalQty-header')
                .forEach(el => el.style.backgroundColor = colors.totalQty);
            document.querySelectorAll('.unit-cell, .unit-header')
                .forEach(el => el.style.backgroundColor = colors.unit);

            document.querySelectorAll('.planned-cell, .planned-header')
                .forEach(el => el.style.backgroundColor = colors.planned);
            document.querySelectorAll('.actual-cell, .actual-header')
                .forEach(el => el.style.backgroundColor = colors.actual);

            document.querySelectorAll('.remaining-cell, .remaining-header')
                .forEach(el => el.style.backgroundColor = colors.remaining);
            document.querySelectorAll('.start-date-cell, .start-date-header')
                .forEach(el => el.style.backgroundColor = colors.startDate);
            document.querySelectorAll('.target-date-cell, .target-header')
                .forEach(el => el.style.backgroundColor = colors.target);
            document.querySelectorAll('.progress-start-date-cell, .progress-start-date-header')
                .forEach(el => el.style.backgroundColor = colors.progressStartDate);
            document.querySelectorAll('.progress-end-date-cell, .progress-end-date-header')
                .forEach(el => el.style.backgroundColor = colors.progressEndDate);

            // Apply header background overrides
            document.querySelectorAll('.prev-header').forEach(el => el.style.backgroundColor = headerColors.prev);
            document.querySelectorAll('.curr-header').forEach(el => el.style.backgroundColor = headerColors.curr);
            document.querySelectorAll('.comp-header').forEach(el => el.style.backgroundColor = headerColors.comp);
            document.querySelectorAll('.start-date-header').forEach(el => el.style.backgroundColor = headerColors.startDate);
            document.querySelectorAll('.progress-start-date-header').forEach(el => el.style.backgroundColor = headerColors.progressStartDate);
            document.querySelectorAll('.progress-end-date-header').forEach(el => el.style.backgroundColor = headerColors.progressEndDate);
        }

        // Print function
        function printReport() {
            window.print();
        }
    </script>
@endsection
