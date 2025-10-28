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
                                            {{ __('Project Data') }}
                                        </h2>
                                        <small
                                            class="d-block mt-1">{{ __('Basic project information and important dates') }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3 d-flex flex-column">
                                                <label class="form-label fw-bold">{{ __('Project') }}</label>
                                                <livewire:app::searchable-select :model="Modules\Progress\Models\ProjectProgress::class" label-field="name"
                                                    wire-model="projectId"
                                                    placeholder="{{ __('Search for project or add new...') }}"
                                                    :key="'project-select-edit-' . $inquiryId" :selected-id="$projectId" />
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label class="form-label fw-bold">{{ __('Tender Number') }}</label>
                                                <input type="text" wire:model="tenderNo" class="form-control">
                                                @error('tenderNo')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold">{{ __('Tender ID') }}</label>
                                                <input type="text" wire:model="tenderId" class="form-control">
                                                @error('tenderId')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-1 mb-3">
                                                <label class="form-label fw-bold">{{ __('Quotation Status') }}</label>
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
                                                    <input type="text" wire:model.live="quotationStateReason"
                                                        class="form-control" placeholder="{{ __('Enter reason...') }}">
                                                    @error('quotationStateReason')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            @endif

                                            <div class="col-md-2 mb-3">
                                                <label class="form-label fw-bold">{{ __('Inquiry Status') }}</label>
                                                <select wire:model="status" class="form-select">
                                                    <option value="">{{ __('Select status...') }}</option>
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
                                                <label class="form-label fw-bold">{{ __('KON Status') }}</label>
                                                <select wire:model="statusForKon" class="form-select">
                                                    <option value="">{{ __('Select...') }}</option>
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
                                                <label class="form-label fw-bold">{{ __('KON Title') }}</label>
                                                <select wire:model="konTitle" class="form-select">
                                                    <option value="">{{ __('Select title...') }}</option>
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
                                                <label class="form-label fw-bold">{{ __('City') }}</label>
                                                <select wire:model.live="cityId" class="form-select">
                                                    <option value="">{{ __('Select city...') }}</option>
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
                                                <label class="form-label fw-bold">{{ __('Area') }}</label>
                                                <select wire:model.live="townId" class="form-select">
                                                    <option value="">{{ __('Select area...') }}</option>
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
                                                <label class="form-label fw-bold">{{ __('Distance (km)') }}</label>
                                                <input type="number" step="0.01" wire:model="townDistance"
                                                    class="form-control"
                                                    placeholder="{{ __('Distance in kilometers') }}">
                                                @error('townDistance')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label class="form-label fw-bold">{{ __('Inquiry Date') }}</label>
                                                <input type="date" wire:model="inquiryDate" class="form-control">
                                                @error('inquiryDate')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label
                                                    class="form-label fw-bold">{{ __('Required Delivery Date') }}</label>
                                                <input type="date" wire:model="reqSubmittalDate"
                                                    class="form-control">
                                                @error('reqSubmittalDate')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label
                                                    class="form-label fw-bold">{{ __('Project Start Date') }}</label>
                                                <input type="date" wire:model="projectStartDate"
                                                    class="form-control">
                                                @error('projectStartDate')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-bold">{{ __('Project Image') }}</label>
                                                <input type="file" wire:model="projectImage" id="projectImage"
                                                    class="form-control @error('projectImage') is-invalid @enderror">
                                                @error('projectImage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror

                                                @if ($existingProjectImage)
                                                    <div class="mt-2">
                                                        <img src="{{ $existingProjectImage->getUrl() }}"
                                                            alt="{{ __('Current Project Image') }}"
                                                            class="img-thumbnail" style="max-height: 150px;">
                                                        <button type="button" wire:click="removeProjectImage"
                                                            class="btn btn-sm btn-danger mt-1"
                                                            onclick="return confirm('{{ __('Are you sure you want to delete the image?') }}')">
                                                            <i class="fas fa-trash"></i> {{ __('Delete Image') }}
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
                                            {{ __('Pricing Status') }}
                                        </h6>
                                        <small class="d-block mt-1">{{ __('Select pricing status') }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-md-2 mb-3">
                                                <label class="form-label fw-bold">{{ __('Project Size') }}</label>
                                                <select wire:model="projectSize" class="form-select">
                                                    <option value="">{{ __('Select project size...') }}</option>
                                                    @foreach ($projectSizeOptions as $size)
                                                        <option value="{{ $size->id }}">{{ $size->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('projectSize')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>


                                            <div class="col-md-2 mb-3">
                                                <label class="form-label fw-bold">{{ __('KON Priority') }}</label>
                                                <select wire:model="konPriority" class="form-select">
                                                    <option value="">{{ __('Select KON priority...') }}</option>
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
                                                <label class="form-label fw-bold">{{ __('Client Priority') }}</label>
                                                <select wire:model="clientPriority" class="form-select">
                                                    <option value="">{{ __('Select priority...') }}</option>
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
                                            {{ __('Hierarchical Work Classification') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if (!empty($selectedWorkTypes))
                                            <div class="mb-3">
                                                <label class="fw-bold">{{ __('Selected Works:') }}</label>
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

                                        <div id="path_display" class="mb-3 text-success">
                                            @if (!empty($currentWorkPath))
                                                <i class="fas fa-route me-1"></i> {{ __('Current Path:') }}
                                                {{ implode(' → ', $currentWorkPath) }}
                                            @else
                                                <i class="fas fa-info-circle me-1"></i>
                                                {{ __('Select classification') }}
                                            @endif
                                        </div>

                                        <div id="steps_wrapper" wire:ignore>
                                            <div class="row mb-3" id="work_types_row">
                                                <div class="col-md-3" data-step="1">
                                                    <label class="form-label fw-bold">
                                                        <span class="badge bg-primary me-2">1</span>
                                                        {{ __('Main Classification') }}
                                                    </label>
                                                    <select wire:model="currentWorkTypeSteps.step_1" id="step_1"
                                                        class="form-select">
                                                        <option value="">
                                                            {{ __('Select main classification...') }}</option>
                                                        @foreach ($workTypes as $type)
                                                            <option value="{{ $type['id'] }}">
                                                                {{ $type['name'] }}</option>
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
                                                    <input type="text" wire:model="finalWorkType"
                                                        id="final_work_type" class="form-control"
                                                        placeholder="{{ !empty($currentWorkPath) ? __('Enter additional work details:') . ' ' . end($currentWorkPath) : __('Enter detailed work description...') }}">
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
                                        <small
                                            class="d-block mt-1">{{ __('Select inquiry source through hierarchy') }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div id="inquiry_sources_path_display" class="mb-3 text-warning">
                                            @if (!empty($selectedInquiryPath))
                                                <i class="fas fa-route text-warning me-1"></i>
                                                {{ __('Selected Path:') }}
                                                {{ implode(' → ', $selectedInquiryPath) }}
                                            @else
                                                <i class="fas fa-info-circle me-1"></i>
                                                {{ __('Select source first to see path') }}
                                            @endif
                                        </div>
                                        <div id="inquiry_sources_steps_wrapper" wire:ignore>
                                            <div class="row mb-3" id="inquiry_sources_row">
                                                <div class="col-md-3" data-step="1">
                                                    <label class="form-label fw-bold">
                                                        <span class="badge bg-warning text-dark me-2">1</span>
                                                        {{ __('Main Source') }}
                                                    </label>
                                                    <select wire:model="inquirySourceSteps.inquiry_source_step_1"
                                                        id="inquiry_source_step_1" class="form-select">
                                                        <option value="">{{ __('Select main source...') }}
                                                        </option>
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
                                                        <input type="text" wire:model="finalInquirySource"
                                                            id="final_inquiry_source" class="form-control"
                                                            placeholder="{{ !empty($selectedInquiryPath) ? __('Enter additional source details:') . ' ' . end($selectedInquiryPath) : __('Enter detailed source description...') }}">
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

                    <div class="row mb-4">
                        <!-- Stakeholders Section -->
                        <div class="col-12">
                            <div class="card border-dark">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        {{ __('Stakeholders') }}
                                    </h6>
                                    <small
                                        class="d-block mt-1">{{ __('Identify all parties involved in the project') }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Client -->
                                        <div class="col-md-3 mb-3 d-flex flex-column">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-user-tie fa-2x text-primary"></i>
                                                </div>
                                                <label class="form-label fw-bold">{{ __('Client') }}</label>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="flex-grow-1">
                                                        <livewire:app::searchable-select :model="App\Models\Client::class"
                                                            label-field="cname" wire-model="clientId"
                                                            placeholder="ابحث عن العميل أو أضف جديد..."
                                                            :selected-id="$clientId" :key="'client-select-edit-' . $inquiryId" />
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        wire:click="openClientModal(1)"
                                                        title="{{ __('Add new client') }}">
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
                                                                <small
                                                                    class="d-block"><strong>{{ __('Name') }}:</strong>
                                                                    {{ $client->cname }}</small>
                                                                @if ($client->phone)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                                        {{ $client->phone }}</small>
                                                                @endif
                                                                @if ($client->email)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Email') }}:</strong>
                                                                        {{ $client->email }}</small>
                                                                @endif
                                                                @if ($client->address)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Address') }}:</strong>
                                                                        {{ $client->address }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Main Contractor -->
                                        <div class="col-md-3 mb-3 d-flex flex-column">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-hard-hat fa-2x text-warning"></i>
                                                </div>
                                                <label class="form-label fw-bold">{{ __('Main Contractor') }}</label>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="flex-grow-1">
                                                        <livewire:app::searchable-select :model="App\Models\Client::class"
                                                            label-field="cname" wire-model="mainContractorId"
                                                            :selected-id="$mainContractorId"
                                                            placeholder="{{ __('Search or add new contractor...') }}"
                                                            :key="'contractor-select-edit-' . $inquiryId" />
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        wire:click="openClientModal(2)"
                                                        title="{{ __('Add new contractor') }}">
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
                                                                <small
                                                                    class="d-block"><strong>{{ __('Name') }}:</strong>
                                                                    {{ $contractor->cname }}</small>
                                                                @if ($contractor->phone)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                                        {{ $contractor->phone }}</small>
                                                                @endif
                                                                @if ($contractor->email)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Email') }}:</strong>
                                                                        {{ $contractor->email }}</small>
                                                                @endif
                                                                @if ($contractor->address)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Address') }}:</strong>
                                                                        {{ $contractor->address }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Consultant -->
                                        <div class="col-md-3 mb-3 d-flex flex-column">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-user-graduate fa-2x text-info"></i>
                                                </div>
                                                <label class="form-label fw-bold">{{ __('Consultant') }}</label>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="flex-grow-1">
                                                        <livewire:app::searchable-select :model="App\Models\Client::class"
                                                            label-field="cname" wire-model="consultantId"
                                                            :selected-id="$consultantId" :key="'consultant-select-edit-' . $inquiryId" />
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-info"
                                                        wire:click="openClientModal(3)"
                                                        title="{{ __('Add new consultant') }}">
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
                                                                <small
                                                                    class="d-block"><strong>{{ __('Name') }}:</strong>
                                                                    {{ $consultant->cname }}</small>
                                                                @if ($consultant->phone)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                                        {{ $consultant->phone }}</small>
                                                                @endif
                                                                @if ($consultant->email)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Email') }}:</strong>
                                                                        {{ $consultant->email }}</small>
                                                                @endif
                                                                @if ($consultant->address)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Address') }}:</strong>
                                                                        {{ $consultant->address }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Owner -->
                                        <div class="col-md-3 mb-3 d-flex flex-column">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-crown fa-2x text-success"></i>
                                                </div>
                                                <label class="form-label fw-bold">{{ __('Owner') }}</label>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="flex-grow-1">
                                                        <livewire:app::searchable-select :model="App\Models\Client::class"
                                                            label-field="cname" wire-model="ownerId"
                                                            placeholder="{{ __('Search for owner or add new...') }}"
                                                            :selected-id="$ownerId" :key="'owner-select-edit-' . $inquiryId" />
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        wire:click="openClientModal(4)"
                                                        title="{{ __('Add new owner') }}">
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
                                                                <small
                                                                    class="d-block"><strong>{{ __('Name') }}:</strong>
                                                                    {{ $owner->cname }}</small>
                                                                @if ($owner->phone)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                                        {{ $owner->phone }}</small>
                                                                @endif
                                                                @if ($owner->email)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Email') }}:</strong>
                                                                        {{ $owner->email }}</small>
                                                                @endif
                                                                @if ($owner->address)
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Address') }}:</strong>
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
                        @include('inquiries::components.addClientModal')

                        <!-- Quotation Types Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-dark">
                                    <div class="card-header ">
                                        <h5>{{ __('Required Quotations Information') }}</h5>
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
                                                                    {{ __('No units for this type') }}</p>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <label for="type_note"
                                                    class="form-label">{{ __('Type Notes (optional)') }}</label>
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

                        <!-- Project Documents & Checklists Section -->
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

                            <!-- Required Submittal Checklist -->
                            <div class="col-6">
                                <div class="card border-success">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-check-square me-2"></i>
                                            {{ __('Required Submittal Checklist') }}
                                        </h6>
                                        <small
                                            class="d-block mt-1">{{ __('Select required submittals (with score calculation)') }}</small>
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
                                            {{ __('Working Conditions List') }}
                                        </h6>
                                        <small
                                            class="d-block mt-1">{{ __('Select conditions (with score calculation)') }}</small>
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
                                                            <option value="">{{ __('Select...') }}</option>
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

                                        <!-- Results Display -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <div class="row">
                                                        <!-- Total Score -->
                                                        <div class="col-md-3">
                                                            <div class="text-center">
                                                                <i
                                                                    class="fas fa-calculator fa-2x text-primary mb-2"></i>
                                                                <h5>{{ __('Total Score') }}</h5>
                                                                <span
                                                                    class="badge bg-primary fs-4">{{ $totalScore }}</span>
                                                            </div>
                                                        </div>

                                                        <!-- Percentage -->
                                                        <div class="col-md-3">
                                                            <div class="text-center">
                                                                <i class="fas fa-percent fa-2x text-success mb-2"></i>
                                                                <h5>{{ __('Percentage') }}</h5>
                                                                <span
                                                                    class="badge bg-success fs-4">{{ $difficultyPercentage }}%</span>
                                                            </div>
                                                        </div>

                                                        <!-- Difficulty Level -->
                                                        <div class="col-md-3">
                                                            <div class="text-center">
                                                                <i
                                                                    class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                                                <h5>{{ __('Difficulty Level') }}</h5>
                                                                <span
                                                                    class="badge bg-warning fs-4">{{ $projectDifficulty }}</span>
                                                            </div>
                                                        </div>

                                                        <!-- Difficulty Classification -->
                                                        <div class="col-md-3">
                                                            <div class="text-center">
                                                                <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                                                <h5>{{ __('Difficulty Classification') }}</h5>
                                                                <span
                                                                    class="badge
                                                                            @if ($projectDifficulty == 1) bg-success
                                                                            @elseif ($projectDifficulty == 2) bg-warning
                                                                            @elseif ($projectDifficulty == 3) bg-orange
                                                                            @else bg-danger @endif fs-5">
                                                                    @if ($projectDifficulty == 1)
                                                                        {{ __('Easy (Less than 25%)') }}
                                                                    @elseif ($projectDifficulty == 2)
                                                                        {{ __('Medium (25% - 50%)') }}
                                                                    @elseif ($projectDifficulty == 3)
                                                                        {{ __('Hard (50% - 75%)') }}
                                                                    @elseif ($projectDifficulty == 4)
                                                                        {{ __('Very Hard (More than 75%)') }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Progress Bar -->
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
                                            {{ __('Estimation Information') }}
                                        </h6>
                                        <small
                                            class="d-block mt-1">{{ __('Estimation and pricing details') }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-bold">{{ __('Start Date') }}</label>
                                                <input type="date" wire:model="estimationStartDate"
                                                    class="form-control">
                                                @error('estimationStartDate')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-bold">{{ __('Finish Date') }}</label>
                                                <input type="date" wire:model="estimationFinishedDate"
                                                    class="form-control">
                                                @error('estimationFinishedDate')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-bold">{{ __('Submission Date') }}</label>
                                                <input type="date" wire:model="submittingDate"
                                                    class="form-control">
                                                @error('submittingDate')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label
                                                    class="form-label fw-bold">{{ __('Total Project Value') }}</label>
                                                <input type="number" wire:model="totalProjectValue"
                                                    class="form-control" placeholder="{{ __('Enter value...') }}">
                                                @error('totalProjectValue')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-3">
                                                <label for="document_files" class="form-label fw-bold">
                                                    <i class="fas fa-upload me-2"></i>
                                                    {{ __('Upload Documents (multiple files)') }}
                                                </label>
                                                <input type="file" wire:model="documentFiles" id="document_files"
                                                    class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                    multiple>

                                                @error('documentFiles.*')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror

                                                @if (!empty($existingDocuments))
                                                    <div class="mt-3">
                                                        <h6 class="fw-bold mb-2 text-info">
                                                            {{ __('Previously Saved Files') }}
                                                            ({{ count($existingDocuments) }}):</h6>
                                                        <div class="list-group mb-3">
                                                            @foreach ($existingDocuments as $doc)
                                                                <div
                                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fas fa-file-alt text-info me-2"></i>
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
                                                                        title="{{ __('Delete File') }}"
                                                                        onclick="return confirm('{{ __('Are you sure you want to delete this file?') }}')">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!empty($documentFiles))
                                                    <div class="mt-3">
                                                        <h6 class="fw-bold mb-2 text-success">
                                                            {{ __('Newly Uploaded Files') }}
                                                            ({{ count($documentFiles) }}):</h6>
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
                                                                        title="{{ __('Delete File') }}">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <div wire:loading wire:target="documentFiles" class="mt-2">
                                                    <div class="spinner-border spinner-border-sm text-primary"
                                                        role="status">
                                                        <span class="visually-hidden">{{ __('Uploading...') }}</span>
                                                    </div>
                                                    <small
                                                        class="text-primary ms-2">{{ __('Uploading files...') }}</small>
                                                </div>
                                            </div>

                                            <div class="col-md-2 mb-3 d-flex flex-column">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-user-cog fa-2x text-secondary"></i>
                                                    </div>
                                                    <label
                                                        class="form-label fw-bold">{{ __('Assigned Engineer') }}</label>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <div class="flex-grow-1">
                                                            <livewire:app::searchable-select :model="App\Models\Client::class"
                                                                label-field="cname" wire-model="assignedEngineer"
                                                                placeholder="{{ __('Search for engineer or add new...') }}"
                                                                :selected-id="$assignedEngineer" :key="'engineer-select-edit-' . $inquiryId" />
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-secondary"
                                                            wire:click="openClientModal(5)"
                                                            title="{{ __('Add new engineer') }}">
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
                                                                    <small
                                                                        class="d-block"><strong>{{ __('Name') }}:</strong>
                                                                        {{ $engineer->cname }}</small>
                                                                    @if ($engineer->phone)
                                                                        <small
                                                                            class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                                            {{ $engineer->phone }}</small>
                                                                    @endif
                                                                    @if ($engineer->email)
                                                                        <small
                                                                            class="d-block"><strong>{{ __('Email') }}:</strong>
                                                                            {{ $engineer->email }}</small>
                                                                    @endif
                                                                    @if ($engineer->address)
                                                                        <small
                                                                            class="d-block"><strong>{{ __('Address') }}:</strong>
                                                                            {{ $engineer->address }}</small>
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
                        </div>

                        <!-- Temporary Comments Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-comments me-2"></i>
                                            {{ __('Comments and Notes') }}
                                        </h6>
                                        <small
                                            class="d-block mt-1">{{ __('Comments will be saved with the inquiry') }}</small>
                                    </div>
                                    <div class="card-body">
                                        <!-- Form لإضافة تعليق -->
                                        <div class="mb-3">
                                            <label for="newTempComment" class="form-label fw-bold">
                                                <i class="fas fa-pen me-2"></i>
                                                {{ __('Add Note') }}
                                            </label>
                                            <div class="input-group">
                                                <textarea wire:model="newTempComment" id="newTempComment" class="form-control" rows="2"
                                                    placeholder="{{ __('Write your notes here...') }}"></textarea>
                                                <button type="button" wire:click="addTempComment"
                                                    class="btn btn-primary">
                                                    <i class="fas fa-plus"></i>
                                                    {{ __('Add') }}
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
                                                    {{ __('Previously Saved Comments') }}
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
                                                            onclick="return confirm('{{ __('Are you sure you want to delete this comment?') }}')">
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
                                                    {{ __('New Comments') }}
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
                                                {{ __('No notes found. You can add your notes before saving the inquiry.') }}
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
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>
                                        {{ __('Update Inquiry') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
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
                            {{ __('Category') }} ${nextStepNum}
                            </label>
                            <select wire:model.live="currentWorkTypeSteps.step_${nextStepNum}" id="step_${nextStepNum}" class="form-select">
                                <option value="">{{ __('Select Step') }} ${nextStepNum}...</option>
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
                        select.innerHTML = `<option value="">{{ __('Select Step') }} ${nextStepNum}...</option>`;
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
                                {{ __('Source') }} ${nextStepNum}
                            </label>
                            <select wire:model.live="inquirySourceSteps.inquiry_source_step_${nextStepNum}" id="inquiry_source_step_${nextStepNum}" class="form-select">
                                <option value="">{{ __('Select Step') }} ${nextStepNum}...</option>
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
                        select.innerHTML = `<option value="">{{ __('Select Step') }} ${nextStepNum}...</option>`;
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
