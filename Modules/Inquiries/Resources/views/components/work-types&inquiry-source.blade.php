<!-- Work Types Section & Inquiry Sources -->
<div class="row mb-4">
    <div class="col-6">
        <div class="card border-info">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-sitemap me-2"></i>
                    {{ __('Hierarchical Work Classification') }}
                </h6>
            </div>
            <div class="card-body">
                <!-- العناصر المختارة -->
                @if (!empty($selectedWorkTypes))
                    <div class="mb-3">
                        <label class="fw-bold">{{ __('Selected Works') }}:</label>
                        @foreach ($selectedWorkTypes as $index => $workType)
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <span>{{ implode(' → ', $workType['path']) }}</span>
                                <button type="button" wire:click="removeWorkType({{ $index }})"
                                    class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Selection الحالي -->
                <div id="path_display" class="mb-3 text-success">
                    @if (!empty($currentWorkPath))
                        <i class="fas fa-route me-1"></i> {{ __('Current Path') }}:
                        {{ implode(' → ', $currentWorkPath) }}
                    @else
                        <i class="fas fa-info-circle me-1"></i> {{ __('Select classification') }}
                    @endif
                </div>

                <div id="steps_wrapper" wire:ignore>
                    <div class="row mb-3" id="work_types_row">
                        <div class="col-md-3" data-step="1">
                            <label class="form-label fw-bold">
                                <span class="badge bg-primary me-2">1</span>
                                {{ __('Main Classification') }}
                            </label>
                            <select wire:model="currentWorkTypeSteps.step_1" id="step_1" class="form-select">
                                <option value="">{{ __('Select main classification...') }}</option>
                                @foreach ($workTypes as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <button type="button" wire:click="addWorkType" class="btn btn-primary mt-2">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add This Classification') }}
                </button>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <label for="final_work_type" class="form-label fw-bold">
                                <i class="fas fa-edit text-success me-2"></i>
                                {{ __('Final Work Description') }}
                            </label>
                            <input type="text" wire:model="finalWorkType" id="final_work_type" class="form-control"
                                placeholder="{{ !empty($selectedWorkPath) ? __('Enter additional work details: ') . end($selectedWorkPath) : __('Enter detailed work description...') }}">
                            @error('finalWorkType')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card border-warning">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-stream me-2"></i>
                    {{ __('Hierarchical Inquiry Sources') }}
                </h6>
                <small class="d-block mt-1">{{ __('Select inquiry source through hierarchical sequence') }}</small>
            </div>
            <div class="card-body">
                <div id="inquiry_sources_path_display" class="mb-3 text-warning">
                    @if (!empty($selectedInquiryPath))
                        <i class="fas fa-route text-warning me-1"></i> {{ __('Selected Path') }}:
                        {{ implode(' → ', $selectedInquiryPath) }}
                    @else
                        <i class="fas fa-info-circle me-1"></i> {{ __('Select source first to see path') }}
                    @endif
                </div>
                <div id="inquiry_sources_steps_wrapper" wire:ignore>
                    <div class="row mb-3" id="inquiry_sources_row">
                        <div class="col-md-3" data-step="1">
                            <label class="form-label fw-bold">
                                <span class="badge bg-warning text-dark me-2">1</span>
                                {{ __('Main Source') }}
                            </label>
                            <select wire:model="inquirySourceSteps.inquiry_source_step_1" id="inquiry_source_step_1"
                                class="form-select">
                                <option value="">{{ __('Select main source...') }}</option>
                                @foreach ($inquirySources as $source)
                                    <option value="{{ $source['id'] }}">
                                        {{ $source['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <label for="final_inquiry_source" class="form-label fw-bold">
                                    <i class="fas fa-edit text-warning me-2"></i>
                                    {{ __('Final Source Description') }}
                                </label>
                                <input type="text" wire:model="finalInquirySource" id="final_inquiry_source"
                                    class="form-control"
                                    placeholder="{{ !empty($selectedInquiryPath) ? __('Enter additional source details: ') . end($selectedInquiryPath) : __('Enter detailed source description...') }}">
                                @error('finalInquirySource')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
