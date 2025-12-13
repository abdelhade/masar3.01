@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.multi-vouchers')
@endsection

@section('content')
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('الرئيسية') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('depreciation.index') }}">{{ __('إدارة الإهلاك') }}</a></li>
                <li class="breadcrumb-item active">{{ __('جدولة الإهلاك') }}</li>
            </ol>
        </nav>

        <!-- Main Content -->
        @livewire('depreciation::depreciation-schedule')
    </div>

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Mint Green Color */
        .bg-mint-green {
            background-color: #a7f3d0 !important;
        }
        
        .text-white {
            color: #ffffff !important;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        /* Ensure all text in mint green cards is white */
        .bg-mint-green * {
            color: #ffffff !important;
        }
        
        .bg-mint-green .text-muted {
            color: #ffffff !important;
        }
        
        .bg-mint-green .text-primary,
        .bg-mint-green .text-success,
        .bg-mint-green .text-warning,
        .bg-mint-green .text-info,
        .bg-mint-green .text-danger,
        .bg-mint-green .text-secondary {
            color: #ffffff !important;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .progress {
            background-color: #e9ecef;
        }
        
        .progress-bar {
            background-color: #007bff;
            color: white;
            font-size: 12px;
            line-height: 20px;
        }
        
        .modal {
            backdrop-filter: blur(3px);
        }
        
        .badge {
            font-size: 0.8em;
        }
    </style>
    @endpush
@endsection