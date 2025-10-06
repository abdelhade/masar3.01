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
                        <input type="date" wire:model="estimationStartDate" class="form-control">
                        @error('startDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">تاريخ الانتهاء</label>
                        <input type="date" wire:model="estimationFinishedDate" class="form-control">
                        @error('finishedDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">تاريخ التقديم</label>
                        <input type="date" wire:model="submittingDate" class="form-control">
                        @error('submittingDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">القيمة الإجمالية للمشروع</label>
                        <input type="number" wire:model="totalProjectValue" class="form-control"
                            placeholder="أدخل القيمة...">
                        @error('totalProjectValue')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-3">
                        <label for="document_files" class="form-label fw-bold">
                            <i class="fas fa-upload me-2"></i>
                            رفع وثائق (ملفات متعددة)
                        </label>
                        <input type="file" wire:model="documentFiles" id="document_files" class="form-control"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>

                        @error('documentFiles.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                        {{-- عرض الملفات الموجودة سابقًا (في التعديل فقط، فارغ في الإنشاء) --}}
                        @if (!empty($existingDocuments ?? []))
                            <div class="mt-3">
                                <h6 class="fw-bold mb-2 text-info">الملفات المحفوظة سابقًا
                                    ({{ count($existingDocuments ?? []) }}):</h6>
                                <div class="list-group mb-3">
                                    @foreach ($existingDocuments ?? [] as $doc)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-info me-2"></i>
                                                <a href="{{ $doc['url'] }}" target="_blank"
                                                    class="text-decoration-none">
                                                    <span class="text-info">{{ $doc['file_name'] }}</span>
                                                </a>
                                                <small
                                                    class="text-muted ms-2">({{ number_format($doc['size'] / 1024, 2) }}
                                                    KB)</small>
                                            </div>
                                            <button type="button"
                                                wire:click="removeExistingDocument({{ $doc['id'] }})"
                                                class="btn btn-sm btn-outline-danger" title="حذف الملف"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا الملف؟')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- عرض الملفات المرفوعة الجديدة --}}
                        @if (!empty($documentFiles))
                            <div class="mt-3">
                                <h6 class="fw-bold mb-2 text-success">الملفات المرفوعة الجديدة
                                    ({{ count($documentFiles) }}):</h6>
                                <div class="list-group">
                                    @foreach ($documentFiles as $index => $file)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-success me-2"></i>
                                                <span class="text-success">{{ $file->getClientOriginalName() }}</span>
                                                <small
                                                    class="text-muted ms-2">({{ number_format($file->getSize() / 1024, 2) }}
                                                    KB)</small>
                                            </div>
                                            <button type="button" wire:click="removeDocumentFile({{ $index }})"
                                                class="btn btn-sm btn-danger" title="حذف الملف">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Loading indicator --}}
                        <div wire:loading wire:target="documentFiles" class="mt-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">جاري الرفع...</span>
                            </div>
                            <small class="text-primary ms-2">جاري رفع الملفات...</small>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname" :selected-id="$assignedEngineer"
                            wire-model="assignedEngineer" label="المهندس"
                            placeholder="ابحث عن المهندس أو أضف جديد..." :where="[
                                'type' => \App\Enums\ClientType::ENGINEER->value,
                            ]" :additional-data="[
                                'type' => \App\Enums\ClientType::ENGINEER->value,
                            ]"
                            :key="'engineer-select'" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
