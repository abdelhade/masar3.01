<!-- Modal لإضافة Contact جديد -->
<div wire:ignore.self class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContactModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    {{ __('Add New') }} {{ $modalContactTypeLabel }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="saveNewContact">
                    <div class="row">
                        <!-- Contact Type (Person/Company) -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">{{ __('Contact Type') }} <span
                                    class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="contactType" id="typePerson"
                                    value="person" wire:model="newContact.type" checked>
                                <label class="btn btn-outline-primary" for="typePerson">
                                    <i class="fas fa-user me-2"></i>{{ __('Person') }}
                                </label>

                                <input type="radio" class="btn-check" name="contactType" id="typeCompany"
                                    value="company" wire:model="newContact.type">
                                <label class="btn btn-outline-primary" for="typeCompany">
                                    <i class="fas fa-building me-2"></i>{{ __('Company') }}
                                </label>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-6 mb-3">
                            <label for="contactName" class="form-label fw-bold">
                                {{ __('Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('newContact.name') is-invalid @enderror"
                                id="contactName" wire:model="newContact.name" placeholder="{{ __('Enter name') }}">
                            @error('newContact.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="contactEmail" class="form-label fw-bold">{{ __('Email') }}</label>
                            <input type="email" class="form-control @error('newContact.email') is-invalid @enderror"
                                id="contactEmail" wire:model="newContact.email" placeholder="{{ __('Enter email') }}">
                            @error('newContact.email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone 1 -->
                        <div class="col-md-6 mb-3">
                            <label for="contactPhone1" class="form-label fw-bold">
                                {{ __('Phone 1') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('newContact.phone_1') is-invalid @enderror"
                                id="contactPhone1" wire:model="newContact.phone_1"
                                placeholder="{{ __('Enter phone number') }}">
                            @error('newContact.phone_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone 2 -->
                        <div class="col-md-6 mb-3">
                            <label for="contactPhone2" class="form-label fw-bold">{{ __('Phone 2') }}</label>
                            <input type="text" class="form-control" id="contactPhone2"
                                wire:model="newContact.phone_2" placeholder="{{ __('Enter alternative phone') }}">
                        </div>

                        <!-- Address 1 -->
                        <div class="col-md-6 mb-3">
                            <label for="contactAddress1" class="form-label fw-bold">{{ __('Address 1') }}</label>
                            <input type="text" class="form-control" id="contactAddress1"
                                wire:model="newContact.address_1" placeholder="{{ __('Enter address') }}">
                        </div>

                        <!-- Address 2 -->
                        <div class="col-md-6 mb-3">
                            <label for="contactAddress2" class="form-label fw-bold">{{ __('Address 2') }}</label>
                            <input type="text" class="form-control" id="contactAddress2"
                                wire:model="newContact.address_2" placeholder="{{ __('Enter alternative address') }}">
                        </div>

                        <!-- Tax Number (for companies) -->
                        @if ($newContact['type'] === 'company')
                            <div class="col-md-6 mb-3">
                                <label for="contactTaxNumber" class="form-label fw-bold">{{ __('Tax Number') }}</label>
                                <input type="text" class="form-control" id="contactTaxNumber"
                                    wire:model="newContact.tax_number" placeholder="{{ __('Enter tax number') }}">
                            </div>
                        @endif

                        <!-- Parent Company (for persons) -->
                        @if ($newContact['type'] === 'person')
                            <div class="col-md-6 mb-3">
                                <label for="contactParent"
                                    class="form-label fw-bold">{{ __('Parent Company') }}</label>
                                <select class="form-select" id="contactParent" wire:model="newContact.parent_id">
                                    <option value="">{{ __('Select parent company (optional)') }}</option>
                                    @foreach ($contacts as $contact)
                                        @if ($contact['type'] === 'company')
                                            <option value="{{ $contact['id'] }}">{{ $contact['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Notes -->
                        <div class="col-md-12 mb-3">
                            <label for="contactNotes" class="form-label fw-bold">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="contactNotes" rows="3" wire:model="newContact.notes"
                                placeholder="{{ __('Enter any additional notes') }}"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary" wire:click="saveNewContact">
                    <i class="fas fa-save me-2"></i>{{ __('Save Contact') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('openContactModal', function() {
                const modal = new bootstrap.Modal(document.getElementById('addContactModal'));
                modal.show();
            });

            Livewire.on('closeContactModal', function() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addContactModal'));
                if (modal) {
                    modal.hide();
                }
            });
        });
    </script>
@endpush
