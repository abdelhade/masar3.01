<div>
    <div wire:ignore.self class="modal fade" id="contactModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>
                        {{ __('Add New Contact') }}
                        @if ($modalContactTypeLabel)
                            - {{ __($modalContactTypeLabel) }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form wire:submit.prevent="saveNewContact">
                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-signature me-1"></i>{{ __('Name') }} <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="newContact.name" class="form-control"
                                    placeholder="{{ __('Enter contact name') }}">
                                @error('newContact.name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-envelope me-1"></i>{{ __('Email') }}
                                </label>
                                <input type="email" wire:model="newContact.email" class="form-control"
                                    placeholder="{{ __('Enter email address') }}">
                                @error('newContact.email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Phone 1 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-phone me-1"></i>{{ __('Phone 1') }} <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="newContact.phone_1" class="form-control"
                                    placeholder="{{ __('Enter primary phone') }}">
                                @error('newContact.phone_1')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Phone 2 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-phone me-1"></i>{{ __('Phone 2') }}
                                </label>
                                <input type="text" wire:model="newContact.phone_2" class="form-control"
                                    placeholder="{{ __('Enter secondary phone') }}">
                                @error('newContact.phone_2')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-user-tag me-1"></i>{{ __('Type') }} <span
                                        class="text-danger">*</span>
                                </label>
                                <select wire:model.live="newContact.type" class="form-select">
                                    <option value="person">{{ __('Person') }}</option>
                                    <option value="company">{{ __('Company') }}</option>
                                </select>
                                @error('newContact.type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Tax Number -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-hashtag me-1"></i>{{ __('Tax Number') }}
                                </label>
                                <input type="text" wire:model="newContact.tax_number" class="form-control"
                                    placeholder="{{ __('Enter tax number') }}">
                                @error('newContact.tax_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Address 1 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ __('Address 1') }}
                                </label>
                                <input type="text" wire:model="newContact.address_1" class="form-control"
                                    placeholder="{{ __('Enter address') }}">
                                @error('newContact.address_1')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Address 2 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ __('Address 2') }}
                                </label>
                                <input type="text" wire:model="newContact.address_2" class="form-control"
                                    placeholder="{{ __('Enter additional address') }}">
                                @error('newContact.address_2')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Roles -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-user-shield me-1"></i>{{ __('Roles') }} <span
                                        class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    @foreach ($inquirieRoles as $role)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model="selectedRoles" value="{{ $role['id'] }}"
                                                    id="role_{{ $role['id'] }}">
                                                <label class="form-check-label" for="role_{{ $role['id'] }}">
                                                    {{ __($role['name']) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedRoles')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Related Contacts (Checkboxes) -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">
                                    @if ($newContact['type'] === 'person')
                                        <i class="fas fa-building me-1"></i>{{ __('Related Companies') }}
                                    @else
                                        <i class="fas fa-user me-1"></i>{{ __('Related Persons') }}
                                    @endif
                                </label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <div class="row g-2">
                                        @if ($newContact['type'] === 'person')
                                            @php
                                                $filteredContacts = array_filter($contacts, function ($c) {
                                                    return $c['type'] === 'company';
                                                });
                                            @endphp
                                            @if (count($filteredContacts) > 0)
                                                @foreach ($filteredContacts as $contact)
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:model="newContact.relatedContacts"
                                                                value="{{ $contact['id'] }}"
                                                                id="company_{{ $contact['id'] }}">
                                                            <label class="form-check-label"
                                                                for="company_{{ $contact['id'] }}">
                                                                <i class="fas fa-building me-1 text-primary"></i>
                                                                {{ $contact['name'] }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="col-12 text-center text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    {{ __('No companies available') }}
                                                </div>
                                            @endif
                                        @else
                                            @php
                                                $filteredContacts = array_filter($contacts, function ($c) {
                                                    return $c['type'] === 'person';
                                                });
                                            @endphp
                                            @if (count($filteredContacts) > 0)
                                                @foreach ($filteredContacts as $contact)
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:model="newContact.relatedContacts"
                                                                value="{{ $contact['id'] }}"
                                                                id="person_{{ $contact['id'] }}">
                                                            <label class="form-check-label"
                                                                for="person_{{ $contact['id'] }}">
                                                                <i class="fas fa-user me-1 text-success"></i>
                                                                {{ $contact['name'] }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="col-12 text-center text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    {{ __('No persons available') }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('Select related contacts for this person/company') }}
                                </small>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-sticky-note me-1"></i>{{ __('Notes') }}
                                </label>
                                <textarea wire:model="newContact.notes" class="form-control" rows="3"
                                    placeholder="{{ __('Enter any additional notes') }}"></textarea>
                                @error('newContact.notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="saveNewContact" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>{{ __('Save Contact') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.addEventListener('openContactModal', event => {
            $('#contactModal').modal('show');
        });

        window.addEventListener('closeContactModal', event => {
            $('#contactModal').modal('hide');
        });
    </script>
@endpush
