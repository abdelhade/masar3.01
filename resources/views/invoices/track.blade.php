@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.purchases-invoices')
@endsection

@section('content')
    <div class="container-fluid px-4 py-5">
        <div class="timeline-header">
            <h1 class="timeline-title">تتبع مسار الفاتورة</h1>
            <div class="invoice-badge">#{{ $root->pro_id ?? $root->id }}</div>
        </div>

        @php
            $totalStages = count($stages ?? []);
            $completedStages = collect($stages ?? [])
                ->where('status', 'completed')
                ->count();
            $progressPercent = $totalStages > 0 ? floor(($completedStages / $totalStages) * 100) : 0;
        @endphp

        <style>
            /* (style copied from user-provided template) */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            .timeline-header {
                text-align: center;
                margin-bottom: 60px;
                position: relative;
            }

            .timeline-title {
                font-size: 36px;
                font-weight: 700;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                margin-bottom: 15px;
                letter-spacing: -0.5px;
            }

            .invoice-badge {
                display: inline-block;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 8px 24px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 18px;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }

            .timeline {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                position: relative;
                margin: 80px 0;
                padding: 20px 0;
            }

            .timeline::before {
                content: '';
                position: absolute;
                top: 70px;
                left: 5%;
                right: 5%;
                height: 4px;
                background: #e9ecef;
                z-index: 1;
                border-radius: 10px;
            }

            .timeline::after {
                content: '';
                position: absolute;
                top: 70px;
                left: 5%;
                height: 4px;
                width: {{ $progressPercent }}%;
                background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
                z-index: 2;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(40, 167, 69, 0.3);
                transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .timeline-item {
                flex: 1;
                text-align: center;
                position: relative;
                z-index: 3;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .timeline-item .node-wrapper {
                position: relative;
                display: inline-block;
                margin-bottom: 20px;
            }

            .timeline-item .node {
                background: #fff;
                border: 4px solid #dee2e6;
                border-radius: 50%;
                width: 70px;
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: #6c757d;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                z-index: 2;
            }

            .timeline-item .pulse-ring {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 70px;
                height: 70px;
                border: 3px solid #28a745;
                border-radius: 50%;
                opacity: 0;
                z-index: 1;
            }

            .timeline-item.completed .node {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                border-color: #28a745;
                color: #fff;
                box-shadow: 0 6px 25px rgba(40, 167, 69, 0.4);
                transform: scale(1.05);
            }

            .timeline-item.completed .pulse-ring {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @keyframes pulse {
                0% {
                    transform: translate(-50%, -50%) scale(1);
                    opacity: 1;
                }

                100% {
                    transform: translate(-50%, -50%) scale(1.5);
                    opacity: 0;
                }
            }

            .timeline-item.pending .node {
                background: #fff;
                border-color: #007bff;
                color: #007bff;
                animation: bounce 2s ease-in-out infinite;
            }

            @keyframes bounce {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-10px);
                }
            }

            .timeline-item:hover .node {
                transform: scale(1.15) rotate(5deg);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            }

            .timeline-item.completed:hover .node {
                transform: scale(1.2) rotate(5deg);
            }

            .timeline-item .label {
                font-size: 18px;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 15px;
                transition: color 0.3s ease;
            }

            .timeline-item.completed .label {
                color: #28a745;
            }

            .timeline-item.pending .label {
                color: #007bff;
            }

            .timeline-item .details {
                font-size: 14px;
                color: #6c757d;
                background: #fff;
                padding: 20px;
                border-radius: 12px;
                margin: 0 10px;
                min-height: 110px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
                border: 1px solid #f0f0f0;
                transition: all 0.3s ease;
                line-height: 1.8;
            }

            .timeline-item:hover .details {
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
                transform: translateY(-5px);
                border-color: #e0e0e0;
            }

            .timeline-item.completed .details {
                background: linear-gradient(135deg, #f8fff9 0%, #e8f8f0 100%);
                border-color: #28a74533;
            }

            .timeline-item.pending .details {
                background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
                border-color: #007bff33;
            }

            .confirm-btn {
                margin-top: 20px;
            }

            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                padding: 12px 28px;
                font-size: 15px;
                font-weight: 600;
                border-radius: 50px;
                color: #fff;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                position: relative;
                overflow: hidden;
            }

            .btn-primary::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                transition: left 0.5s;
            }

            .btn-primary:hover::before {
                left: 100%;
            }

            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            }

            .btn-primary:active {
                transform: translateY(-1px);
            }

            .btn-primary:disabled {
                background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
                cursor: not-allowed;
                box-shadow: none;
            }

            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                margin-top: 8px;
            }

            .status-badge.completed {
                background: #d4edda;
                color: #155724;
            }

            .status-badge.pending {
                background: #cce5ff;
                color: #004085;
            }

            @media (max-width: 992px) {
                .timeline {
                    flex-direction: column;
                    align-items: stretch;
                    padding-right: 20px;
                }

                .timeline::before {
                    left: 34px;
                    width: 4px;
                    height: 100%;
                    top: 0;
                    background: #e9ecef;
                }

                .timeline::after {
                    left: 34px;
                    width: 4px;
                    height: 31.6%;
                    top: 0;
                    background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
                }

                .timeline-item {
                    text-align: right;
                    margin-bottom: 40px;
                    padding-right: 80px;
                    position: relative;
                }

                .timeline-item .node-wrapper {
                    position: absolute;
                    right: 0;
                    top: 0;
                }

                .timeline-item .node {
                    margin: 0;
                }

                .timeline-item .details {
                    margin: 0;
                    min-height: auto;
                    text-align: right;
                }

                .timeline-item .label {
                    text-align: right;
                }

                .confirm-btn {
                    text-align: right;
                }
            }

            @media (max-width: 576px) {
                .timeline-title {
                    font-size: 28px;
                }

                .invoice-badge {
                    font-size: 16px;
                    padding: 6px 20px;
                }

                .timeline-item .node {
                    width: 60px;
                    height: 60px;
                    font-size: 20px;
                }

                .timeline::before,
                .timeline::after {
                    left: 29px;
                }

                .timeline-item {
                    padding-right: 70px;
                }
            }
        </style>

        <div class="timeline">
            @foreach ($stages as $index => $stage)
                <div class="timeline-item {{ $stage['status'] }}">
                    <div class="node-wrapper">
                        <div class="pulse-ring"></div>
                        <div class="node">
                            <i class="fas {{ $stage['icon'] }}"></i>
                        </div>
                    </div>
                    <div class="label">{{ $stage['name'] }}</div>
                    <div class="details">
                        <div>{!! $stage['details'] !!}</div>
                        <span class="status-badge {{ $stage['status'] }}">
                            {{ $stage['status'] == 'completed' ? '✓ مكتمل' : '⏳ قيد الانتظار' }}
                        </span>
                    </div>

                    {{-- ✅ زر التأكيد --}}
                    @if ($index < count($stages) - 1 && $stage['status'] == 'completed' && $stages[$index + 1]['status'] != 'completed')
                        <form action="{{ route('invoices.confirm', $root->id) }}" method="POST" class="confirm-btn">
                            @csrf
                            <input type="hidden" name="next_stage" value="{{ $index + 2 }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle"></i>
                                تأكيد وإنشاء {{ $stages[$index + 1]['name'] }}
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
