<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form wire:submit.prevent="save">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    تعديل الاستفسار: {{ $inquiryName }}
                                </h4>
                            </div>
                        </div>
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

                    <!-- Types and Units Section -->
                    <div class="row">
                        <div class="col-6">
                            <div class="card border-primary">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-list me-2"></i>
                                        النوع (Type)
                                    </h6>
                                    <small class="d-block mt-1">اختر نوع أو أكثر</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($types as $index => $type)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        wire:model="types.{{ $index }}.checked"
                                                        id="type_{{ $index }}" class="form-check-input">
                                                    <label for="type_{{ $index }}" class="form-check-label">
                                                        {{ $type['name'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label">ملاحظة (اختياري)</label>
                                        <input type="text" wire:model="type_note" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card border-success">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-balance-scale me-2"></i>
                                        الوحدة (Unit)
                                    </h6>
                                    <small class="d-block mt-1">اختر وحدة أو أكثر</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($units as $index => $unit)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        wire:model="units.{{ $index }}.checked"
                                                        id="unit_{{ $index }}" class="form-check-input">
                                                    <label for="unit_{{ $index }}" class="form-check-label">
                                                        {{ $unit['name'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents and Checklists -->
                    <div class="row mb-4">
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

                        <!-- Submittal & Working Conditions -->
                        @include('inquiries::components.submittal-work-condition')
                    </div>

                    <!-- Estimation Information Section -->
                    @include('inquiries::components.estimation-information')

                    <!-- Temporary Comments Section - NEW -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comments me-2"></i>
                                        التعليقات والملاحظات
                                    </h6>
                                    <small class="d-block mt-1">التعليقات المحفوظة والتعليقات الجديدة</small>
                                </div>
                                <div class="card-body">
                                    <!-- التعليقات الموجودة من قبل -->
                                    @if (!empty($existingComments))
                                        <div class="mb-4">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-history me-2"></i>
                                                التعليقات المحفوظة ({{ count($existingComments) }})
                                            </h6>
                                            <div class="existing-comments-list">
                                                @foreach ($existingComments as $comment)
                                                    <div class="alert alert-secondary d-flex justify-content-between align-items-start mb-2">
                                                        <div class="flex-grow-1">
                                                            <div class="mb-1">
                                                                <strong>
                                                                    <i class="fas fa-user me-1"></i>
                                                                    {{ $comment['user_name'] }}
                                                                </strong>
                                                                <small class="text-muted ms-2">
                                                                    <i class="fas fa-clock me-1"></i>
                                                                    {{ \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i') }}
                                                                </small>
                                                            </div>
                                                            <p class="mb-0">{{ $comment['comment'] }}</p>
                                                        </div>
                                                        <button type="button"
                                                            wire:click="removeExistingComment({{ $comment['id'] }})"
                                                            class="btn btn-sm btn-outline-danger ms-2"
                                                            onclick="return confirm('هل أنت متأكد من حذف هذا التعليق؟')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <hr>
                                    @endif

                                    <!-- Form لإضافة تعليق جديد -->
                                    <div class="mb-3">
                                        <label for="newTempComment" class="form-label fw-bold">
                                            <i class="fas fa-pen me-2"></i>
                                            أضف ملاحظة جديدة
                                        </label>
                                        <div class="input-group">
                                            <textarea wire:model="newTempComment" id="newTempComment"
                                                class="form-control" rows="2"
                                                placeholder="اكتب ملاحظاتك هنا..."></textarea>
                                            <button type="button" wire:click="addTempComment" class="btn btn-primary">
                                                <i class="fas fa-plus"></i>
                                                إضافة
                                            </button>
                                        </div>
                                        @error('newTempComment')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- عرض التعليقات المؤقتة (قبل الحفظ) -->
                                    @if (!empty($tempComments))
                                        <div class="mt-3">
                                            <h6 class="fw-bold text-success mb-3">
                                                <i class="fas fa-plus-circle me-2"></i>
                                                تعليقات جديدة سيتم حفظها ({{ count($tempComments) }})
                                            </h6>
                                            <div class="comments-list">
                                                @foreach ($tempComments as $index => $comment)
                                                    <div class="alert alert-info d-flex justify-content-between align-items-start mb-2">
                                                        <div class="flex-grow-1">
                                                            <div class="mb-1">
                                                                <strong>
                                                                    <i class="fas fa-user me-1"></i>
                                                                    {{ $comment['user_name'] }}
                                                                </strong>
                                                                <small class="text-muted ms-2">
                                                                    <i class="fas fa-clock me-1"></i>
                                                                    {{ \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i') }}
                                                                </small>
                                                                <span class="badge bg-warning text-dark ms-2">جديد</span>
                                                            </div>
                                                            <p class="mb-0">{{ $comment['comment'] }}</p>
                                                        </div>
                                                        <button type="button" wire:click="removeTempComment({{ $index }})"
                                                            class="btn btn-sm btn-outline-danger ms-2">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if (empty($existingComments) && empty($tempComments))
                                        <div class="alert alert-secondary mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            لا توجد تعليقات. يمكنك إضافة ملاحظاتك هنا.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('inquiries.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    تحديث الاستفسار
                                </button>
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
                const stepsToRemove = stepsWrapper.querySelectorAll('[data-step]');
                stepsToRemove.forEach(step => {
                    const stepNumber = parseInt(step.getAttribute('data-step'));
                    if (stepNumber > stepNum) {
                        step.remove();
                    }
                });
            }

            Livewire.on('workTypeChildrenLoaded', ({stepNum, children}) => {
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
                        <select wire:model.live="workTypeSteps.step_${nextStepNum}" id="step_${nextStepNum}" class="form-select">
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

            Livewire.on('inquirySourceChildrenLoaded', ({stepNum, children}) => {
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
            const step1 = document.getElementById('step_1');
            if (step1) {
                step1.addEventListener('change', function() {
                    const selectedId = this.value;
                    removeWorkTypeStepsAfter(1);
                    if (selectedId) {
                        createWorkTypeStepItem(2, selectedId);
                    }
                });
            }

            // Handle inquiry_source_step_1 change
            const inquiryStep1 = document.getElementById('inquiry_source_step_1');
            if (inquiryStep1) {
                inquiryStep1.addEventListener('change', function() {
                    const selectedId = this.value;
                    removeInquirySourceStepsAfter(1);
                    if (selectedId) {
                        createInquirySourceStepItem(2, selectedId);
                    }
                });
            }
        });
    </script>
@endpush
