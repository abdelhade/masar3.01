{{-- Location Information Tab --}}
<div x-show="activeViewTab === 'location'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-info text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-map-marker-alt me-2"></i>{{ __('بيانات الموقع') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-globe me-1 text-info"></i>{{ __('البلد') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->country?->title ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-city me-1 text-info"></i>{{ __('المحافظة') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->state?->title ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-building me-1 text-info"></i>{{ __('المدينة') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->city?->title ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-map-pin me-1 text-info"></i>{{ __('المنطقة') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->town?->title ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

