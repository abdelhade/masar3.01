<div>
    <div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form wire:submit.prevent="save">
                        <div class="card-body">
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

                                                <div class="col-md-3 mb-3 d-flex flex-column">
                                                    <label class="form-label fw-bold">المشروع</label>
                                                    <livewire:app::searchable-select :model="Modules\Progress\Models\ProjectProgress::class"
                                                        label-field="name" wire-model="projectId"
                                                        placeholder="ابحث عن المشروع أو أضف جديد..." :key="'project-select-edit-' . $inquiryId"
                                                        :selected-id="$projectId" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">رقم المناقصة</label>
                                                    <input type="text" wire:model="tenderNo" class="form-control">
                                                    @error('tenderNo')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">معرف المناقصة</label>
                                                    <input type="text" wire:model="tenderId" class="form-control">
                                                    @error('tenderId')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">حالة الاستفسار</label>
                                                    <select wire:model="status" class="form-select">
                                                        <option value="">اختر الحالة...</option>
                                                        @foreach ($statusOptions as $statusOption)
                                                            <option value="{{ $statusOption->value }}">
                                                                {{ $statusOption->label() }}</option>
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
                                                        @foreach ($statusForKonOptions as $statusOption)
                                                            <option value="{{ $statusOption->value }}">
                                                                {{ $statusOption->label() }}</option>
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

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">المنطقة</label>
                                                    <select wire:model.live="townId" class="form-select">
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
                                                    <label class="form-label fw-bold">المسافة (كم)</label>
                                                    <input type="number" step="0.01" wire:model="townDistance"
                                                        class="form-control" placeholder="المسافة بالكيلومتر">
                                                    @error('townDistance')
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

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">تاريخ بدء المشروع</label>
                                                    <input type="date" wire:model="projectStartDate"
                                                        class="form-control">
                                                    @error('projectStartDate')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">صورة المشروع</label>
                                                    <input type="file" wire:model="projectImage" id="projectImage"
                                                        class="form-control @error('projectImage') is-invalid @enderror">
                                                    @error('projectImage')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror

                                                    <!-- عرض الصورة الحالية -->
                                                    @if ($existingProjectImage)
                                                        <div class="mt-2">
                                                            <img src="{{ $existingProjectImage->getUrl() }}"
                                                                alt="صورة المشروع الحالية" class="img-thumbnail"
                                                                style="max-height: 150px;">
                                                            <button type="button" wire:click="removeProjectImage"
                                                                class="btn btn-sm btn-danger mt-1"
                                                                onclick="return confirm('هل أنت متأكد من حذف الصورة؟')">
                                                                <i class="fas fa-trash"></i> حذف الصورة
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quotation State Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-file-invoice me-2"></i>
                                                حالة التسعير
                                            </h6>
                                            <small class="d-block mt-1">اختر حالة التسعير</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">حالة التسعير</label>
                                                    <select wire:model.live="quotationState" class="form-select">
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

                                                @if (in_array($this->quotationState, [
                                                        \Modules\Inquiries\Enums\QuotationStateEnum::REJECTED->value,
                                                        \Modules\Inquiries\Enums\QuotationStateEnum::RE_ESTIMATION->value,
                                                    ]))
                                                    <div class="col-md-2 mb-3">
                                                        <label class="form-label fw-bold">سبب الحالة</label>
                                                        <input type="text" wire:model.live="quotationStateReason"
                                                            class="form-control" placeholder="أدخل السبب...">
                                                        @error('quotationStateReason')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                @endif

                                                <div class="col-md-2 mb-3">
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

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">أولوية KON</label>
                                                    <select wire:model="konPriority" class="form-select">
                                                        <option value="">اختر أولوية KON...</option>
                                                        @foreach ($konPriorityOptions as $option)
                                                            <option value="{{ $option }}">{{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('konPriority')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label fw-bold">أولوية العميل</label>
                                                    <select wire:model="clientPriority" class="form-select">
                                                        <option value="">اختر أولوية ...</option>
                                                        @foreach ($clientPriorityOptions as $option)
                                                            <option value="{{ $option }}">{{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('clientPriority')
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
                                        </div>
                                        <div class="card-body">
                                            <!-- العناصر المختارة -->
                                            @if (!empty($selectedWorkTypes))
                                                <div class="mb-3">
                                                    <label class="fw-bold">الأعمال المختارة:</label>
                                                    @foreach ($selectedWorkTypes as $index => $workType)
                                                        <div
                                                            class="alert alert-info d-flex justify-content-between align-items-center">
                                                            <span>{{ implode(' → ', $workType['path']) }}</span>
                                                            <button type="button"
                                                                wire:click="removeWorkType({{ $index }})"
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
                                                    <i class="fas fa-route me-1"></i> المسار الحالي:
                                                    {{ implode(' → ', $currentWorkPath) }}
                                                @else
                                                    <i class="fas fa-info-circle me-1"></i> اختر التصنيف
                                                @endif
                                            </div>

                                            <div id="steps_wrapper" wire:ignore>
                                                <div class="row mb-3" id="work_types_row">
                                                    <div class="col-md-3" data-step="1">
                                                        <label class="form-label fw-bold">
                                                            <span class="badge bg-primary me-2">1</span>
                                                            التصنيف الرئيسي
                                                        </label>
                                                        <select wire:model="currentWorkTypeSteps.step_1"
                                                            id="step_1" class="form-select">
                                                            <option value="">اختر التصنيف الرئيسي...</option>
                                                            @foreach ($workTypes as $type)
                                                                <option value="{{ $type['id'] }}">
                                                                    {{ $type['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" wire:click="addWorkType"
                                                class="btn btn-primary mt-2">
                                                <i class="fas fa-plus me-2"></i>
                                                إضافة هذا التصنيف
                                            </button>
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
                                                            placeholder="{{ !empty($currentWorkPath) ? 'أدخل تفاصيل إضافية للعمل: ' . end($currentWorkPath) : 'أدخل وصف العمل بالتفصيل...' }}">
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
                                                    <div class="col-md-3" data-step="1">
                                                        <label class="form-label fw-bold">
                                                            <span class="badge bg-warning text-dark me-2">1</span>
                                                            المصدر الرئيسي
                                                        </label>
                                                        <select wire:model="inquirySourceSteps.inquiry_source_step_1"
                                                            id="inquiry_source_step_1" class="form-select">
                                                            <option value="">اختر المصدر الرئيسي...</option>
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
                                            <!-- العميل -->
                                            <div class="col-md-3 mb-3 d-flex flex-column">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-user-tie fa-2x text-primary"></i>
                                                    </div>
                                                    <label class="form-label fw-bold">العميل</label>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <div class="flex-grow-1">
                                                            <livewire:app::searchable-select :model="App\Models\Client::class"
                                                                label-field="cname" wire-model="clientId"
                                                                placeholder="ابحث عن العميل أو أضف جديد..."
                                                                :where="[
                                                                    'type' => [
                                                                        \App\Enums\ClientType::Person->value,
                                                                        \App\Enums\ClientType::Company->value,
                                                                    ],
                                                                ]" :selected-id="$clientId" :additional-data="[
                                                                    'type' => \App\Enums\ClientType::Person->value,
                                                                ]"
                                                                :key="'client-select-edit-' . $inquiryId" />
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::Person->value }} })"
                                                            title="إضافة عميل جديد">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    @if ($clientId)
                                                        @php
                                                            $client = \App\Models\Client::find($clientId);
                                                        @endphp
                                                        @if ($client)
                                                            <div class="card mt-3 bg-light">
                                                                <div class="card-body p-2 text-start">
                                                                    <small class="d-block"><strong>الاسم:</strong>
                                                                        {{ $client->cname }}</small>
                                                                    @if ($client->phone)
                                                                        <small class="d-block"><strong>الهاتف:</strong>
                                                                            {{ $client->phone }}</small>
                                                                    @endif
                                                                    @if ($client->email)
                                                                        <small class="d-block"><strong>البريد:</strong>
                                                                            {{ $client->email }}</small>
                                                                    @endif
                                                                    @if ($client->address)
                                                                        <small
                                                                            class="d-block"><strong>العنوان:</strong>
                                                                            {{ $client->address }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- المقاول الرئيسي -->
                                            <div class="col-md-3 mb-3 d-flex flex-column">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-hard-hat fa-2x text-warning"></i>
                                                    </div>
                                                    <label class="form-label fw-bold">المقاول الرئيسي</label>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <div class="flex-grow-1">
                                                            <livewire:app::searchable-select :model="App\Models\Client::class"
                                                                label-field="cname" wire-model="mainContractorId"
                                                                :selected-id="$mainContractorId"
                                                                placeholder="ابحث أو أضف مقاول جديد..."
                                                                :where="[
                                                                    'type' =>
                                                                        \App\Enums\ClientType::MainContractor->value,
                                                                ]" :additional-data="[
                                                                    'type' =>
                                                                        \App\Enums\ClientType::MainContractor->value,
                                                                ]"
                                                                :key="'contractor-select-edit-' . $inquiryId" />
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-warning"
                                                            wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::MainContractor->value }} })"
                                                            title="إضافة مقاول جديد">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    @if ($mainContractorId)
                                                        @php
                                                            $contractor = \App\Models\Client::find($mainContractorId);
                                                        @endphp
                                                        @if ($contractor)
                                                            <div class="card mt-3 bg-light">
                                                                <div class="card-body p-2 text-start">
                                                                    <small class="d-block"><strong>الاسم:</strong>
                                                                        {{ $contractor->cname }}</small>
                                                                    @if ($contractor->phone)
                                                                        <small class="d-block"><strong>الهاتف:</strong>
                                                                            {{ $contractor->phone }}</small>
                                                                    @endif
                                                                    @if ($contractor->email)
                                                                        <small class="d-block"><strong>البريد:</strong>
                                                                            {{ $contractor->email }}</small>
                                                                    @endif
                                                                    @if ($contractor->address)
                                                                        <small
                                                                            class="d-block"><strong>العنوان:</strong>
                                                                            {{ $contractor->address }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- الاستشاري -->
                                            <div class="col-md-3 mb-3 d-flex flex-column">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-user-graduate fa-2x text-info"></i>
                                                    </div>
                                                    <label class="form-label fw-bold">الاستشاري</label>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <div class="flex-grow-1">
                                                            <livewire:app::searchable-select :model="App\Models\Client::class"
                                                                label-field="cname" wire-model="consultantId"
                                                                :selected-id="$consultantId" :where="[
                                                                    'type' => \App\Enums\ClientType::Consultant->value,
                                                                ]" :additional-data="[
                                                                    'type' => \App\Enums\ClientType::Consultant->value,
                                                                ]"
                                                                :key="'consultant-select-edit-' . $inquiryId" />
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-info"
                                                            wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::Consultant->value }} })"
                                                            title="إضافة استشاري جديد">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    @if ($consultantId)
                                                        @php
                                                            $consultant = \App\Models\Client::find($consultantId);
                                                        @endphp
                                                        @if ($consultant)
                                                            <div class="card mt-3 bg-light">
                                                                <div class="card-body p-2 text-start">
                                                                    <small class="d-block"><strong>الاسم:</strong>
                                                                        {{ $consultant->cname }}</small>
                                                                    @if ($consultant->phone)
                                                                        <small class="d-block"><strong>الهاتف:</strong>
                                                                            {{ $consultant->phone }}</small>
                                                                    @endif
                                                                    @if ($consultant->email)
                                                                        <small class="d-block"><strong>البريد:</strong>
                                                                            {{ $consultant->email }}</small>
                                                                    @endif
                                                                    @if ($consultant->address)
                                                                        <small
                                                                            class="d-block"><strong>العنوان:</strong>
                                                                            {{ $consultant->address }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- المالك -->
                                            <div class="col-md-3 mb-3 d-flex flex-column">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-crown fa-2x text-success"></i>
                                                    </div>
                                                    <label class="form-label fw-bold">المالك</label>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <div class="flex-grow-1">
                                                            <livewire:app::searchable-select :model="App\Models\Client::class"
                                                                label-field="cname" wire-model="ownerId"
                                                                placeholder="ابحث عن المالك أو أضف جديد..."
                                                                :where="[
                                                                    'type' => \App\Enums\ClientType::Owner->value,
                                                                ]" :selected-id="$ownerId" :additional-data="[
                                                                    'type' => \App\Enums\ClientType::Owner->value,
                                                                ]"
                                                                :key="'owner-select-edit-' . $inquiryId" />
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::Owner->value }} })"
                                                            title="إضافة مالك جديد">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    @if ($ownerId)
                                                        @php
                                                            $owner = \App\Models\Client::find($ownerId);
                                                        @endphp
                                                        @if ($owner)
                                                            <div class="card mt-3 bg-light">
                                                                <div class="card-body p-2 text-start">
                                                                    <small class="d-block"><strong>الاسم:</strong>
                                                                        {{ $owner->cname }}</small>
                                                                    @if ($owner->phone)
                                                                        <small class="d-block"><strong>الهاتف:</strong>
                                                                            {{ $owner->phone }}</small>
                                                                    @endif
                                                                    @if ($owner->email)
                                                                        <small class="d-block"><strong>البريد:</strong>
                                                                            {{ $owner->email }}</small>
                                                                    @endif
                                                                    @if ($owner->address)
                                                                        <small
                                                                            class="d-block"><strong>العنوان:</strong>
                                                                            {{ $owner->address }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quotation Types Section -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border-dark">
                                        <div class="card-header ">
                                            <h5>معلومات العروض السعرية المطلوبة</h5>
                                        </div>
                                        <div class="card-body">
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
                                                                        <input class="form-check-input"
                                                                            type="checkbox"
                                                                            wire:model="selectedQuotationUnits.{{ $type->id }}.{{ $unit->id }}"
                                                                            id="quotation_unit_{{ $type->id }}_{{ $unit->id }}">
                                                                        <label class="form-check-label small"
                                                                            for="quotation_unit_{{ $type->id }}_{{ $unit->id }}">
                                                                            {{ $unit->name }}
                                                                        </label>
                                                                    </div>
                                                                @empty
                                                                    <p class="text-muted small text-center mb-0">لا
                                                                        توجد وحدات لهذا النوع</p>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <label for="type_note" class="form-label">ملاحظات النوع
                                                        (اختياري)</label>
                                                    <textarea class="form-control" id="type_note" rows="3" wire:model="type_note"
                                                        placeholder="أدخل أي ملاحظات إضافية هنا..."></textarea>
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

                            <!-- Project Documents & Checklists Section -->
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
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Required Submittal Checklist & Working Conditions Section -->
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
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Working Conditions Section -->
                            <div class="row mb-4">
                                <div class="col-12">
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

                                            <!-- عرض النتائج -->
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <div class="row">
                                                            <!-- السكور الإجمالي -->
                                                            <div class="col-md-3">
                                                                <div class="text-center">
                                                                    <i
                                                                        class="fas fa-calculator fa-2x text-primary mb-2"></i>
                                                                    <h5>السكور الإجمالي</h5>
                                                                    <span
                                                                        class="badge bg-primary fs-4">{{ $totalScore }}</span>
                                                                </div>
                                                            </div>

                                                            <!-- النسبة المئوية -->
                                                            <div class="col-md-3">
                                                                <div class="text-center">
                                                                    <i
                                                                        class="fas fa-percent fa-2x text-success mb-2"></i>
                                                                    <h5>النسبة المئوية</h5>
                                                                    <span
                                                                        class="badge bg-success fs-4">{{ $difficultyPercentage }}%</span>
                                                                </div>
                                                            </div>

                                                            <!-- درجة الصعوبة -->
                                                            <div class="col-md-3">
                                                                <div class="text-center">
                                                                    <i
                                                                        class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                                                    <h5>درجة الصعوبة</h5>
                                                                    <span
                                                                        class="badge bg-warning fs-4">{{ $projectDifficulty }}</span>
                                                                </div>
                                                            </div>

                                                            <!-- تصنيف الصعوبة -->
                                                            <div class="col-md-3">
                                                                <div class="text-center">
                                                                    <i
                                                                        class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                                                    <h5>تصنيف الصعوبة</h5>
                                                                    <span
                                                                        class="badge
                            @if ($projectDifficulty == 1) bg-success
                            @elseif ($projectDifficulty == 2) bg-warning
                            @elseif ($projectDifficulty == 3) bg-orange
                            @else bg-danger @endif fs-5">
                                                                        @if ($projectDifficulty == 1)
                                                                            سهل (أقل من 25%)
                                                                        @elseif ($projectDifficulty == 2)
                                                                            متوسط (25% - 50%)
                                                                        @elseif ($projectDifficulty == 3)
                                                                            صعب (50% - 75%)
                                                                        @elseif ($projectDifficulty == 4)
                                                                            صعب جداً (أكثر من 75%)
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Progress Bar للنسبة المئوية -->
                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <div class="progress" style="height: 30px;">
                                                                    <div class="progress-bar
                            @if ($projectDifficulty == 1) bg-success
                            @elseif ($projectDifficulty == 2) bg-warning
                            @elseif ($projectDifficulty == 3) bg-orange
                            @else bg-danger @endif"
                                                                        role="progressbar"
                                                                        style="width: {{ $difficultyPercentage }}%"
                                                                        aria-valuenow="{{ $difficultyPercentage }}"
                                                                        aria-valuemin="0" aria-valuemax="100">
                                                                        {{ $difficultyPercentage }}%
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

                                                <div class="col-3">
                                                    <label for="document_files" class="form-label fw-bold">
                                                        <i class="fas fa-upload me-2"></i>
                                                        رفع وثائق (ملفات متعددة)
                                                    </label>
                                                    <input type="file" wire:model="documentFiles"
                                                        id="document_files" class="form-control"
                                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>

                                                    @error('documentFiles.*')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror

                                                    <!-- عرض الملفات الموجودة سابقًا -->
                                                    @if (!empty($existingDocuments))
                                                        <div class="mt-3">
                                                            <h6 class="fw-bold mb-2 text-info">الملفات المحفوظة سابقًا
                                                                ({{ count($existingDocuments) }}):</h6>
                                                            <div class="list-group mb-3">
                                                                @foreach ($existingDocuments as $doc)
                                                                    <div
                                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                            <i
                                                                                class="fas fa-file-alt text-info me-2"></i>
                                                                            <a href="{{ $doc['url'] }}"
                                                                                target="_blank"
                                                                                class="text-decoration-none">
                                                                                <span
                                                                                    class="text-info">{{ $doc['file_name'] }}</span>
                                                                            </a>
                                                                            <small
                                                                                class="text-muted ms-2">({{ number_format($doc['size'] / 1024, 2) }}
                                                                                KB)</small>
                                                                        </div>
                                                                        <button type="button"
                                                                            wire:click="removeExistingDocument({{ $doc['id'] }})"
                                                                            class="btn btn-sm btn-outline-danger"
                                                                            title="حذف الملف"
                                                                            onclick="return confirm('هل أنت متأكد من حذف هذا الملف؟')">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- عرض الملفات المرفوعة الجديدة -->
                                                    @if (!empty($documentFiles))
                                                        <div class="mt-3">
                                                            <h6 class="fw-bold mb-2 text-success">الملفات المرفوعة
                                                                الجديدة ({{ count($documentFiles) }}):</h6>
                                                            <div class="list-group">
                                                                @foreach ($documentFiles as $index => $file)
                                                                    <div
                                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                                        <div class="d-flex align-items-center">
                                                                            <i
                                                                                class="fas fa-file-alt text-success me-2"></i>
                                                                            <span
                                                                                class="text-success">{{ $file->getClientOriginalName() }}</span>
                                                                            <small
                                                                                class="text-muted ms-2">({{ number_format($file->getSize() / 1024, 2) }}
                                                                                KB)</small>
                                                                        </div>
                                                                        <button type="button"
                                                                            wire:click="removeDocumentFile({{ $index }})"
                                                                            class="btn btn-sm btn-danger"
                                                                            title="حذف الملف">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- Loading indicator -->
                                                    <div wire:loading wire:target="documentFiles" class="mt-2">
                                                        <div class="spinner-border spinner-border-sm text-primary"
                                                            role="status">
                                                            <span class="visually-hidden">جاري الرفع...</span>
                                                        </div>
                                                        <small class="text-primary ms-2">جاري رفع الملفات...</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <livewire:app::searchable-select :model="App\Models\Client::class"
                                                        label-field="cname" :selected-id="$assignedEngineer"
                                                        wire-model="assignedEngineer" label="المهندس"
                                                        placeholder="ابحث عن المهندس أو أضف جديد..." :where="['type' => \App\Enums\ClientType::ENGINEER->value]"
                                                        :additional-data="['type' => \App\Enums\ClientType::ENGINEER->value]" :key="'engineer-select-edit-' . $inquiryId" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Temporary Comments Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-comments me-2"></i>
                                                التعليقات والملاحظات
                                            </h6>
                                            <small class="d-block mt-1">سيتم حفظ التعليقات مع الاستفسار</small>
                                        </div>
                                        <div class="card-body">
                                            <!-- Form لإضافة تعليق -->
                                            <div class="mb-3">
                                                <label for="newTempComment" class="form-label fw-bold">
                                                    <i class="fas fa-pen me-2"></i>
                                                    أضف ملاحظة
                                                </label>
                                                <div class="input-group">
                                                    <textarea wire:model="newTempComment" id="newTempComment" class="form-control" rows="2"
                                                        placeholder="اكتب ملاحظاتك هنا..."></textarea>
                                                    <button type="button" wire:click="addTempComment"
                                                        class="btn btn-primary">
                                                        <i class="fas fa-plus"></i>
                                                        إضافة
                                                    </button>
                                                </div>
                                                @error('newTempComment')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- عرض التعليقات المحفوظة -->
                                            @if (!empty($existingComments))
                                                <div class="mb-4">
                                                    <h6 class="fw-bold text-dark mb-3">
                                                        <i class="fas fa-history me-2"></i>
                                                        التعليقات المحفوظة سابقاً
                                                    </h6>
                                                    @foreach ($existingComments as $comment)
                                                        <div
                                                            class="alert alert-dark d-flex justify-content-between align-items-start mb-2">
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
                                            @endif

                                            <!-- عرض التعليقات المؤقتة -->
                                            @if (!empty($tempComments))
                                                <div class="comments-list">
                                                    <h6 class="fw-bold text-primary mb-3">
                                                        <i class="fas fa-sticky-note me-2"></i>
                                                        التعليقات الجديدة
                                                    </h6>
                                                    @foreach ($tempComments as $index => $comment)
                                                        <div
                                                            class="alert alert-info d-flex justify-content-between align-items-start mb-2">
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
                                                                wire:click="removeTempComment({{ $index }})"
                                                                class="btn btn-sm btn-outline-danger ms-2">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif (empty($existingComments) && empty($tempComments))
                                                <div class="alert alert-secondary">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    لا توجد ملاحظات. يمكنك إضافة ملاحظاتك قبل حفظ الاستفسار.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

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

    @include('inquiries::components.addClientModal')

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

                // Handle prepopulation events
                Livewire.on('prepopulateWorkTypes', ({
                    steps,
                    path
                }) => {
                    // Clear existing steps
                    removeWorkTypeStepsAfter(1);

                    // Populate steps based on the provided data
                    Object.keys(steps).forEach(stepNum => {
                        const stepId = steps[stepNum];
                        if (stepNum == 1) {
                            document.getElementById('step_1').value = stepId;
                            document.getElementById('step_1').dispatchEvent(new Event('change'));
                        } else {
                            createWorkTypeStepItem(parseInt(stepNum), steps[stepNum - 1]);
                            // Wait for DOM update then set value
                            setTimeout(() => {
                                const select = document.getElementById(`step_${stepNum}`);
                                if (select) {
                                    select.value = stepId;
                                    select.dispatchEvent(new Event('change'));
                                }
                            }, 100);
                        }
                    });
                });

                Livewire.on('prepopulateInquirySources', ({
                    steps,
                    path
                }) => {
                    // Clear existing steps
                    removeInquirySourceStepsAfter(1);

                    // Populate steps based on the provided data
                    Object.keys(steps).forEach(stepNum => {
                        const stepId = steps[stepNum];
                        if (stepNum == 1) {
                            document.getElementById('inquiry_source_step_1').value = stepId;
                            document.getElementById('inquiry_source_step_1').dispatchEvent(new Event(
                                'change'));
                        } else {
                            createInquirySourceStepItem(parseInt(stepNum), steps[stepNum - 1]);
                            // Wait for DOM update then set value
                            setTimeout(() => {
                                const select = document.getElementById(
                                    `inquiry_source_step_${stepNum}`);
                                if (select) {
                                    select.value = stepId;
                                    select.dispatchEvent(new Event('change'));
                                }
                            }, 100);
                        }
                    });
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

                // Client Modal Events
                Livewire.on('openClientModal', () => {
                    const modal = new bootstrap.Modal(document.getElementById('clientModal'));
                    modal.show();
                });

                Livewire.on('closeClientModal', () => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('clientModal'));
                    modal.hide();
                });
            });
        </script>
    @endpush
</div>
