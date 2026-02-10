@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Edit Category") }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('myresources.categories.update', $category) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">{{ __("Name") }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name_ar" class="form-label">{{ __("Arabic Name") }} <span class="text-danger">*</span></label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $category->name_ar) }}" required>
                                @error('name_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">{{ __("Description") }}</label>
                                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="icon" class="form-label">{{ __("Icon") }}</label>
                                <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', $category->icon) }}" placeholder="{{ __('Example') }}: fas fa-cogs">
                                <small class="text-muted">{{ __("Use Font Awesome icons") }}</small>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">{{ __("Color") }}</label>
                                <select name="color" id="color" class="form-select @error('color') is-invalid @enderror">
                                    <option value="primary" {{ old('color', $category->color) == 'primary' ? 'selected' : '' }}>{{ __("Blue") }}</option>
                                    <option value="success" {{ old('color', $category->color) == 'success' ? 'selected' : '' }}>{{ __("Green") }}</option>
                                    <option value="info" {{ old('color', $category->color) == 'info' ? 'selected' : '' }}>{{ __("Light") }}</option>
                                    <option value="warning" {{ old('color', $category->color) == 'warning' ? 'selected' : '' }}>{{ __("Yellow") }}</option>
                                    <option value="danger" {{ old('color', $category->color) == 'danger' ? 'selected' : '' }}>{{ __("Red") }}</option>
                                    <option value="secondary" {{ old('color', $category->color) == 'secondary' ? 'selected' : '' }}>{{ __("Gray") }}</option>
                                </select>
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __("Active") }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __("Update") }}
                                </button>
                                <a href="{{ route('myresources.categories.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> {{ __("Cancel") }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

