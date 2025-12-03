{{-- Other Details Tab --}}
<div>
    <div class="row g-4">
        {{-- Work Covenants Section --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-info text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-file-contract me-2"></i>{{ __('hr.covenants') }}
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $covenants = $viewEmployee->covenants ?? collect();
                    @endphp
                    @if($covenants->count() > 0)
                        <div class="row g-3">
                            @foreach($covenants as $covenant)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100 shadow-sm covenant-card">
                                        <div class="card-body">
                                            {{-- Covenant Image --}}
                                            <div class="text-center mb-3">
                                                @php
                                                    $covenantImage = $covenant->image_url;
                                                    $hasCovenantImage = $covenant->hasMedia('HR_Covenants') && $covenantImage;
                                                @endphp
                                                @if($hasCovenantImage)
                                                    <div class="covenant-image-wrapper position-relative d-inline-block">
                                                        <img src="{{ $covenantImage }}"
                                                             alt="{{ e($covenant->name ?? __('hr.covenants')) }}"
                                                             class="img-fluid rounded border shadow-sm"
                                                             style="max-height: 200px; width: 100%; object-fit: cover; cursor: pointer;"
                                                             loading="lazy"
                                                             @click="previewImageUrl = '{{ $covenantImage }}'; isLightboxVisible = true"
                                                             onerror="this.src='{{ asset('assets/images/placeholder-document.png') }}';">
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded border"
                                                         style="height: 200px;">
                                                        <i class="fas fa-file-image fa-3x text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Covenant Details --}}
                                            <div class="covenant-details row align-items-center justify-content-center">
                                                <div class="mb-2 col-6">
                                                    <label class="form-label fw-bold text-muted small mb-1">
                                                        <i class="fas fa-file-signature me-1 text-info"></i>{{ __('hr.name') }}
                                                    </label>
                                                    <p class="form-control-plaintext mb-0 fw-bold">
                                                        {{ e($covenant->name ?? __('غير محدد')) }}
                                                    </p>
                                                </div>

                                                @if($covenant->description)
                                                    <div class="mb-2 col-6">
                                                        <label class="form-label fw-bold text-muted small mb-1">
                                                            <i class="fas fa-align-right me-1 text-info"></i>{{ __('hr.description') }}
                                                        </label>
                                                        <p class="form-control-plaintext mb-0 text-muted small">
                                                            {{ e($covenant->description) }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('hr.no_covenants_found') }}</h5>
                            <p class="text-muted small">{{ __('hr.no_covenants_for_employee') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

