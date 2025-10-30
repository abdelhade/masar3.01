<!-- Modal for Adding New Client -->
<div wire:ignore.self class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    {{ __('Add New') }} {{ $modalClientTypeLabel ?? __('Client') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Client Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="newClient.cname"
                            placeholder="{{ __('Enter Name') }}">
                        @error('newClient.cname')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" class="form-control" wire:model="newClient.email"
                            placeholder="example@email.com">
                        @error('newClient.email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Phone 1') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="newClient.phone"
                            placeholder="{{ __('Primary Phone Number') }}">
                        @error('newClient.phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Phone 2') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.phone2"
                            placeholder="{{ __('Secondary Phone Number') }}">
                        @error('newClient.phone2')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Company') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.company"
                            placeholder="{{ __('Company Name') }}">
                        @error('newClient.company')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Job Title') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.job"
                            placeholder="{{ __('Job Title') }}">
                        @error('newClient.job')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Address 1') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.address"
                            placeholder="{{ __('Primary Address') }}">
                        @error('newClient.address')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Address 2') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.address2"
                            placeholder="{{ __('Secondary Address') }}">
                        @error('newClient.address2')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('National ID') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.national_id"
                            placeholder="{{ __('National ID') }}">
                        @error('newClient.national_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Date of Birth') }}</label>
                        <input type="date" class="form-control" wire:model="newClient.date_of_birth">
                        @error('newClient.date_of_birth')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Gender') }} <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.blur="newClient.gender" wire:key="newClient.gender">
                            <option value="">-- {{ __('Select Gender') }} --</option>
                            <option value="male">{{ __('Male') }}</option>
                            <option value="female">{{ __('Female') }}</option>
                        </select>
                        @error('newClient.gender')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Client Category') }}</label>
                        <select class="form-select" wire:model.blur="newClient.client_category_id"
                            wire:key="newClient.client_category_id">
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach ($clientCategories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                        @error('newClient.client_category_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Client Type') }} <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model="newClient.client_type_id" disabled>
                            <option value="">-- {{ __('Select Type') }} --</option>
                            @foreach (\Modules\CRM\Models\ClientType::all() as $type)
                                <option value="{{ $type->id }}"
                                    {{ $type->id == $modalClientType ? 'selected' : '' }}>
                                    {{ $type->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('newClient.client_type_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Contact Person') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.contact_person"
                            placeholder="{{ __('Responsible Person Name') }}">
                        @error('newClient.contact_person')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Contact Phone') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.contact_phone"
                            placeholder="{{ __('Responsible Person Phone') }}">
                        @error('newClient.contact_phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Relationship') }}</label>
                        <input type="text" class="form-control" wire:model="newClient.contact_relation"
                            placeholder="{{ __('Relationship') }}">
                        @error('newClient.contact_relation')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" wire:model="newClient.info" rows="2"
                            placeholder="{{ __('Any Additional Information') }}"></textarea>
                        @error('newClient.info')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="newClient.is_active"
                                id="clientIsActive" checked>
                            <label class="form-check-label" for="clientIsActive">
                                <i class="fas fa-toggle-on me-1"></i> {{ __('Active') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary" wire:click="saveNewClient"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveNewClient">
                        <i class="fas la-save me-1"></i> {{ __('Save') }}
                    </span>
                    <span wire:loading wire:target="saveNewClient">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        {{ __('Saving...') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('openClientModal', (data) => {
                const modal = new bootstrap.Modal(document.getElementById('addClientModal'));
                modal.show();
            });

            Livewire.on('closeClientModal', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addClientModal'));
                if (modal) {
                    modal.hide();
                }
            });
        });
    </script>
@endpush
