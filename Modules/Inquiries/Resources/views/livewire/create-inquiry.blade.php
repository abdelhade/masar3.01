<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form wire:submit.prevent="save('final')">
                    <div class="card-body">

                        @if ($isDraft)
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>{{ __('Draft Mode') }}</strong> - {{ __('You are editing a draft inquiry.') }}
                                @if ($lastAutoSaveTime)
                                    <br><small>{{ __('Last auto-saved at:') }} {{ $lastAutoSaveTime }}</small>
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="card mb-3 bg-light border-primary">
                            <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap">
                                <div class="form-check form-switch d-flex align-items-center mb-2 mb-sm-0">
                                    <input class="form-check-input me-2" type="checkbox" id="autoSaveToggle"
                                        wire:model.live="autoSaveEnabled">
                                    <label class="form-check-label text-nowrap" for="autoSaveToggle">
                                        <i class="fas fa-sync-alt me-1"></i>
                                        {{ __('Auto-save every 2 minutes') }}
                                    </label>
                                </div>

                                @if ($lastAutoSaveTime)
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        {{ __('Last saved:') }} {{ $lastAutoSaveTime }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <!-- Project Data Section -->
                        @include('inquiries::components.project-data')
                        <!-- Quotation State Section -->
                        @include('inquiries::components.quotation-state')

                        @include('inquiries::components.cities-select')

                        <!-- Work Types Section & Inquiry Sources -->
                        @include('inquiries::components.work-types&inquiry-source')
                    </div>
                    <!-- Stakeholders Section -->
                    @include('inquiries::components.Stakeholders-Section')
                    {{-- بعد حقل المهندس الحالي (Contact Engineer) --}}

                    @include('inquiries::components.assigned-enginner')


                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-dark">
                                <div class="card-header ">
                                    <h5>{{ __('Required Quotation Information') }}</h5>
                                </div>
                                <div class="card-body">
                                    {{-- Quotation Types & Units: Side by Side --}}
                                    <div class="row">
                                        @foreach ($quotationTypes as $type)
                                            <div class="col-md-2 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-header">
                                                        <h6 class="mb-0 text-primary">{{ $type->name }}</h6>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        @forelse ($type->units as $unit)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    wire:model="selectedQuotationUnits.{{ $type->id }}.{{ $unit->id }}"
                                                                    id="quotation_unit_{{ $type->id }}_{{ $unit->id }}">
                                                                <label class="form-check-label small"
                                                                    for="quotation_unit_{{ $type->id }}_{{ $unit->id }}">
                                                                    {{ $unit->name }}
                                                                </label>
                                                            </div>
                                                        @empty
                                                            <p class="text-muted small text-center mb-0">
                                                                {{ __('No units available for this type') }}</p>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <label for="type_note"
                                                class="form-label">{{ __('Type Notes (Optional)') }}</label>
                                            <textarea class="form-control" id="type_note" rows="3" wire:model="type_note"
                                                placeholder="{{ __('Enter any additional notes here...') }}"></textarea>
                                            @error('type_note')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    @error('selectedQuotationUnits')
                                        <div class="alert alert-danger mt-3">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Project Documents Section -->
                        <div class="col-6">
                            <div class="card border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-file-alt me-2"></i>
                                        {{ __('Project Documents') }}
                                    </h6>
                                    <small class="d-block mt-1">{{ __('Select available documents') }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($projectDocuments as $index => $document)
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        wire:model="projectDocuments.{{ $index }}.checked"
                                                        id="document_{{ $index }}" class="form-check-input">
                                                    <label for="document_{{ $index }}" class="form-check-label">
                                                        {{ $document['name'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Required Submittal Checklist & Working Conditions Section -->
                        @include('inquiries::components.submittal-work-condition')
                    </div>

                    <!-- Estimation Information Section -->
                    @include('inquiries::components.estimation-information')

                    <!-- Temporary Comments Section -->
                    @include('inquiries::components.inquiry-comments')

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="{{ route('inquiries.index') }}" class="btn btn-secondary btn-lg">
                                                <i class="fas fa-times me-2"></i>
                                                {{ __('Cancel') }}
                                            </a>
                                        </div>

                                        <div class="btn-group" role="group">
                                            <button type="button" wire:click="save('draft')"
                                                class="btn btn-warning btn-lg">
                                                <i class="fas fa-file-alt me-2"></i>
                                                {{ __('Save as Draft') }}
                                            </button>

                                            <button type="button" wire:click="save" class="btn btn-success btn-lg">
                                                <i class="fas fa-check-circle me-2"></i>
                                                @if ($isDraft)
                                                    {{ __('Publish Inquiry') }}
                                                @else
                                                    {{ __('Save Inquiry') }}
                                                @endif
                                            </button>
                                        </div>
                                    </div>

                                    @if ($isDraft)
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                {{ __('Click "Save as Draft" to save your progress, or "Publish Inquiry" to finalize and publish.') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            // Auto-save functionality
            let autoSaveInterval = null;

            function startAutoSave() {
                if (autoSaveInterval) {
                    clearInterval(autoSaveInterval);
                }

                autoSaveInterval = setInterval(() => {
                    if (@json($autoSaveEnabled)) {
                        @this.call('saveAsDraft');
                    }
                }, 120000); // كل دقيقتين
            }

            // Start auto-save on page load
            if (@json($autoSaveEnabled)) {
                startAutoSave();
            }

            // Listen for auto-save toggle
            Livewire.on('autoSaveToggled', () => {
                if (@json($autoSaveEnabled)) {
                    startAutoSave();
                } else {
                    if (autoSaveInterval) {
                        clearInterval(autoSaveInterval);
                    }
                }
            });

            // Show notification when draft is saved
            Livewire.on('draftSaved', (data) => {
                // يمكن إضافة notification هنا
                console.log('Draft saved successfully!', data);
            });

            // Work Types Hierarchical Selection
            const stepsWrapper = document.getElementById('steps_wrapper');
            const workTypesRow = document.getElementById('work_types_row');

            function createWorkTypeStepItem(stepNum, parentId) {
                Livewire.dispatch('getWorkTypeChildren', {
                    stepNum: stepNum - 1,
                    parentId: parentId
                });
            }

            function removeWorkTypeStepsAfter(stepNum) {
                const stepsToRemove = stepsWrapper.querySelectorAll('[data-step]');
                stepsToRemove.forEach(step => {
                    const stepNumber = parseInt(step.getAttribute('data-step'));
                    if (stepNumber > stepNum) {
                        step.remove();
                    }
                });
            }

            // Listen for workTypeChildrenLoaded
            Livewire.on('workTypeChildrenLoaded', ({
                stepNum,
                children
            }) => {
                if (children.length === 0) {
                    return;
                }

                const nextStepNum = stepNum + 1;
                const existingStep = document.querySelector(`[data-step="${nextStepNum}"]`);

                if (!existingStep) {
                    const stepItem = document.createElement('div');
                    stepItem.className = 'col-md-3';
                    stepItem.setAttribute('data-step', nextStepNum);
                    stepItem.innerHTML = `
                        <label class="form-label fw-bold">
                            <span class="badge bg-primary me-2">${nextStepNum}</span>
                            Classification ${nextStepNum}
                        </label>
                        <select wire:model.live="currentWorkTypeSteps.step_${nextStepNum}" id="step_${nextStepNum}" class="form-select">
                            <option value="">Select step ${nextStepNum}...</option>
                                </select>
                             `;

                    workTypesRow.appendChild(stepItem);

                    const select = document.getElementById(`step_${nextStepNum}`);
                    select.addEventListener('change', function() {
                        const selectedId = this.value;
                        if (selectedId) {
                            removeWorkTypeStepsAfter(nextStepNum);
                            createWorkTypeStepItem(nextStepNum + 1, selectedId);
                        } else {
                            removeWorkTypeStepsAfter(nextStepNum);
                        }
                    });
                }

                const select = document.getElementById(`step_${nextStepNum}`);
                if (select) {
                    select.innerHTML = `<option value="">Select step ${nextStepNum}...</option>`;
                    children.forEach(item => {
                        select.add(new Option(item.name, item.id));
                    });
                }
            });

            // Inquiry Sources Hierarchical Selection
            const inquiryStepsWrapper = document.getElementById('inquiry_sources_steps_wrapper');
            const inquirySourcesRow = document.getElementById('inquiry_sources_row');

            function createInquirySourceStepItem(stepNum, parentId) {
                Livewire.dispatch('getInquirySourceChildren', {
                    stepNum: stepNum - 1,
                    parentId: parentId
                });
            }

            function removeInquirySourceStepsAfter(stepNum) {
                const stepsToRemove = inquiryStepsWrapper.querySelectorAll('[data-step]');
                stepsToRemove.forEach(step => {
                    const stepNumber = parseInt(step.getAttribute('data-step'));
                    if (stepNumber > stepNum) {
                        step.remove();
                    }
                });
            }
            Livewire.on('workTypeAdded', () => {
                const step1Select = document.getElementById('step_1');
                step1Select.value = '';
                removeWorkTypeStepsAfter(1);
                Livewire.dispatch('updateCurrentWorkPath', {
                    path: []
                });
            });

            Livewire.on('inquirySourceChildrenLoaded', ({
                stepNum,
                children
            }) => {
                if (children.length === 0) {
                    return;
                }

                const nextStepNum = stepNum + 1;
                const existingStep = document.querySelector(
                    `#inquiry_sources_row [data-step="${nextStepNum}"]`);

                if (!existingStep) {
                    const stepItem = document.createElement('div');
                    stepItem.className = 'col-md-3';
                    stepItem.setAttribute('data-step', nextStepNum);
                    stepItem.innerHTML = `
                        <label class="form-label fw-bold">
                            <span class="badge bg-warning text-dark me-2">${nextStepNum}</span>
                            المصدر ${nextStepNum}
                        </label>
                        <select wire:model.live="inquirySourceSteps.inquiry_source_step_${nextStepNum}" id="inquiry_source_step_${nextStepNum}" class="form-select">
                            <option value="">Select step ${nextStepNum}...</option>
                        </select>
                    `;

                    inquirySourcesRow.appendChild(stepItem);

                    const select = document.getElementById(`inquiry_source_step_${nextStepNum}`);
                    select.addEventListener('change', function() {
                        const selectedId = this.value;
                        if (selectedId) {
                            removeInquirySourceStepsAfter(nextStepNum);
                            createInquirySourceStepItem(nextStepNum + 1, selectedId);
                        } else {
                            removeInquirySourceStepsAfter(nextStepNum);
                        }
                    });
                }

                const select = document.getElementById(`inquiry_source_step_${nextStepNum}`);
                if (select) {
                    select.innerHTML = `<option value="">Select step ${nextStepNum}...</option>`;
                    children.forEach(item => {
                        select.add(new Option(item.name, item.id));
                    });
                }
            });

            // Handle step_1 change
            document.getElementById('step_1').addEventListener('change', function() {
                const selectedId = this.value;
                removeWorkTypeStepsAfter(1);
                if (selectedId) {
                    createWorkTypeStepItem(2, selectedId);
                }
            });

            // Handle inquiry_source_step_1 change
            document.getElementById('inquiry_source_step_1').addEventListener('change', function() {
                const selectedId = this.value;
                removeInquirySourceStepsAfter(1);
                if (selectedId) {
                    createInquirySourceStepItem(2, selectedId);
                }
            });
        });
    </script>
@endpush
</div>
