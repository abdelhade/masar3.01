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
        /* Dark Brown Text Color - Apply to all text */
        div,
        h1, h2, h3, h4, h5, h6,
        p, span, a, label, small, strong,
        th, td, li,
        .text-primary, .text-success, .text-warning, .text-info, 
        .text-danger, .text-muted, .text-secondary, .text-dark, .text-white,
        .card-body, .card-header, .card-title, .modal-title, .modal-body,
        .form-label, .btn, .badge,
        input, select, textarea {
            color: #5D4037 !important; /* Dark brown color */
        }
        
        /* Keep button text readable but maintain dark brown */
        .btn.btn-primary, .btn.btn-success, .btn.btn-warning, 
        .btn.btn-info, .btn.btn-danger, .btn.btn-secondary {
            color: #5D4037 !important;
        }
        
        /* Keep badges readable */
        .badge {
            color: #5D4037 !important;
        }
        
        /* Links should also be dark brown */
        a {
            color: #5D4037 !important;
        }
        
        a:hover {
            color: #3E2723 !important; /* Darker brown on hover */
        }
        
        /* Mint Green Color */
        .bg-mint-green {
            background-color: #a7f3d0 !important;
            color: #5D4037 !important;
        }
        
        .bg-mint-green * {
            color: #5D4037 !important;
        }
        
        .bg-mint-green .text-muted {
            color: #5D4037 !important;
        }
        
        .bg-mint-green .text-primary,
        .bg-mint-green .text-success,
        .bg-mint-green .text-warning,
        .bg-mint-green .text-info,
        .bg-mint-green .text-danger,
        .bg-mint-green .text-secondary {
            color: #5D4037 !important;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
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
            color: #5D4037 !important;
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