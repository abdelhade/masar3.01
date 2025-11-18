<div class="row mb-3">
    <div class="col-md-12">
        <div class="card border-primary shadow-sm">
            <div class="card-header ">
                <h6 class="mb-0">
                    <i class="fas fa-users-cog me-2"></i>
                    {{ __('Assigned Engineer To Inquiry') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                    @if (count($availableEngineers) > 0)
                        <div class="row g-2">
                            @foreach ($availableEngineers as $engineer)
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-check p-2 border rounded bg-white hover-shadow"
                                        style="transition: all 0.2s;">
                                        <input class="form-check-input" type="checkbox" value="{{ $engineer['id'] }}"
                                            wire:model.live="selectedEngineers" id="engineer_{{ $engineer['id'] }}">
                                        <label class="form-check-label w-100 cursor-pointer"
                                            for="engineer_{{ $engineer['id'] }}">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-primary me-2"></i>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $engineer['name'] }}</div>
                                                    @if (!empty($engineer['email']))
                                                        <small class="text-muted">{{ $engineer['email'] }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('No Available Engineers') }}</p>
                        </div>
                    @endif
                </div>

                @if (!empty($selectedEngineers) && count($selectedEngineers) > 0)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-primary">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ __('Selected Engineers') }} ({{ count($selectedEngineers) }})
                            </strong>

                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($selectedEngineers as $engId)
                                @php
                                    $eng = collect($availableEngineers)->firstWhere('id', $engId);
                                @endphp
                                @if ($eng)
                                    <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.9rem;">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $eng['name'] }}
                                        <button type="button" class="btn-close btn-close-white ms-2"
                                            style="font-size: 0.65em;" wire:click="removeEngineer({{ $engId }})"
                                            aria-label="{{ __('Remove') }}">
                                        </button>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('No Engineers Selected Yet') }}
                    </div>
                @endif

                @error('selectedEngineers')
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .hover-shadow:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
            transform: translateY(-1px);
        }

        .form-check-input:checked~.form-check-label {
            color: #0d6efd;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
@endpush
