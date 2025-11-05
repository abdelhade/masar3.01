<div>
    <div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form wire:submit.prevent="save">
                        <div class="card-body">

                            <!-- Project Data Section -->
                            @include('inquiries::components.project-data')

                            <!-- Quotation State Section -->
                            @include('inquiries::components.quotation-state')

                            <!-- Cities & Distance Section -->
                            @include('inquiries::components.cities-select')

                            <!-- Work Types Section & Inquiry Sources -->
                            @include('inquiries::components.work-types&inquiry-source')
                        </div>

                        <!-- Stakeholders Section -->
                        @include('inquiries::components.Stakeholders-Section')

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-dark">
                                    <div class="card-header ">
                                        <h5>{{ __('Required Quotation Information') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        {{-- Quotation Types & Units --}}
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
                                                        <label for="document_{{ $index }}"
                                                            class="form-check-label">
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
                                                <a href="{{ route('inquiries.index') }}"
                                                    class="btn btn-secondary btn-lg">
                                                    <i class="fas fa-times me-2"></i>
                                                    {{ __('Cancel') }}
                                                </a>
                                            </div>

                                            <div>
                                                <button type="button" wire:click="save" class="btn btn-success btn-lg">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    {{ __('Update Inquiry') }}
                                                </button>
                                            </div>
                                        </div>
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
                    const stepsToRemove = workTypesRow.querySelectorAll('[data-step]');
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
                    let existingStep = workTypesRow.querySelector(`[data-step="${nextStepNum}"]`);

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

                        existingStep = stepItem.querySelector('select');
                    }

                    const select = document.getElementById(`step_${nextStepNum}`) || existingStep;
                    if (select) {
                        select.innerHTML = `<option value="">Select step ${nextStepNum}...</option>`;
                        children.forEach(item => {
                            const option = new Option(item.name, item.id);
                            select.add(option);
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
                    const stepsToRemove = inquirySourcesRow.querySelectorAll('[data-step]');
                    stepsToRemove.forEach(step => {
                        const stepNumber = parseInt(step.getAttribute('data-step'));
                        if (stepNumber > stepNum) {
                            step.remove();
                        }
                    });
                }

                Livewire.on('workTypeAdded', () => {
                    const step1Select = document.getElementById('step_1');
                    if (step1Select) {
                        step1Select.value = '';
                        removeWorkTypeStepsAfter(1);
                    }
                });

                Livewire.on('inquirySourceChildrenLoaded', ({
                    stepNum,
                    children
                }) => {
                    if (children.length === 0) {
                        return;
                    }

                    const nextStepNum = stepNum + 1;
                    let existingStep = inquirySourcesRow.querySelector(`[data-step="${nextStepNum}"]`);

                    if (!existingStep) {
                        const stepItem = document.createElement('div');
                        stepItem.className = 'col-md-3';
                        stepItem.setAttribute('data-step', nextStepNum);
                        stepItem.innerHTML = `
                    <label class="form-label fw-bold">
                        <span class="badge bg-warning text-dark me-2">${nextStepNum}</span>
                        Source ${nextStepNum}
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

                        existingStep = stepItem.querySelector('select');
                    }

                    const select = document.getElementById(`inquiry_source_step_${nextStepNum}`) ||
                    existingStep;
                    if (select) {
                        select.innerHTML = `<option value="">Select step ${nextStepNum}...</option>`;
                        children.forEach(item => {
                            const option = new Option(item.name, item.id);
                            select.add(option);
                        });
                    }
                });

                // Prepopulate Work Types (عند التحميل الأول)
                Livewire.on('prepopulateWorkTypes', ({
                    steps,
                    path
                }) => {
                    removeWorkTypeStepsAfter(1);

                    Object.keys(steps).forEach((stepNum, index) => {
                        const stepId = steps[stepNum];

                        setTimeout(() => {
                            if (stepNum == 1) {
                                const step1 = document.getElementById('step_1');
                                if (step1) {
                                    step1.value = stepId;
                                    step1.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                }
                            } else {
                                const select = document.getElementById(`step_${stepNum}`);
                                if (select) {
                                    select.value = stepId;
                                    select.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                }
                            }
                        }, index * 200); // تأخير تدريجي لكل خطوة
                    });
                });

                // Prepopulate Inquiry Sources
                Livewire.on('prepopulateInquirySources', ({
                    steps,
                    path
                }) => {
                    removeInquirySourceStepsAfter(1);

                    Object.keys(steps).forEach((stepNum, index) => {
                        const stepId = steps[stepNum];

                        setTimeout(() => {
                            if (stepNum == 1) {
                                const step1 = document.getElementById('inquiry_source_step_1');
                                if (step1) {
                                    step1.value = stepId;
                                    step1.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                }
                            } else {
                                const select = document.getElementById(
                                    `inquiry_source_step_${stepNum}`);
                                if (select) {
                                    select.value = stepId;
                                    select.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                }
                            }
                        }, index * 200);
                    });
                });

                // Handle step_1 change
                const step1Element = document.getElementById('step_1');
                if (step1Element) {
                    step1Element.addEventListener('change', function() {
                        const selectedId = this.value;
                        removeWorkTypeStepsAfter(1);
                        if (selectedId) {
                            createWorkTypeStepItem(2, selectedId);
                        }
                    });
                }

                // Handle inquiry_source_step_1 change
                const inquiryStep1Element = document.getElementById('inquiry_source_step_1');
                if (inquiryStep1Element) {
                    inquiryStep1Element.addEventListener('change', function() {
                        const selectedId = this.value;
                        removeInquirySourceStepsAfter(1);
                        if (selectedId) {
                            createInquirySourceStepItem(2, selectedId);
                        }
                    });
                }

                // Contact Modal Events
                Livewire.on('openContactModal', () => {
                    const modal = new bootstrap.Modal(document.getElementById('addContactModal'));
                    modal.show();
                });

                Livewire.on('closeContactModal', () => {
                    const modalElement = document.getElementById('addContactModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                });
            });
        </script>
    @endpush
</div>
