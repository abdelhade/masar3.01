{{-- Location Tab --}}
<div x-show="activeTab === 'location'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient-info text-white py-2">
            <h6 class="card-title mb-0 font-hold fw-bold">
                <i class="fas fa-map-marker-alt me-2"></i>{{ __('الموقع الجغرافي') }}
            </h6>
        </div>
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">{{ __('البلد') }}</label>
                    <select class="form-select" wire:model.defer="country_id">
                        <option value="">{{ __('اختر البلد') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->title }}</option>
                        @endforeach
                    </select>
                    @error('country_id')
                        <div class="text-danger small mt-1">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">{{ __('المحافظة') }}</label>
                    <select class="form-select" wire:model.defer="state_id">
                        <option value="">{{ __('اختر المحافظة') }}</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->id }}">{{ $state->title }}</option>
                        @endforeach
                    </select>
                    @error('state_id')
                        <div class="text-danger small mt-1">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">{{ __('المدينة') }}</label>
                    <select class="form-select" wire:model.defer="city_id">
                        <option value="">{{ __('اختر المدينة') }}</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->title }}</option>
                        @endforeach
                    </select>
                    @error('city_id')
                        <div class="text-danger small mt-1">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">{{ __('المنطقة') }}</label>
                    <select class="form-select" wire:model.defer="town_id">
                        <option value="">{{ __('اختر المنطقة') }}</option>
                        @foreach ($towns as $town)
                            <option value="{{ $town->id }}">{{ $town->title }}</option>
                        @endforeach
                    </select>
                    @error('town_id')
                        <div class="text-danger small mt-1">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

