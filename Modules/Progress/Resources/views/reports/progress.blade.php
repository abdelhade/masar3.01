@extends('progress::layouts.app')

@section('title', __('general.progress_report') . ' - ' . $project->name)

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="mb-1">{{ __('general.progress_report') }}</h2>
            <h5 class="text-muted">{{ $project->name }} - {{ \Carbon\Carbon::now()->format('d-M-Y') }}</h5>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="fas fa-print me-2"></i>{{ __('general.print_report') }}
            </button>
            <a href="{{ route('progress.projects.show', $project) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>{{ __('general.back') }}
            </a>
        </div>
    </div>

    
    <div class="print-header d-none">
        <div class="text-center mb-3">
            <h2 class="mb-1">{{ __('general.progress_report') }}</h2>
            <h3 class="mb-0">{{ $project->name }}</h3>
        </div>
        <div class="d-flex justify-content-between mb-3">
            <div><strong>{{ __('general.date') }}:</strong> {{ \Carbon\Carbon::now()->format('d-M-Y') }}</div>
            <div><strong>{{ __('general.prepared_by') }}:</strong> {{ Auth::user()->name ?? __('general.system') }}</div>
        </div>
        <div class="d-flex justify-content-between">
            <div><strong>{{ __('general.working_zone') }}:</strong> {{ $project->working_zone ?? __('general.all') }}</div>
        </div>
    </div>

    
    <div class="working-zone-display no-print mb-4 p-3 bg-light border rounded">
        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
        <strong>{{ __('general.working_zone') }}:</strong> {{ $project->working_zone ?? __('general.all') }}
    </div>

    
    <div class="card printable-area">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 progress-table">
                    <thead>
                        <tr class="table-header">
                            <th rowspan="2" style="text-align: center;">{{ __('general.sl') }}</th>
                            <th rowspan="2" style="text-align: center;">{{ __('general.activity_description') }}</th>
                            <th rowspan="2" style="text-align: center;">{{ __('general.total_quantity') }}</th>
                            <th rowspan="2" style="text-align: center;">{{ __('general.unit') }}</th>
                            <th colspan="4" style="text-align: center;" class="previous-header">{{ __('general.previous') }}</th>
                            <th colspan="4" style="text-align: center;" class="current-header">{{ __('general.current') }}</th>
                            <th colspan="2" style="text-align: center;" class="completed-header">{{ __('general.completed') }}</th>
                            <th colspan="2" style="text-align: center;" class="remaining-header">{{ __('general.remaining') }}</th>
                            <th rowspan="2" style="text-align: center;">{{ __('general.target_completion_date') }}</th>
                        </tr>
                        <tr class="table-header">
                            
                            <th class="expected-subheader">{{ __('general.planned_qty') }}</th>
                            <th class="expected-subheader">{{ __('general.planned_percentage') }}</th>
                            <th class="previous-subheader">{{ __('general.qty_actual_previous') }}</th>
                            <th class="previous-subheader">{{ __('general.actual_previous_percentage') }}</th>

                            
                            <th class="expected-subheader">{{ __('general.planned_qty') }}</th>
                            <th class="expected-subheader">{{ __('general.planned_percentage') }}</th>
                            <th class="current-subheader">{{ __('general.qty_actual_today') }}</th>
                            <th class="current-subheader">{{ __('general.actual_today_percentage') }}</th>

                            
                            <th class="completed-subheader">{{ __('general.total_qty_completed') }}</th>
                            <th class="completed-subheader">{{ __('general.total_completed_percentage') }}</th>

                            
                            <th class="remaining-subheader">{{ __('general.total_quantity_remaining') }}</th>
                            <th class="remaining-subheader">{{ __('general.total_remaining_percentage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->items as $index => $item)
                            @php
                                // تاريخ اليوم
                                $today = \Carbon\Carbon::today()->format('Y-m-d');

                                // حساب الكميات
                                $previousQuantity = $item->dailyProgress
                                    ->where('progress_date', '<', $today)
                                    ->sum('quantity');

                                $todayQuantity = $item->dailyProgress
                                    ->where('progress_date', $today)
                                    ->sum('quantity');

                                $totalCompleted = $previousQuantity + $todayQuantity;
                                $remaining = max($item->total_quantity - $totalCompleted, 0);

                                // حساب النسب المئوية
                                $previousPercentage = $item->total_quantity > 0
                                    ? round(($previousQuantity / $item->total_quantity) * 100, 2)
                                    : 0;

                                $todayPercentage = $item->total_quantity > 0
                                    ? round(($todayQuantity / $item->total_quantity) * 100, 2)
                                    : 0;

                                $totalCompletedPercentage = $item->total_quantity > 0
                                    ? round(($totalCompleted / $item->total_quantity) * 100, 2)
                                    : 0;

                                $remainingPercentage = $item->total_quantity > 0
                                    ? round(($remaining / $item->total_quantity) * 100, 2)
                                    : 0;

                                // حساب القيم المتوقعة
                                $startDate = \Carbon\Carbon::parse($project->start_date);
                                $endDate = \Carbon\Carbon::parse($project->end_date);
                                $todayDate = \Carbon\Carbon::today();
                                $yesterday = \Carbon\Carbon::yesterday();

                                // التأكد من أن التواريخ صالحة
                                $totalDays = $startDate && $endDate ? $startDate->diffInDays($endDate) : 1;
                                $daysCompleted = $startDate && $yesterday ? $startDate->diffInDays(min($yesterday, $endDate)) : 0;

                                // حساب الكميات المتوقعة
                                $expectedPrevious = $totalDays > 0
                                    ? round(($daysCompleted / $totalDays) * $item->total_quantity, 2)
                                    : 0;

                                $expectedToday = $totalDays > 0
                                    ? round((1 / $totalDays) * $item->total_quantity, 2)
                                    : 0;

                                $expectedPreviousPercentage = $item->total_quantity > 0
                                    ? round(($expectedPrevious / $item->total_quantity) * 100, 2)
                                    : 0;

                                $expectedTodayPercentage = $item->total_quantity > 0
                                    ? round(($expectedToday / $item->total_quantity) * 100, 2)
                                    : 0;

                                // تحديد فئات المقارنة
                                $prevQtyClass = $previousQuantity <=> $expectedPrevious;
                                $todayQtyClass = $todayQuantity <=> $expectedToday;

                                $prevQtyClass = match($prevQtyClass) {
                                    1 => 'above-expected',
                                    -1 => 'below-expected',
                                    default => 'equal-expected'
                                };

                                $todayQtyClass = match($todayQtyClass) {
                                    1 => 'above-expected',
                                    -1 => 'below-expected',
                                    default => 'equal-expected'
                                };
                            @endphp
                            <tr>
                                
                                <td class="sl-cell">{{ $index + 1 }}</td>
                                <td class="activity-cell">{{ $item->workItem->name }}</td>
                                <td class="quantity-cell">{{ number_format($item->total_quantity, 2) }}</td>
                                <td class="unit-cell">{{ strtoupper($item->workItem->unit) }}</td>

                                
                                <td class="expected-cell">{{ number_format($expectedPrevious, 2) }}</td>
                                <td class="expected-cell">{{ $expectedPreviousPercentage }}%</td>
                                <td class="previous-cell {{ $prevQtyClass }}">{{ number_format($previousQuantity, 2) }}</td>
                                <td class="previous-cell {{ $prevQtyClass }}">{{ $previousPercentage }}%</td>

                                
                                <td class="expected-cell">{{ number_format($expectedToday, 2) }}</td>
                                <td class="expected-cell">{{ $expectedTodayPercentage }}%</td>
                                <td class="current-cell {{ $todayQtyClass }}">{{ number_format($todayQuantity, 2) }}</td>
                                <td class="current-cell {{ $todayQtyClass }}">{{ $todayPercentage }}%</td>

                                
                                <td class="completed-cell">{{ number_format($totalCompleted, 2) }}</td>
                                <td class="completed-cell">{{ $totalCompletedPercentage }}%</td>

                                
                                <td class="remaining-cell">{{ number_format($remaining, 2) }}</td>
                                <td class="remaining-cell">{{ $remainingPercentage }}%</td>

                                
                                <td class="date-cell">{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d-M-y') : __('general.na') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="footer-row">
                            <td colspan="15" class="footer-total">{{ __('general.total') }}</td>
                            <td colspan="2" class="footer-executed">{{ __('general.total_executed') }} = {{ number_format($project->items->sum(function($item) {
                                return $item->dailyProgress->sum('quantity');
                            }), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    
    <div class="no-print mt-4">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i>{{ __('general.color_key') }}</h5>
            <div class="d-flex flex-wrap gap-3 mt-3">
                <div class="key-item">
                    <span class="key-color above-expected-key"></span>
                    {{ __('general.above_expected') }}
                </div>
                <div class="key-item">
                    <span class="key-color equal-expected-key"></span>
                    {{ __('general.equal_expected') }}
                </div>
                <div class="key-item">
                    <span class="key-color below-expected-key"></span>
                    {{ __('general.below_expected') }}
                </div>
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
            background: white !important;
            color: black !important;
            font-size: 10pt;
            zoom: 90%;
        }

        .no-print {
            display: none !important;
        }

        .print-header {
            display: block !important;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .printable-area {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .progress-table {
            width: 100% !important;
            font-size: 8pt !important;
            border-collapse: collapse !important;
        }

        .progress-table th,
        .progress-table td {
            padding: 4px 3px !important;
            border: 1px solid #ddd !important;
            text-align: center;
        }

        
        .table-header,
        .previous-header,
        .current-header,
        .completed-header,
        .remaining-header {
            background: #f0f0f0 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        
        .previous-subheader { background: #e6f7ed !important; }
        .current-subheader { background: #e6f0ff !important; }
        .completed-subheader { background: #ffeedd !important; }
        .remaining-subheader { background: #f0f0f0 !important; }
        .expected-subheader { background: #e6e6fa !important; }

        
        .sl-cell, .zone-cell { background: #f8f9fa !important; }
        .previous-cell { background: #e6f7ed !important; }
        .current-cell { background: #e6f0ff !important; }
        .completed-cell { background: #ffeedd !important; }
        .remaining-cell { background: #f0f0f0 !important; }
        .expected-cell { background: #e6e6fa !important; }

        
        .above-expected { background: #d1fae5 !important; color: #065f46 !important; }
        .below-expected { background: #fee2e2 !important; color: #b91c1c !important; }
        .equal-expected { background: #fef3c7 !important; color: #92400e !important; }

        
        .footer-row { background: #f8f9fa !important; }
        .footer-total, .footer-executed { font-weight: bold; }

        
        .progress-table tbody tr:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        
        tr { page-break-inside: avoid; }
    }

    
    .progress-table {
        font-size: 12px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .progress-table th, .progress-table td {
        padding: 8px 6px;
        vertical-align: middle;
        border: 1px solid #d1d5db;
        text-align: center;
        font-weight: 500;
    }

    
    .table-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: white;
        font-weight: bold;
        font-size: 13px;
    }

    
    .previous-header {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
    }

    .current-header {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .completed-header {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
    }

    .remaining-header {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
    }

    .expected-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }

    .previous-subheader {
        background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
        color: #166534;
    }

    .current-subheader {
        background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
        color: #1e40af;
    }

    .completed-subheader {
        background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
        color: #9a3412;
    }

    .remaining-subheader {
        background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
        color: #374151;
    }

    .expected-subheader {
        background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
        color: #5b21b6;
    }

    
    .sl-cell, .zone-cell {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        font-weight: bold;
    }

    .activity-cell {
        background: white;
        text-align: right;
        padding-right: 10px !important;
    }

    .previous-cell {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
    }

    .current-cell {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }

    .completed-cell {
        background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
        color: #9a3412;
    }

    .remaining-cell {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #374151;
    }

    .expected-cell {
        background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        color: #5b21b6;
    }

    
    .above-expected {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        font-weight: bold;
        position: relative;
    }

    .above-expected::after {
        content: "↑";
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #065f46;
    }

    .below-expected {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
        font-weight: bold;
        position: relative;
    }

    .below-expected::after {
        content: "↓";
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #b91c1c;
    }

    .equal-expected {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        font-weight: bold;
        position: relative;
    }

    .equal-expected::after {
        content: "=";
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #92400e;
    }

    
    .footer-row {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        font-weight: bold;
    }

    
    .working-zone-display {
        background-color: #e0f2fe;
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        color: #1e40af;
        border-left: 4px solid #1e40af;
    }

    
    .key-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .key-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        display: inline-block;
    }

    .above-expected-key {
        background-color: #d1fae5;
        border: 1px solid #065f46;
    }

    .below-expected-key {
        background-color: #fee2e2;
        border: 1px solid #b91c1c;
    }

    .equal-expected-key {
        background-color: #fef3c7;
        border: 1px solid #92400e;
    }

    
    .progress-table tbody tr:hover {
        transform: scale(1.01);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
</style>

<script>
    // Print optimization
    document.addEventListener('DOMContentLoaded', function() {
        // Set print header content
        const printHeader = document.querySelector('.print-header');
        printHeader.innerHTML = `
            <div class="text-center mb-3">
                <h2 class="mb-1">{{ __('general.progress_report') }}</h2>
                <h3 class="mb-0">{{ $project->name }}</h3>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <div><strong>{{ __('general.date') }}:</strong> ${new Date().toLocaleDateString('ar-EG', {day: 'numeric', month: 'long', year: 'numeric'})}</div>
                <div><strong>{{ __('general.prepared_by') }}:</strong> {{ Auth::user()->name ?? __('general.system') }}</div>
            </div>
            <div class="d-flex justify-content-between">
                <div><strong>{{ __('general.working_zone') }}:</strong> {{ $project->working_zone ?? __('general.all') }}</div>
            </div>
        `;
    });

    // Print event handlers
    window.addEventListener('beforeprint', () => {
        document.querySelector('.print-header').classList.remove('d-none');
    });

    window.addEventListener('afterprint', () => {
        document.querySelector('.print-header').classList.add('d-none');
    });
</script>
@endsection
