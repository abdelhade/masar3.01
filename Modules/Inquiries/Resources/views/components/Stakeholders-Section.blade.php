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
                    <!-- العميل (Client) -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                            </div>
                            <label class="form-label fw-bold">{{ __('Client') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <select class="form-select" wire:model.live="selectedContacts.client">
                                        <option value="">{{ __('Search for client or add new...') }}</option>
                                        @foreach ($contacts as $contact)
                                            <option value="{{ $contact['id'] }}">
                                                {{ $contact['name'] }}
                                                @if ($contact['type'] === 'company')
                                                    ({{ __('Company') }})
                                                @endif
                                                @if (!empty($contact['parent_id']))
                                                    @php
                                                        $parent = collect($contacts)->firstWhere(
                                                            'id',
                                                            $contact['parent_id'],
                                                        );
                                                    @endphp
                                                    @if ($parent)
                                                        - {{ $parent['name'] }}
                                                    @endif
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" wire:click="openContactModal(1)"
                                    title="{{ __('Add New Client') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @if ($selectedContacts['client'])
                                @php
                                    $contact = collect($contacts)->firstWhere('id', $selectedContacts['client']);
                                @endphp
                                @if ($contact)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block">
                                                <strong>{{ __('Name') }}:</strong> {{ $contact['name'] }}
                                            </small>
                                            <small class="d-block">
                                                <strong>{{ __('Type') }}:</strong>
                                                {{ $contact['type'] === 'company' ? __('Company') : __('Person') }}
                                            </small>
                                            @if ($contact['phone_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Phone') }}:</strong> {{ $contact['phone_1'] }}
                                                </small>
                                            @endif
                                            @if ($contact['email'])
                                                <small class="d-block">
                                                    <strong>{{ __('Email') }}:</strong> {{ $contact['email'] }}
                                                </small>
                                            @endif
                                            @if ($contact['address_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Address') }}:</strong> {{ $contact['address_1'] }}
                                                </small>
                                            @endif
                                            @if (!empty($contact['parent_id']))
                                                @php
                                                    $parent = collect($contacts)->firstWhere(
                                                        'id',
                                                        $contact['parent_id'],
                                                    );
                                                @endphp
                                                @if ($parent)
                                                    <small class="d-block">
                                                        <strong>{{ __('Parent Company') }}:</strong>
                                                        {{ $parent['name'] }}
                                                    </small>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- المقاول الرئيسي (Main Contractor) -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-hard-hat fa-2x text-warning"></i>
                            </div>
                            <label class="form-label fw-bold">{{ __('Main Contractor') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <select class="form-select" wire:model.live="selectedContacts.main_contractor">
                                        <option value="">{{ __('Search or add new contractor...') }}</option>
                                        @foreach ($contacts as $contact)
                                            <option value="{{ $contact['id'] }}">
                                                {{ $contact['name'] }}
                                                @if ($contact['type'] === 'company')
                                                    ({{ __('Company') }})
                                                @endif
                                                @if (!empty($contact['parent_id']))
                                                    @php
                                                        $parent = collect($contacts)->firstWhere(
                                                            'id',
                                                            $contact['parent_id'],
                                                        );
                                                    @endphp
                                                    @if ($parent)
                                                        - {{ $parent['name'] }}
                                                    @endif
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-warning" wire:click="openContactModal(2)"
                                    title="{{ __('Add New Contractor') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @if ($selectedContacts['main_contractor'])
                                @php
                                    $contact = collect($contacts)->firstWhere(
                                        'id',
                                        $selectedContacts['main_contractor'],
                                    );
                                @endphp
                                @if ($contact)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block">
                                                <strong>{{ __('Name') }}:</strong> {{ $contact['name'] }}
                                            </small>
                                            <small class="d-block">
                                                <strong>{{ __('Type') }}:</strong>
                                                {{ $contact['type'] === 'company' ? __('Company') : __('Person') }}
                                            </small>
                                            @if ($contact['phone_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Phone') }}:</strong> {{ $contact['phone_1'] }}
                                                </small>
                                            @endif
                                            @if ($contact['email'])
                                                <small class="d-block">
                                                    <strong>{{ __('Email') }}:</strong> {{ $contact['email'] }}
                                                </small>
                                            @endif
                                            @if ($contact['address_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Address') }}:</strong> {{ $contact['address_1'] }}
                                                </small>
                                            @endif
                                            @if (!empty($contact['parent_id']))
                                                @php
                                                    $parent = collect($contacts)->firstWhere(
                                                        'id',
                                                        $contact['parent_id'],
                                                    );
                                                @endphp
                                                @if ($parent)
                                                    <small class="d-block">
                                                        <strong>{{ __('Parent Company') }}:</strong>
                                                        {{ $parent['name'] }}
                                                    </small>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- الاستشاري (Consultant) -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-graduate fa-2x text-info"></i>
                            </div>
                            <label class="form-label fw-bold">{{ __('Consultant') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <select class="form-select" wire:model.live="selectedContacts.consultant">
                                        <option value="">{{ __('Search for consultant or add new...') }}</option>
                                        @foreach ($contacts as $contact)
                                            <option value="{{ $contact['id'] }}">
                                                {{ $contact['name'] }}
                                                @if ($contact['type'] === 'company')
                                                    ({{ __('Company') }})
                                                @endif
                                                @if (!empty($contact['parent_id']))
                                                    @php
                                                        $parent = collect($contacts)->firstWhere(
                                                            'id',
                                                            $contact['parent_id'],
                                                        );
                                                    @endphp
                                                    @if ($parent)
                                                        - {{ $parent['name'] }}
                                                    @endif
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-info" wire:click="openContactModal(3)"
                                    title="{{ __('Add New Consultant') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @if ($selectedContacts['consultant'])
                                @php
                                    $contact = collect($contacts)->firstWhere('id', $selectedContacts['consultant']);
                                @endphp
                                @if ($contact)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block">
                                                <strong>{{ __('Name') }}:</strong> {{ $contact['name'] }}
                                            </small>
                                            <small class="d-block">
                                                <strong>{{ __('Type') }}:</strong>
                                                {{ $contact['type'] === 'company' ? __('Company') : __('Person') }}
                                            </small>
                                            @if ($contact['phone_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Phone') }}:</strong> {{ $contact['phone_1'] }}
                                                </small>
                                            @endif
                                            @if ($contact['email'])
                                                <small class="d-block">
                                                    <strong>{{ __('Email') }}:</strong> {{ $contact['email'] }}
                                                </small>
                                            @endif
                                            @if ($contact['address_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Address') }}:</strong> {{ $contact['address_1'] }}
                                                </small>
                                            @endif
                                            @if (!empty($contact['parent_id']))
                                                @php
                                                    $parent = collect($contacts)->firstWhere(
                                                        'id',
                                                        $contact['parent_id'],
                                                    );
                                                @endphp
                                                @if ($parent)
                                                    <small class="d-block">
                                                        <strong>{{ __('Parent Company') }}:</strong>
                                                        {{ $parent['name'] }}
                                                    </small>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- المالك (Owner) -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-crown fa-2x text-success"></i>
                            </div>
                            <label class="form-label fw-bold">{{ __('Owner') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <select class="form-select" wire:model.live="selectedContacts.owner">
                                        <option value="">{{ __('Search for owner or add new...') }}</option>
                                        @foreach ($contacts as $contact)
                                            <option value="{{ $contact['id'] }}">
                                                {{ $contact['name'] }}
                                                @if ($contact['type'] === 'company')
                                                    ({{ __('Company') }})
                                                @endif
                                                @if (!empty($contact['parent_id']))
                                                    @php
                                                        $parent = collect($contacts)->firstWhere(
                                                            'id',
                                                            $contact['parent_id'],
                                                        );
                                                    @endphp
                                                    @if ($parent)
                                                        - {{ $parent['name'] }}
                                                    @endif
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-success" wire:click="openContactModal(4)"
                                    title="{{ __('Add New Owner') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @if ($selectedContacts['owner'])
                                @php
                                    $contact = collect($contacts)->firstWhere('id', $selectedContacts['owner']);
                                @endphp
                                @if ($contact)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block">
                                                <strong>{{ __('Name') }}:</strong> {{ $contact['name'] }}
                                            </small>
                                            <small class="d-block">
                                                <strong>{{ __('Type') }}:</strong>
                                                {{ $contact['type'] === 'company' ? __('Company') : __('Person') }}
                                            </small>
                                            @if ($contact['phone_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Phone') }}:</strong> {{ $contact['phone_1'] }}
                                                </small>
                                            @endif
                                            @if ($contact['email'])
                                                <small class="d-block">
                                                    <strong>{{ __('Email') }}:</strong> {{ $contact['email'] }}
                                                </small>
                                            @endif
                                            @if ($contact['address_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Address') }}:</strong> {{ $contact['address_1'] }}
                                                </small>
                                            @endif
                                            @if (!empty($contact['parent_id']))
                                                @php
                                                    $parent = collect($contacts)->firstWhere(
                                                        'id',
                                                        $contact['parent_id'],
                                                    );
                                                @endphp
                                                @if ($parent)
                                                    <small class="d-block">
                                                        <strong>{{ __('Parent Company') }}:</strong>
                                                        {{ $parent['name'] }}
                                                    </small>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- المهندس المسؤول (Engineer) -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-cog fa-2x text-danger"></i>
                            </div>
                            <label class="form-label fw-bold">{{ __('Assigned Engineer') }}</label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <select class="form-select" wire:model.live="selectedContacts.engineer">
                                        <option value="">{{ __('Search for engineer or add new...') }}</option>
                                        @foreach ($contacts as $contact)
                                            <option value="{{ $contact['id'] }}">
                                                {{ $contact['name'] }}
                                                @if ($contact['type'] === 'company')
                                                    ({{ __('Company') }})
                                                @endif
                                                @if (!empty($contact['parent_id']))
                                                    @php
                                                        $parent = collect($contacts)->firstWhere(
                                                            'id',
                                                            $contact['parent_id'],
                                                        );
                                                    @endphp
                                                    @if ($parent)
                                                        - {{ $parent['name'] }}
                                                    @endif
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger" wire:click="openContactModal(5)"
                                    title="{{ __('Add New Engineer') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @if ($selectedContacts['engineer'])
                                @php
                                    $contact = collect($contacts)->firstWhere('id', $selectedContacts['engineer']);
                                @endphp
                                @if ($contact)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block">
                                                <strong>{{ __('Name') }}:</strong> {{ $contact['name'] }}
                                            </small>
                                            <small class="d-block">
                                                <strong>{{ __('Type') }}:</strong>
                                                {{ $contact['type'] === 'company' ? __('Company') : __('Person') }}
                                            </small>
                                            @if ($contact['phone_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Phone') }}:</strong> {{ $contact['phone_1'] }}
                                                </small>
                                            @endif
                                            @if ($contact['email'])
                                                <small class="d-block">
                                                    <strong>{{ __('Email') }}:</strong> {{ $contact['email'] }}
                                                </small>
                                            @endif
                                            @if ($contact['address_1'])
                                                <small class="d-block">
                                                    <strong>{{ __('Address') }}:</strong> {{ $contact['address_1'] }}
                                                </small>
                                            @endif
                                            @if (!empty($contact['parent_id']))
                                                @php
                                                    $parent = collect($contacts)->firstWhere(
                                                        'id',
                                                        $contact['parent_id'],
                                                    );
                                                @endphp
                                                @if ($parent)
                                                    <small class="d-block">
                                                        <strong>{{ __('Parent Company') }}:</strong>
                                                        {{ $parent['name'] }}
                                                    </small>
                                                @endif
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

@include('inquiries::components.addContactModal')
