<!-- Estimation Information Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    {{ __('Estimation Information') }}
                </h6>
                <small class="d-block mt-1">{{ __('Estimation and Pricing Details') }}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">{{ __('Start Date') }}</label>
                        <input type="date" wire:model="estimationStartDate" class="form-control">
                        @error('startDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">{{ __('End Date') }}</label>
                        <input type="date" wire:model="estimationFinishedDate" class="form-control">
                        @error('finishedDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">{{ __('Submission Date') }}</label>
                        <input type="date" wire:model="submittingDate" class="form-control">
                        @error('submittingDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">{{ __('Total Project Value') }}</label>
                        <input type="number" wire:model="totalProjectValue" class="form-control"
                            placeholder="{{ __('Enter the value...') }}">
                        @error('totalProjectValue')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-3">
                        <label for="document_files" class="form-label fw-bold">
                            <i class="fas fa-upload me-2"></i>
                            {{ __('Upload Documents (Multiple Files)') }}
                        </label>
                        <input type="file" wire:model="documentFiles" id="document_files" class="form-control"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>

                        @error('documentFiles.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                        {{-- عرض الملفات الموجودة سابقًا (في التعديل فقط، فارغ في الإنشاء) --}}
                        @if (!empty($existingDocuments ?? []))
                            <div class="mt-3">
                                <h6 class="fw-bold mb-2 text-info">
                                    {{ __('Previously Saved Files') }} ({{ count($existingDocuments ?? []) }}):
                                </h6>
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
                                                    {{ __('KB') }})</small>
                                            </div>
                                            <button type="button"
                                                wire:click="removeExistingDocument({{ $doc['id'] }})"
                                                class="btn btn-sm btn-outline-danger" title="{{ __('Delete File') }}"
                                                onclick="return confirm('{{ __('Are you sure you want to delete this file?') }}')">
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
                                <h6 class="fw-bold mb-2 text-success">
                                    {{ __('Newly Uploaded Files') }} ({{ count($documentFiles) }}):
                                </h6>
                                <div class="list-group">
                                    @foreach ($documentFiles as $index => $file)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-success me-2"></i>
                                                <span class="text-success">{{ $file->getClientOriginalName() }}</span>
                                                <small
                                                    class="text-muted ms-2">({{ number_format($file->getSize() / 1024, 2) }}
                                                    {{ __('KB') }})</small>
                                            </div>
                                            <button type="button" wire:click="removeDocumentFile({{ $index }})"
                                                class="btn btn-sm btn-danger" title="{{ __('Delete File') }}">
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
                                <span class="visually-hidden">{{ __('Uploading...') }}</span>
                            </div>
                            <small class="text-primary ms-2">{{ __('Uploading files...') }}</small>
                        </div>
                    </div>

                    <!-- المهندس -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-cog fa-2x text-dark"></i>
                            </div>
                            <label class="form-label fw-bold">{{ __('Engineer') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="assignedEngineer"
                                        placeholder="{{ __('Search for engineer or add new...') }}" :selected-id="$assignedEngineer"
                                        :key="'engineer-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-dark" wire:click="openClientModal(5)"
                                    title="{{ __('Add New Engineer') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @if ($assignedEngineer)
                                @php
                                    $engineer = \App\Models\Client::find($assignedEngineer);
                                @endphp
                                @if ($engineer)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block"><strong>{{ __('Name') }}:</strong>
                                                {{ $engineer->cname }}</small>
                                            @if ($engineer->phone)
                                                <small class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                    {{ $engineer->phone }}</small>
                                            @endif
                                            @if ($engineer->email)
                                                <small class="d-block"><strong>{{ __('Email') }}:</strong>
                                                    {{ $engineer->email }}</small>
                                            @endif
                                            @if ($engineer->address)
                                                <small class="d-block"><strong>{{ __('Address') }}:</strong>
                                                    {{ $engineer->address }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('Pricing Status') }}</label>
                        <select wire:model.live="quotationState" class="form-select">
                            <option value="">{{ __('Select status...') }}</option>
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
                            <label class="form-label fw-bold">{{ __('Status Reason') }}</label>
                            <input type="text" wire:model.live="quotationStateReason" class="form-control"
                                placeholder="{{ __('Enter reason...') }}">
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
