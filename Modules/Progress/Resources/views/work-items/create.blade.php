@extends('progress::layouts.daily-progress')

@section('title', __('general.add_new_work_item'))

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.add_new_work_item'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('general.work_items_management'), 'url' => route('work.items.index')],
            ['label' => __('general.create')],
        ],
    ])

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between py-3">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="las la-plus-circle me-2"></i>
                        {{ __('general.add_new_work_item') }}
                    </h5>
                    <a href="{{ route('work.items.index') }}" class="btn btn-sm btn-light text-primary fw-bold">
                        <i class="las la-arrow-right me-1"></i> {{ __('general.back') }}
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('work.items.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <!-- Item Name -->
                            <div class="col-lg-6">
                                <label class="form-label fw-bold">{{ __('general.item_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="name"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                        value="{{ old('name') }}" placeholder="{{ __('general.item_name') }}" required>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-tag"></i></span>
                                </div>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Unit -->
                            <div class="col-lg-6">
                                <label class="form-label fw-bold">{{ __('general.unit_of_measurement') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="unit"
                                        class="form-control form-control-lg @error('unit') is-invalid @enderror" 
                                        value="{{ old('unit') }}" placeholder="مثال: م3، متر طولي..." required>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-ruler-combined"></i></span>
                                </div>
                                @error('unit')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="col-lg-12">
                                <label class="form-label fw-bold">{{ __('general.category') }}</label>
                                <div class="input-group">
                                    <select name="category_id" class="form-select form-select-lg @error('category_id') is-invalid @enderror">
                                        <option value="">{{ __('general.select_category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-layer-group"></i></span>
                                </div>
                                @error('category_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-lg-12">
                                <label class="form-label fw-bold">{{ __('general.description') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="وصف إضافي للبند...">{{ old('description') }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <a href="{{ route('work.items.index') }}" class="btn btn-outline-secondary btn-lg me-2">
                                <i class="las la-times"></i> {{ __('general.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="las la-save"></i> {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
