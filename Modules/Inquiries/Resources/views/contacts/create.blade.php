@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Add New Contact'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Contacts'), 'url' => route('contacts.index')],
            ['label' => __('Add New')],
        ],
    ])

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('contacts.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                {{ __('Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone 1 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                {{ __('Phone 1') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="phone_1" class="form-control" value="{{ old('phone_1') }}" required>
                            @error('phone_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone 2 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Phone 2') }}</label>
                            <input type="text" name="phone_2" class="form-control" value="{{ old('phone_2') }}">
                            @error('phone_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                {{ __('Type') }} <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="contactType" class="form-select" required>
                                <option value="person" {{ old('type') === 'person' ? 'selected' : '' }}>
                                    {{ __('Person') }}
                                </option>
                                <option value="company" {{ old('type') === 'company' ? 'selected' : '' }}>
                                    {{ __('Company') }}
                                </option>
                            </select>
                            @error('type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tax Number -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Tax Number') }}</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number') }}">
                            @error('tax_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Address 1 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Address 1') }}</label>
                            <input type="text" name="address_1" class="form-control" value="{{ old('address_1') }}">
                            @error('address_1')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Address 2 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Address 2') }}</label>
                            <input type="text" name="address_2" class="form-control" value="{{ old('address_2') }}">
                            @error('address_2')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Roles -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('Roles') }}</label>
                            <div class="row g-2">
                                @foreach ($roles as $role)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]"
                                                value="{{ $role->id }}" id="role_{{ $role->id }}"
                                                {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ __($role->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Related Contacts -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">
                                <span id="relatedLabel">{{ __('Related Companies/Persons') }}</span>
                            </label>
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                <div class="row g-2" id="relatedContactsContainer">
                                    @if (isset($allContacts) && count($allContacts) > 0)
                                        @foreach ($allContacts as $contact)
                                            <div class="col-md-4 contact-checkbox" data-type="{{ $contact->type }}">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="related_contacts[]" value="{{ $contact->id }}"
                                                        id="contact_{{ $contact->id }}"
                                                        {{ in_array($contact->id, old('related_contacts', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="contact_{{ $contact->id }}">
                                                        @if ($contact->type === 'company')
                                                            <i class="fas fa-building text-primary me-1"></i>
                                                        @else
                                                            <i class="fas fa-user text-success me-1"></i>
                                                        @endif
                                                        {{ $contact->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-12 text-center text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            {{ __('No contacts available') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('Select related contacts') }}
                            </small>
                        </div>

                        <!-- Notes -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>{{ __('Save') }}
                        </button>
                        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('contactType');
                const relatedLabel = document.getElementById('relatedLabel');
                const checkboxes = document.querySelectorAll('.contact-checkbox');

                function updateRelatedContacts() {
                    const selectedType = typeSelect.value;
                    const targetType = selectedType === 'person' ? 'company' : 'person';

                    relatedLabel.textContent = selectedType === 'person' ?
                        '{{ __('Related Companies') }}' :
                        '{{ __('Related Persons') }}';

                    checkboxes.forEach(checkbox => {
                        if (checkbox.dataset.type === targetType) {
                            checkbox.style.display = 'block';
                        } else {
                            checkbox.style.display = 'none';
                            const input = checkbox.querySelector('input[type="checkbox"]');
                            if (input) input.checked = false;
                        }
                    });
                }

                typeSelect.addEventListener('change', updateRelatedContacts);
                updateRelatedContacts();
            });
        </script>
    @endpush
@endsection
