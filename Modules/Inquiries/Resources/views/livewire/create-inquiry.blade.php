<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form wire:submit.prevent="save">
                    <div class="card shadow">
                        <div class="card-body">
                            <!-- Project Data Section -->
                            @include('inquiries::components.project-data')

                            <!-- Quotation State Section -->
                            @include('inquiries::components.quotation-state')

                            <!-- Work Types Section & Inquiry Sources -->
                            @include('inquiries::components.work-types&inquiry-source')
                        </div>

                        <!-- Stakeholders Section -->
                        @include('inquiries::components.Stakeholders-Section')
                    </div>

                    <div class="row mb-4">
                        <!-- Project Documents Section -->
                        <div class="col-6">
                            <div class="card border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-file-alt me-2"></i>
                                        وثائق المشروع
                                    </h6>
                                    <small class="d-block mt-1">اختر الوثائق المتاحة</small>
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
                        <div class="col-4">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('inquiries.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    حفظ الاستفسار
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
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
                const stepsToRemove = stepsWrapper.querySelectorAll('[data-step]');
                stepsToRemove.forEach(step => {
                    const stepNumber = parseInt(step.getAttribute('data-step'));
                    if (stepNumber > stepNum) {
                        step.remove();
                    }
                });
            }

            // Listen for workTypeChildrenLoaded - مُحدث
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
                            التصنيف ${nextStepNum}
                        </label>
                        <select wire:model.live="currentWorkTypeSteps.step_${nextStepNum}" id="step_${nextStepNum}" class="form-select">
                            <option value="">اختر الخطوة ${nextStepNum}...</option>
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
                    select.innerHTML = `<option value="">اختر الخطوة ${nextStepNum}...</option>`;
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
                // إعادة تعيين الـ select بتاع المرحلة الأولى
                const step1Select = document.getElementById('step_1');
                step1Select.value = ''; // إفراغ الاختيار
                removeWorkTypeStepsAfter(1); // حذف كل المراحل بعد الأولى
                // تحديث عرض المسار الحالي
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
                            <option value="">اختر الخطوة ${nextStepNum}...</option>
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
                    select.innerHTML = `<option value="">اختر الخطوة ${nextStepNum}...</option>`;
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
