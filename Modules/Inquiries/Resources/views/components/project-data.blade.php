<!-- Project Data Section -->
<div class="row mb-4 ">
    <div class="col-12 ">
        <div class="card border-success ">
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
                        <livewire:app::searchable-select :model="Modules\Progress\Models\ProjectProgress::class" label-field="name" wire-model="projectId"
                            placeholder="ابحث عن المشروع أو أضف جديد..." :key="'project-select'" :selected-id="$projectId" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">رقم المناقصة</label>
                        <input type="text" wire:model="tenderNo" class="form-control" readonly>
                        @error('tenderNo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">معرف المناقصة</label>
                        <input type="text" wire:model="tenderId" class="form-control" readonly>
                        @error('tenderId')
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
                        <input type="number" step="0.01" wire:model="townDistance" class="form-control"
                            placeholder="المسافة بالكيلومتر">
                        @error('distance')
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
                        <label class="form-label fw-bold">تاريخ التسليم </label>
                        <input type="date" wire:model="reqSubmittalDate" class="form-control">
                        @error('reqSubmittalDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">تاريخ بدء المشروع</label>
                        <input type="date" wire:model="projectStartDate" class="form-control">
                        @error('projectStartDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
