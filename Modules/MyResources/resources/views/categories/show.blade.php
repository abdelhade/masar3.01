@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title">{{ __("Category Details") }}: {{ $category->name_ar ?? $category->name }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('myresources.categories.edit', $category) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> {{ __("Edit") }}
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> {{ __("Print") }}
                        </button>
                        <a href="{{ route('myresources.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> {{ __("Back") }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card printable-content">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tag"></i> {{ __("Category Information") }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Name") }}:</label>
                            <div class="form-control-static">{{ $category->name }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __("Arabic Name") }}:</label>
                            <div class="form-control-static">{{ $category->name_ar ?? __("Unspecified") }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Description") }}:</label>
                            <div class="form-control-static">{{ $category->description ?? __("No description") }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __("Icon") }}:</label>
                            <div class="form-control-static">
                                @if($category->icon)
                                    <i class="{{ $category->icon }}"></i> {{ $category->icon }}
                                @else
                                    {{ __("Unspecified") }}
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __("Color") }}:</label>
                            <div class="form-control-static">
                                @if($category->color)
                                    <span class="badge bg-{{ $category->color }}">{{ $category->color }}</span>
                                @else
                                    {{ __("Unspecified") }}
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __("Sort Order") }}:</label>
                            <div class="form-control-static">{{ $category->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">{{ __("Status") }}:</label>
                            <div class="form-control-static">
                                @if($category->is_active)
                                    <span class="badge bg-success">{{ __("Active") }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __("Inactive") }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control-static {
        padding: 0.5rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        min-height: 2.5rem;
        display: flex;
        align-items: center;
    }

    .printable-content {
        page-break-inside: avoid;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .card-header {
            background: #f1f1f1 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-size: 12px;
        }

        .form-control-static {
            background: #fff !important;
            border: 1px solid #000 !important;
        }
    }
</style>
@endpush
@endsection

