@extends('progress::layouts.daily-progress')

@section('title', __('general.edit_item_status'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ __('general.edit_item_status') }}</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('item-statuses.index') }}">{{ __('general.item_statuses') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('general.edit') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 text-white fw-bold">
                    <i class="las la-edit me-2"></i> {{ __('general.edit_item_status') }}: {{ $itemStatus->name }}
                </h5>
                <a href="{{ route('item-statuses.index') }}" class="btn btn-sm btn-light text-primary fw-bold">
                    <i class="las la-arrow-right me-1"></i> {{ __('general.back') }}
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('item-statuses.update', $itemStatus->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                     <!-- Hidden field for is_active checkbox unchecked state behavior -->
                    <input type="hidden" name="is_active" value="0">

                    <div class="row g-4">
                        <!-- Name -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('general.name') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" value="{{ old('name', $itemStatus->name) }}" required>
                                <span class="input-group-text bg-light text-muted"><i class="las la-tag"></i></span>
                            </div>
                             @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Color -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('general.color') }}</label>
                            <div class="input-group">
                                <input type="text" name="color" class="form-control form-control-lg @error('color') is-invalid @enderror" value="{{ old('color', $itemStatus->color) }}" placeholder="{{ __('general.enter_status_color') }}">
                                <span class="input-group-text bg-light text-muted"><i class="las la-palette"></i></span>
                            </div>
                            <small class="text-muted">{{ __('general.color_hint') }}</small>
                            @error('color')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Icon -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('general.icon') }}</label>
                            <div class="input-group">
                                <input type="text" name="icon" class="form-control form-control-lg @error('icon') is-invalid @enderror" value="{{ old('icon', $itemStatus->icon) }}" placeholder="{{ __('general.enter_icon_class') }}">
                                <span class="input-group-text bg-light text-muted"><i class="las la-icons"></i></span>
                            </div>
                             <small class="text-muted">{{ __('general.icon_hint') }}</small>
                             @error('icon')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                         <!-- Description -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('general.description') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ $itemStatus->description }}</textarea>
                        </div>

                        <!-- Order -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('general.order') }}</label>
                            <div class="input-group">
                                <input type="number" name="order" class="form-control form-control-lg" value="{{ $itemStatus->order }}">
                                <span class="input-group-text bg-light text-muted"><i class="las la-sort-amount-up"></i></span>
                            </div>
                            @error('order')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                             <label class="form-label fw-bold d-block">{{ __('general.status') }}</label>
                            <div class="form-check form-switch form-switch-lg d-flex align-items-center p-0 m-0 gap-2">
                                <input class="form-check-input m-0" type="checkbox" role="switch" id="isActive" name="is_active" value="1" {{ $itemStatus->is_active ? 'checked' : '' }} style="width: 3em; height: 1.5em; margin-left: 0;">
                                <label class="form-check-label" for="isActive">{{ __('general.status_active') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                         <a href="{{ route('item-statuses.index') }}" class="btn btn-outline-secondary btn-lg me-2"><i class="las la-times"></i> {{ __('general.cancel') }}</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5"><i class="las la-save"></i> {{ __('general.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
