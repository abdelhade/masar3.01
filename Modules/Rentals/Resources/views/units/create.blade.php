@extends('admin.dashboard')

@section('content')
    {{-- Breadcrumb for better navigation --}}
    @include('components.breadcrumb', [
        'title' => __('إضافة وحدة جديدة'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('المباني والوحدات'), 'url' => route('rentals.buildings.index')],
            ['label' => $building->name, 'url' => route('rentals.buildings.show', $building->id)],
            ['label' => __('إضافة وحدة')],
        ],
    ])

    <div class="container-fluid px-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    إضافة وحدة جديدة في المبنى: <strong class="text-primary">{{ $building->name }}</strong>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rentals.units.store', $building->id) }}" method="POST">
                    @csrf
                    {{-- Hidden input for the building ID --}}
                    <input type="hidden" name="building_id" value="{{ $building->id }}">

                    <div class="row">

                        {{-- Unit Name --}}
                        <div class="col-md-3 mb-3">
                            <label for="name" class="form-label">اسم/رقم الوحدة <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Area --}}
                        <div class="col-md-3 mb-3">
                            <label for="area" class="form-label">المساحة (م²)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-ruler-combined"></i></span>
                                <input type="number" step="0.01" name="area" id="area"
                                    class="form-control @error('area') is-invalid @enderror" value="{{ old('area') }}">
                            </div>
                            @error('area')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Floor --}}
                        <div class="col-md-3 mb-3">
                            <label for="floor" class="form-label">الطابق <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                <select name="floor" id="floor"
                                    class="form-select @error('floor') is-invalid @enderror" required>
                                    @foreach ($floors as $floor)
                                        <option value="{{ $floor }}" {{ old('floor') == $floor ? 'selected' : '' }}>
                                            {{ $floor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                <select name="status" id="status"
                                    class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach (\Modules\Rentals\Enums\UnitStatus::cases() as $status)
                                        <option value="{{ $status->value }}"
                                            {{ old('status', \Modules\Rentals\Enums\UnitStatus::AVAILABLE->value) == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Details --}}
                        <div class="col-12 mb-3">
                            <label for="details" class="form-label">تفاصيل إضافية</label>
                            <textarea name="details" id="details" class="form-control @error('details') is-invalid @enderror" rows="4">{{ old('details') }}</textarea>
                            @error('details')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer text-end bg-transparent border-top pt-3 pe-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ الوحدة
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
