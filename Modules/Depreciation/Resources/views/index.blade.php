@extends('admin.dashboard')

@section('content')
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('الرئيسية') }}</a></li>
                <li class="breadcrumb-item active">{{ __('إدارة إهلاك الأصول') }}</li>
            </ol>
        </nav>

        <!-- Main Content -->
        @livewire('depreciation::depreciation-manager')
    </div>

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
            background-color: #28a745;
            color: white;
            font-size: 12px;
            line-height: 20px;
        }
        
        .modal {
            backdrop-filter: blur(3px);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
    @endpush
@endsection
