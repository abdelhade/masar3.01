@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.work-items.index') }}" class="text-muted text-decoration-none">
            {{ __('general.items') }}
        </a>
    </li>
@endsection
@section('title', __('general.edit_work_item'))

@section('content')
    <style>
        :root {
            --primary-color: #2c7be5;
            --secondary-color: #6c757d;
            --card-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        }

        .main-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .main-card:hover {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 0.75rem 0.75rem 0 0 !important;
            padding: 1.2rem 1.5rem;
            background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%) !important;
            border: none;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #344050;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e3ebf6;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(44, 123, 229, 0.15);
        }

        .input-group-text {
            background-color: #f5f7f9;
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid #e3ebf6;
        }

        .btn-primary {
            background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(44, 123, 229, 0.3);
        }

        .btn-secondary {
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }

        .calculated-field {
            background-color: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }

        .divider {
            border-top: 2px dashed #e5e7eb;
            margin: 2rem 0;
        }
    </style>

    <div class="container">
        <div class="main-card card">
            <div class="card-header text-white">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> {{ __('general.edit_work_item') }}:
                    {{ $workItem->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('progress.work-items.update', $workItem->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('general.item_name') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $workItem->name) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('general.unit_of_measurement') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                <input type="text" name="unit" class="form-control"
                                    value="{{ old('unit', $workItem->unit) }}" required>
                            </div>
                        </div>
                    </div>

                    
                    <div class="mb-4">
                        <label class="form-label">{{ __('general.category') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                            <select name="category_id" class="form-select" required>
                                <option value="">{{ __('general.choose_category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $workItem->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    
                    <div class="mb-4">
                        <label class="form-label">{{ __('general.description') }}</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $workItem->description) }}</textarea>
                    </div>

                    
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> {{ __('general.save_changes') }}
                        </button>
                        <a href="{{ route('progress.work-items.index') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-2"></i> {{ __('general.cancel') }}
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>



@endsection
