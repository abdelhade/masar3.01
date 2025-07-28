@extends('admin.dashboard')

@push('styles')
    <style>
        .inventory-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .discrepancy-shortage {
            background-color: #fff5f5;
            border-left: 4px solid #e53e3e;
        }

        .discrepancy-overage {
            background-color: #f0f9ff;
            border-left: 4px solid #3182ce;
        }

        .discrepancy-match {
            background-color: #f0fff4;
            border-left: 4px solid #38a169;
        }

        .badge-shortage {
            background-color: #fed7d7;
            color: #c53030;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-overage {
            background-color: #bee3f8;
            color: #2b6cb0;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-match {
            background-color: #c6f6d5;
            color: #2f855a;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .print-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .print-section {
                box-shadow: none;
                border: none;
            }

            body {
                background: white;
            }
        }

        .quantity-input {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            width: 100px;
            text-align: center;
        }

        .quantity-input:focus {
            border-color: #4299e1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .btn-apply-adjustments {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        .btn-apply-adjustments:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .alert-adjustment {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تقرير جرد الأصناف'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('التقارير'), 'url' => route('reports.index')],
            ['label' => __('تقرير جرد الأصناف')],
        ],
    ])

    <div class="container-fluid">
        <livewire:reports.inventory-discrepancy />
    </div>
@endsection





