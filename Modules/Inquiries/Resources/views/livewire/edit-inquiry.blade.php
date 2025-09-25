<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
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
                        <form wire:submit.prevent="save">
                            <!-- Project Data Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-success">
                                        <div class="card-header">
                                            <h2 class="card-title mb-0">
                                                <i class="fas fa-project-diagram me-2"></i>
                                                بيانات المشروع
                                            </h2>
                                            <small class="d-block mt-1">المعلومات الأساسية للمشروع والتواريخ
                                                المهمة</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">الاسم</label>
                                                    <input type="text" wire:model="inquiryName" class="form-control">
                                                    @error('inquiryName')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">المدينة</label>
                                                    <select wire:model.live="cityId" class="form-select">
                                                        <option value="">اختر المدينة...</option>
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city['id'] }}">{{ $city['title'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('cityId')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">المنطقة</label>
                                                    <select wire:model="townId" class="form-select">
                                                        <option value="">اختر المنطقة...</option>
                                                        @foreach ($towns as $town)
                                                            <option value="{{ $town['id'] }}">{{ $town['title'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('townId')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">حالة الاستفسار</label>
                                                    <select wire:model="status" class="form-select">
                                                        <option value="">اختر الحالة...</option>
                                                        @foreach ($statusOptions as $status)
                                                            <option value="{{ $status->value }}">
                                                                {{ $status->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('status')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">حالة KON</label>
                                                    <select wire:model="statusForKon" class="form-select">
                                                        <option value="">اختر...</option>
                                                        @foreach ($statusForKonOptions as $status)
                                                            <option value="{{ $status->value }}">
                                                                {{ $status->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('statusForKon')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">عنوان KON</label>
                                                    <select wire:model="konTitle" class="form-select">
                                                        <option value="">اختر العنوان...</option>
                                                        @foreach ($konTitleOptions as $title)
                                                            <option value="{{ $title->value }}">{{ $title->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('konTitle')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">المشروع</label>
                                                    <select wire:model="projectId" class="form-select">
                                                        <option value="">اختر المشروع...</option>
                                                        @foreach ($projects as $project)
                                                            <option value="{{ $project['id'] }}">
                                                                {{ $project['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('projectId')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">تاريخ الاستفسار</label>
                                                    <input type="date" wire:model="inquiryDate" class="form-control">
                                                    @error('inquiryDate')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">تاريخ التسليم المطلوب</label>
                                                    <input type="date" wire:model="reqSubmittalDate"
                                                        class="form-control">
                                                    @error('reqSubmittalDate')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">تاريخ بدء المشروع</label>
                                                    <input type="date" wire:model="projectStartDate"
                                                        class="form-control">
                                                    @error('projectStartDate')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">حجم المشروع</label>
                                                    <select wire:model="projectSize" class="form-select">
                                                        <option value="">اختر حجم المشروع...</option>
                                                        @foreach ($projectSizeOptions as $size)
                                                            <option value="{{ $size }}">{{ $size }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('projectSize')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">أولوية العميل</label>
                                                    <select wire:model="clientPriority" class="form-select">
                                                        <option value="">اختر الأولوية...</option>
                                                        @foreach ($clientPriorityOptions as $priority)
                                                            <option value="{{ $priority }}">{{ $priority }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('clientPriority')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">أولوية KON</label>
                                                    <select wire:model="konPriority" class="form-select">
                                                        <option value="">اختر الأولوية...</option>
                                                        @foreach ($konPriorityOptions as $priority)
                                                            <option value="{{ $priority }}">{{ $priority }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('konPriority')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Work Types Section & Inquiry Sources -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="card border-info">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-sitemap me-2"></i>
                                                تصنيف العمل الهرمي
                                            </h6>
                                            <small class="d-block mt-1">اختر تصنيف العمل المطلوب من خلال التسلسل
                                                الهرمي</small>
                                        </div>
                                        <div class="card-body">
                                            <div id="path_display" class="mb-3 text-success">
                                                @if (!empty($selectedWorkPath))
                                                    <i class="fas fa-route text-success me-1"></i> المسار المختار:
                                                    {{ implode(' → ', $selectedWorkPath) }}
                                                @else
                                                    <i class="fas fa-info-circle me-1"></i> اختر التصنيف أولاً لرؤية
                                                    المسار
                                                @endif
                                            </div>
                                            <div id="steps_wrapper" wire:ignore>
                                                <div class="row mb-3" id="work_types_row">
                                                    @foreach ($workTypeSteps as $stepNum => $value)
                                                        <div class="col-md-3" data-step="{{ $stepNum }}">
                                                            <label class="form-label fw-bold">
                                                                <span
                                                                    class="badge bg-primary me-2">{{ $stepNum }}</span>
                                                                {{ $stepNum == 1 ? 'التصنيف الرئيسي' : 'التصنيف ' . $stepNum }}
                                                            </label>
                                                            <select
                                                                wire:model="workTypeSteps.step_{{ $stepNum }}"
                                                                id="step_{{ $stepNum }}" class="form-select">
                                                                <option value="">اختر...</option>
                                                                @if ($stepNum == 1)
                                                                    @foreach ($workTypes as $type)
                                                                        <option value="{{ $type['id'] }}">
                                                                            {{ $type['name'] }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <label for="final_work_type" class="form-label fw-bold">
                                                                <i class="fas fa-edit text-success me-2"></i>
                                                                الوصف النهائي للعمل
                                                            </label>
                                                            <input type="text" wire:model="finalWorkType"
                                                                id="final_work_type" class="form-control"
                                                                placeholder="{{ !empty($selectedWorkPath) ? 'أدخل تفاصيل إضافية للعمل: ' . end($selectedWorkPath) : 'أدخل وصف العمل بالتفصيل...' }}">
                                                            @error('finalWorkType')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
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
                                                مصادر الاستفسار الهرمية
                                            </h6>
                                            <small class="d-block mt-1">اختر مصدر الاستفسار من خلال التسلسل
                                                الهرمي</small>
                                        </div>
                                        <div class="card-body">
                                            <div id="inquiry_sources_path_display" class="mb-3 text-warning">
                                                @if (!empty($selectedInquiryPath))
                                                    <i class="fas fa-route text-warning me-1"></i> المسار المختار:
                                                    {{ implode(' → ', $selectedInquiryPath) }}
                                                @else
                                                    <i class="fas fa-info-circle me-1"></i> اختر المصدر أولاً لرؤية
                                                    المسار
                                                @endif
                                            </div>
                                            <div id="inquiry_sources_steps_wrapper" wire:ignore>
                                                <div class="row mb-3" id="inquiry_sources_row">
                                                    @foreach ($inquirySourceSteps as $stepNum => $value)
                                                        <div class="col-md-3" data-step="{{ $stepNum }}">
                                                            <label class="form-label fw-bold">
                                                                <span
                                                                    class="badge bg-warning text-dark me-2">{{ $stepNum }}</span>
                                                                {{ $stepNum == 1 ? 'المصدر الرئيسي' : 'المصدر ' . $stepNum }}
                                                            </label>
                                                            <select
                                                                wire:model="inquirySourceSteps.inquiry_source_step_{{ $stepNum }}"
                                                                id="inquiry_source_step_{{ $stepNum }}"
                                                                class="form-select">
                                                                <option value="">اختر...</option>
                                                                @if ($stepNum == 1)
                                                                    @foreach ($inquirySources as $source)
                                                                        <option value="{{ $source['id'] }}">
                                                                            {{ $source['name'] }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <label for="final_inquiry_source"
                                                                class="form-label fw-bold">
                                                                <i class="fas fa-edit text-warning me-2"></i>
                                                                الوصف النهائي للمصدر
                                                            </label>
                                                            <input type="text" wire:model="finalInquirySource"
                                                                id="final_inquiry_source" class="form-control"
                                                                placeholder="{{ !empty($selectedInquiryPath) ? 'أدخل تفاصيل إضافية للمصدر: ' . end($selectedInquiryPath) : 'أدخل وصف المصدر بالتفصيل...' }}">
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

                            <!-- Stakeholders Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-dark">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-users me-2"></i>
                                                الأطراف المعنية
                                            </h6>
                                            <small class="d-block mt-1">تحديد جميع الأطراف المشاركة في المشروع</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                                                            </div>
                                                            <label class="form-label fw-bold">العميل</label>
                                                            <select wire:model="clientId" class="form-select">
                                                                <option value="">اختر العميل...</option>
                                                                @foreach ($clients as $client)
                                                                    <option value="{{ $client['id'] }}">
                                                                        {{ $client['cname'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('clientId')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <i class="fas fa-hard-hat fa-2x text-warning"></i>
                                                            </div>
                                                            <label class="form-label fw-bold">المقاول الرئيسي</label>
                                                            <select wire:model="mainContractorId" class="form-select">
                                                                <option value="">اختر المقاول...</option>
                                                                @foreach ($mainContractors as $mc)
                                                                    <option value="{{ $mc['id'] }}">
                                                                        {{ $mc['cname'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('mainContractorId')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <i class="fas fa-user-graduate fa-2x text-info"></i>
                                                            </div>
                                                            <label class="form-label fw-bold">الاستشاري</label>
                                                            <select wire:model="consultantId" class="form-select">
                                                                <option value="">اختر الاستشاري...</option>
                                                                @foreach ($consultants as $consultant)
                                                                    <option value="{{ $consultant['id'] }}">
                                                                        {{ $consultant['cname'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('consultantId')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <i class="fas fa-crown fa-2x text-success"></i>
                                                            </div>
                                                            <label class="form-label fw-bold">المالك</label>
                                                            <select wire:model="ownerId" class="form-select">
                                                                <option value="">اختر المالك...</option>
                                                                @foreach ($owners as $owner)
                                                                    <option value="{{ $owner['id'] }}">
                                                                        {{ $owner['cname'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('ownerId')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <i class="fas fa-tools fa-2x text-danger"></i>
                                                            </div>
                                                            <label class="form-label fw-bold">المهندس</label>
                                                            <select wire:model="assignedEngineer" class="form-select">
                                                                <option value="">المهندس المعين...</option>
                                                                @foreach ($engineers as $engineer)
                                                                    <option value="{{ $engineer['id'] }}">
                                                                        {{ $engineer['cname'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('assignedEngineer')
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

                            <!-- Documents and Checklists Section -->
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
                                                                id="document_{{ $index }}"
                                                                class="form-check-input">
                                                            <label for="document_{{ $index }}"
                                                                class="form-check-label">
                                                                {{ $document['name'] }}
                                                            </label>
                                                            {{-- @if ($document['name'] === 'other' && $projectDocuments[$index]['checked'])
                                                                <input type="text"
                                                                    wire:model="projectDocuments.{{ $index }}.description"
                                                                    class="form-control mt-2"
                                                                    placeholder="اكتب نوع الوثيقة...">
                                                                @error('projectDocuments.' . $index . '.description')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            @endif --}}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Required Submittal Checklist Section -->
                                <div class="col-6">
                                    <div class="card border-success">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-check-square me-2"></i>
                                                قائمة التقديمات المطلوبة
                                            </h6>
                                            <small class="d-block mt-1">اختر التقديمات المطلوبة (مع حساب
                                                السكور)</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach ($submittalChecklist as $index => $item)
                                                    @if (isset($item['checked']))
                                                        <div class="col-md-3 mb-3">
                                                            <div class="form-check">
                                                                <input type="checkbox"
                                                                    wire:model.live="submittalChecklist.{{ $index }}.checked"
                                                                    id="submittal_{{ $index }}"
                                                                    class="form-check-input">
                                                                <label for="submittal_{{ $index }}"
                                                                    class="form-check-label">
                                                                    {{ $item['name'] }} ({{ $item['value'] }})
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="mt-3 fw-bold text-success">
                                                <i class="fas fa-calculator me-2"></i>
                                                إجمالي السكور للتقديمات: <span
                                                    class="badge bg-success">{{ $totalSubmittalScore }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Working Conditions Checklist Section -->
                                <div class="col-12 mt-3">
                                    <div class="card border-danger">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                قائمة شروط العمل
                                            </h6>
                                            <small class="d-block mt-1">اختر الشروط (مع حساب السكور)</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach ($workingConditions as $index => $condition)
                                                    <div class="col-md-3 mb-3">
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                wire:model.live="workingConditions.{{ $index }}.checked"
                                                                id="condition_{{ $index }}"
                                                                class="form-check-input">
                                                            <label for="condition_{{ $index }}"
                                                                class="form-check-label">
                                                                {{ $condition['name'] }}
                                                            </label>
                                                        </div>
                                                        @if (isset($condition['options']) && $workingConditions[$index]['checked'])
                                                            <select
                                                                wire:model.live="workingConditions.{{ $index }}.selectedOption"
                                                                class="form-select mt-2">
                                                                <option value="">اختر...</option>
                                                                @foreach ($condition['options'] as $option => $score)
                                                                    <option value="{{ $score }}">
                                                                        {{ $option }} ({{ $score }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('workingConditions.' . $index . '.selectedOption')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Display Results -->
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="text-center">
                                                                    <i
                                                                        class="fas fa-list-check fa-2x text-primary mb-2"></i>
                                                                    <h5>إجمالي السكور للشروط</h5>
                                                                    <span
                                                                        class="badge bg-primary fs-4">{{ $totalConditionsScore }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="text-center">
                                                                    <i
                                                                        class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                                                    <h5>صعوبة المشروع</h5>
                                                                    <span
                                                                        class="badge bg-warning fs-4">{{ $projectDifficulty }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="text-center">
                                                                    <i
                                                                        class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                                                    <h5>تصنيف الصعوبة</h5>
                                                                    <span
                                                                        class="badge @if ($projectDifficulty == 1) bg-success @elseif($projectDifficulty == 2) bg-warning @elseif($projectDifficulty == 3) bg-danger @else bg-dark @endif fs-4">
                                                                        @if ($projectDifficulty == 1)
                                                                            سهل
                                                                        @elseif($projectDifficulty == 2)
                                                                            متوسط
                                                                        @elseif($projectDifficulty == 3)
                                                                            صعب
                                                                        @else
                                                                            صعب جداً
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estimation Information Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-calculator me-2"></i>
                                                معلومات التقدير
                                            </h6>
                                            <small class="d-block mt-1">تفاصيل التقدير والتسعير</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">رقم المناقصة</label>
                                                    <input type="text" wire:model="tenderNo" class="form-control"
                                                        placeholder="مثال: T-169">
                                                    @error('tenderNo')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">معرف المناقصة</label>
                                                    <input type="text" wire:model="tenderId" class="form-control"
                                                        placeholder="مثال: T-169,Piling & Shoring Works">
                                                    @error('tenderId')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">تاريخ البدء</label>
                                                    <input type="date" wire:model="estimationStartDate"
                                                        class="form-control">
                                                    @error('estimationStartDate')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">تاريخ الانتهاء</label>
                                                    <input type="date" wire:model="estimationFinishedDate"
                                                        class="form-control">
                                                    @error('estimationFinishedDate')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">تاريخ التقديم</label>
                                                    <input type="date" wire:model="submittingDate"
                                                        class="form-control">
                                                    @error('submittingDate')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">القيمة الإجمالية للمشروع</label>
                                                    <input type="number" wire:model="totalProjectValue"
                                                        class="form-control" placeholder="أدخل القيمة...">
                                                    @error('totalProjectValue')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Documents Section -->
                            @if (!empty($existingDocuments))
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border-info">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-folder-open me-2"></i>
                                                    الوثائق المرفوعة حالياً
                                                </h6>
                                                <small class="d-block mt-1">الملفات المرفوعة مسبقاً</small>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach ($existingDocuments as $document)
                                                        <div class="col-md-3 mb-3">
                                                            <div class="card border">
                                                                <div class="card-body text-center p-3">
                                                                    <i
                                                                        class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                                                    <h6 class="card-title">{{ $document['name'] }}
                                                                    </h6>
                                                                    <small
                                                                        class="text-muted">{{ number_format($document['size'] / 1024, 2) }}
                                                                        KB</small>
                                                                    <div class="mt-2">
                                                                        <a href="{{ $document['url'] }}"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-primary me-1">
                                                                            <i class="fas fa-eye me-1"></i>
                                                                            عرض
                                                                        </a>
                                                                        <button
                                                                            wire:click="removeExistingDocument({{ $document['id'] }})"
                                                                            class="btn btn-sm btn-danger"
                                                                            onclick="return confirm('هل أنت متأكد من حذف هذا الملف؟')">
                                                                            <i class="fas fa-trash me-1"></i>
                                                                            حذف
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Quotation State Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-warning">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                                حالة عرض الأسعار
                                            </h6>
                                            <small class="d-block mt-1">حدد حالة عرض الأسعار وأسباب الرفض إن
                                                وجدت</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">حالة عرض الأسعار</label>
                                                    <select wire:model="quotationState" class="form-select">
                                                        <option value="">اختر الحالة...</option>
                                                        @foreach ($quotationStateOptions as $state)
                                                            <option value="{{ $state->value }}">
                                                                {{ $state->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('quotationState')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                @if (in_array($quotationState, [
                                                        \Modules\Inquiries\Enums\QuotationStateEnum::REJECTED->value,
                                                        \Modules\Inquiries\Enums\QuotationStateEnum::RE_ESTIMATION->value,
                                                    ]))
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">سبب الرفض</label>
                                                        <textarea wire:model="quotationStateReason" class="form-control" rows="4" placeholder="أدخل سبب الرفض..."></textarea>
                                                        @error('quotationStateReason')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File Upload Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-upload me-2"></i>
                                                رفع وثيقة جديدة
                                            </h6>
                                            <small class="d-block mt-1">رفع ملفات جديدة (PDF, DOC, JPG, PNG بحد أقصى
                                                10MB)</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <input type="file" wire:model="documentFile"
                                                        class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                                                    @error('documentFile')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                    @if ($documentFile)
                                                        <div class="mt-2">
                                                            <small class="text-muted">الملف المختار:
                                                                {{ $documentFile->getClientOriginalName() }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('inquiries.index') }}" class="btn btn-secondary btn-lg">
                                            <i class="fas fa-times me-2"></i>
                                            إلغاء
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-lg">
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
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', function() {
                // Work Types Hierarchical Selection
                const stepsWrapper = document.getElementById('steps_wrapper');
                const workTypesRow = document.getElementById('work_types_row');
                const pathDisplay = document.getElementById('path_display');
                const finalInput = document.getElementById('final_work_type');

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

                Livewire.on('workTypeChildrenLoaded', ({
                    stepNum,
                    children
                }) => {
                    if (children.length === 0) return;

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
                            <select wire:model="workTypeSteps.step_${nextStepNum}" id="step_${nextStepNum}" class="form-select">
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
                const inquiryPathDisplay = document.getElementById('inquiry_sources_path_display');
                const inquiryFinalInput = document.getElementById('final_inquiry_source');

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

                Livewire.on('inquirySourceChildrenLoaded', ({
                    stepNum,
                    children
                }) => {
                    if (children.length === 0) return;

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
                            <select wire:model="inquirySourceSteps.inquiry_source_step_${nextStepNum}" id="inquiry_source_step_${nextStepNum}" class="form-select">
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

                // Load existing steps for Work Types and Inquiry Sources
                @if ($inquiryId)
                    @foreach ($workTypeSteps as $stepNum => $stepId)
                        @if ($stepNum > 1 && $stepId)
                            createWorkTypeStepItem({{ $stepNum }}, {{ $stepId }});
                        @endif
                    @endforeach
                    @foreach ($inquirySourceSteps as $stepNum => $stepId)
                        @if ($stepNum > 1 && $stepId)
                            createInquirySourceStepItem({{ $stepNum }}, {{ $stepId }});
                        @endif
                    @endforeach
                @endif

                // Initialize first step listeners
                document.getElementById('step_1').addEventListener('change', function() {
                    const selectedId = this.value;
                    removeWorkTypeStepsAfter(1);
                    if (selectedId) {
                        createWorkTypeStepItem(2, selectedId);
                    }
                });

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
