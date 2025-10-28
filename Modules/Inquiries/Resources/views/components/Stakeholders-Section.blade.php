<div class="row mb-4">
    <div class="col-12">
        <div class="card border-dark">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    {{ __('Stakeholders') }}
                </h6>
                <small class="d-block mt-1">{{ __('Identify all parties involved in the project') }}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- العميل -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                            </div>
                            <label class="form-label fw-bold">{{ __('Client') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="clientId" placeholder="{{ __('Search for client or add new...') }}"
                                        :selected-id="$clientId" :key="'client-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" wire:click="openClientModal(1)"
                                    title="{{ __('Add New Client') }}">
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
                                            <small class="d-block"><strong>{{ __('Name') }}:</strong>
                                                {{ $client->cname }}</small>
                                            @if ($client->phone)
                                                <small class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                    {{ $client->phone }}</small>
                                            @endif
                                            @if ($client->email)
                                                <small class="d-block"><strong>{{ __('Email') }}:</strong>
                                                    {{ $client->email }}</small>
                                            @endif
                                            @if ($client->address)
                                                <small class="d-block"><strong>{{ __('Address') }}:</strong>
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
                            <label class="form-label fw-bold">{{ __('Main Contractor') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="mainContractorId" :selected-id="$mainContractorId"
                                        placeholder="{{ __('Search or add new contractor...') }}" :key="'contractor-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-warning" wire:click="openClientModal(2)"
                                    title="{{ __('Add New Contractor') }}">
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
                                            <small class="d-block"><strong>{{ __('Name') }}:</strong>
                                                {{ $contractor->cname }}</small>
                                            @if ($contractor->phone)
                                                <small class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                    {{ $contractor->phone }}</small>
                                            @endif
                                            @if ($contractor->email)
                                                <small class="d-block"><strong>{{ __('Email') }}:</strong>
                                                    {{ $contractor->email }}</small>
                                            @endif
                                            @if ($contractor->address)
                                                <small class="d-block"><strong>{{ __('Address') }}:</strong>
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
                            <label class="form-label fw-bold">{{ __('Consultant') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="consultantId"
                                        placeholder="{{ __('Search for consultant or add new...') }}" :selected-id="$consultantId"
                                        :key="'consultant-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-info" wire:click="openClientModal(3)"
                                    title="{{ __('Add New Consultant') }}">
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
                                            <small class="d-block"><strong>{{ __('Name') }}:</strong>
                                                {{ $consultant->cname }}</small>
                                            @if ($consultant->phone)
                                                <small class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                    {{ $consultant->phone }}</small>
                                            @endif
                                            @if ($consultant->email)
                                                <small class="d-block"><strong>{{ __('Email') }}:</strong>
                                                    {{ $consultant->email }}</small>
                                            @endif
                                            @if ($consultant->address)
                                                <small class="d-block"><strong>{{ __('Address') }}:</strong>
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
                            <label class="form-label fw-bold">{{ __('Owner') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="ownerId" placeholder="{{ __('Search for owner or add new...') }}"
                                        :selected-id="$ownerId" :key="'owner-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-success" wire:click="openClientModal(4)"
                                    title="{{ __('Add New Owner') }}">
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
                                            <small class="d-block"><strong>{{ __('Name') }}:</strong>
                                                {{ $owner->cname }}</small>
                                            @if ($owner->phone)
                                                <small class="d-block"><strong>{{ __('Phone') }}:</strong>
                                                    {{ $owner->phone }}</small>
                                            @endif
                                            @if ($owner->email)
                                                <small class="d-block"><strong>{{ __('Email') }}:</strong>
                                                    {{ $owner->email }}</small>
                                            @endif
                                            @if ($owner->address)
                                                <small class="d-block"><strong>{{ __('Address') }}:</strong>
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
</div>

@include('inquiries::components.addClientModal')
