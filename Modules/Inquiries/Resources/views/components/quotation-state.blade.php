<!-- Quotation State Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    {{ __('Quotation State') }}
                </h6>
                <small class="d-block mt-1">{{ __('Select quotation state') }}</small>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-1 mb-3">
                        <label class="form-label fw-bold">{{ __('Project Size') }}</label>
                        <select wire:model="projectSize" class="form-select">
                            <option value="">{{ __('Select project size...') }}</option>
                            @foreach ($projectSizeOptions as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('projectSize')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label fw-bold">{{ __('KON Priority') }}</label>
                        <select wire:model="konPriority" class="form-select">
                            <option value="">{{ __('Select KON priority...') }}</option>
                            @foreach ($konPriorityOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                        @error('konPriority')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label fw-bold">{{ __('Client Priority') }}</label>
                        <select wire:model="clientPriority" class="form-select">
                            <option value="">{{ __('Select priority...') }}</option>
                            @foreach ($clientPriorityOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                        @error('clientPriority')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('Pricing Status') }}</label>
                        <select wire:model="pricingStatusId" class="form-select">
                            <option value="">{{ __('Select Status') }}</option>
                            @foreach ($pricingStatuses as $status)
                                <option value="{{ $status->id }}" style="color: {{ $status->color }};">
                                    {{ __($status->name) }}
                                </option>
                            @endforeach
                        </select>

                        @error('quotationState')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- @if (in_array($this->quotationState, [
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
                    @endif --}}
                    {{-- <div class="col-md-3 mb-3 d-flex flex-column">
                        <label class="form-label fw-bold">{{ __('Project') }}</label>
                        <livewire:app::searchable-select :model="Modules\Progress\Models\ProjectProgress::class" label-field="name" wire-model="projectId"
                            placeholder="{{ __('Search for project or add new...') }}" :key="'project-select'"
                            :selected-id="$projectId" />
                    </div> --}}
                    {{-- <!-- المهندس المسؤول (Engineer) -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <label class="form-label fw-bold">{{ __('Assigned Engineer') }}</label>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="flex-grow-1">
                                <livewire:app::searchable-select :model="Modules\Inquiries\Models\Contact::class" label-field="name"
                                    wire-model="selectedContacts.engineer"
                                    placeholder="{{ __('Search for engineer...') }}" :selected-id="$selectedContacts['engineer']"
                                    :key="'engineer-select-' . ($selectedContacts['engineer'] ?? 'new')" />
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
                                                $parent = collect($contacts)->firstWhere('id', $contact['parent_id']);
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
                    </div> --}}

                    {{-- assign engineer date --}}
                    {{-- <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('Assign Engineer Date') }}</label>
                        <input type="date" wire:model="assignEngineerDate" class="form-control">
                        @error('assignEngineerDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
