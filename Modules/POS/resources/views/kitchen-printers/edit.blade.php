@extends('pos::layouts.master')

@section('content')
<div class="kitchen-printer-form-container">
    <div class="page-header">
        <a href="{{ route('kitchen-printers.index') }}" class="back-link">
            <i class="las la-arrow-right"></i>
            <span>{{ __('pos.back_to_list') }}</span>
        </a>
        <div class="page-title">
            <h1>{{ __('pos.edit_printer_station') }}</h1>
            <p class="page-subtitle">{{ __('pos.edit_printer_station_desc') }}</p>
        </div>
    </div>

    <div class="form-card">
        <form action="{{ route('kitchen-printers.update', $kitchenPrinter) }}" method="POST" x-data="printerForm()">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h3 class="section-title">{{ __('pos.basic_information') }}</h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-label required">
                                {{ __('pos.station_name') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $kitchenPrinter->name) }}" 
                                   placeholder="{{ __('pos.station_name_placeholder') }}"
                                   required
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">{{ __('pos.station_name_help') }}</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="printer_name" class="form-label required">
                                {{ __('pos.printer_name') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('printer_name') is-invalid @enderror" 
                                   id="printer_name" 
                                   name="printer_name" 
                                   value="{{ old('printer_name', $kitchenPrinter->printer_name) }}" 
                                   placeholder="{{ __('pos.printer_name_placeholder') }}"
                                   required>
                            @error('printer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">{{ __('pos.printer_name_help') }}</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sort_order" class="form-label">
                                {{ __('pos.sort_order') }}
                            </label>
                            <input type="number" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', $kitchenPrinter->sort_order) }}" 
                                   min="0"
                                   step="1">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">{{ __('pos.sort_order_help') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">{{ __('pos.settings') }}</h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $kitchenPrinter->is_active) ? 'checked' : '' }}
                                       x-model="isActive">
                                <label class="custom-control-label" for="is_active">
                                    <span class="switch-label">{{ __('pos.active_station') }}</span>
                                    <small class="d-block text-muted">{{ __('pos.active_station_help') }}</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_default" value="0">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_default" 
                                       name="is_default" 
                                       value="1"
                                       {{ old('is_default', $kitchenPrinter->is_default) ? 'checked' : '' }}
                                       x-model="isDefault">
                                <label class="custom-control-label" for="is_default">
                                    <span class="switch-label">{{ __('pos.default_station') }}</span>
                                    <small class="d-block text-muted">{{ __('pos.default_station_help') }}</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning for default station -->
                <div x-show="isDefault" x-transition class="alert alert-info">
                    <i class="las la-info-circle"></i>
                    {{ __('pos.default_station_warning') }}
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('kitchen-printers.index') }}" class="btn btn-secondary">
                    <i class="las la-times"></i>
                    {{ __('pos.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="las la-save"></i>
                    {{ __('pos.update') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .kitchen-printer-form-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem;
        background: #ffffff;
        min-height: 100vh;
    }

    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--mint-green-200);
    }

    .back-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: var(--mint-green-500);
    }

    .back-link i {
        font-size: 1.25rem;
    }

    .page-title {
        flex: 1;
    }

    .page-title h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.25rem 0;
    }

    .page-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .form-card {
        background: white;
        border: 2px solid var(--mint-green-200);
        border-radius: 12px;
        padding: 2rem;
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--mint-green-100);
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--mint-green-500);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 1.5rem;
        background: var(--mint-green-500);
        border-radius: 2px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.9375rem;
    }

    .form-label.required::after {
        content: ' *';
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-family: 'Cairo', sans-serif;
        border: 2px solid var(--mint-green-200);
        border-radius: 8px;
        transition: all 0.2s;
        background: white;
        color: #111827;
    }

    .form-control:focus {
        border-color: var(--mint-green-400);
        box-shadow: 0 0 0 3px rgba(42, 184, 141, 0.1);
        outline: none;
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.375rem;
    }

    .form-text {
        display: block;
        color: #6b7280;
        font-size: 0.8125rem;
        margin-top: 0.375rem;
    }

    .custom-control {
        position: relative;
        display: block;
        padding-right: 3.5rem;
        min-height: 1.5rem;
    }

    .custom-control-input {
        position: absolute;
        right: 0;
        z-index: -1;
        width: 3rem;
        height: 1.5rem;
        opacity: 0;
    }

    .custom-control-label {
        position: relative;
        margin-bottom: 0;
        vertical-align: top;
        cursor: pointer;
    }

    .custom-control-label::before {
        position: absolute;
        top: 0;
        right: -3.5rem;
        display: block;
        width: 3rem;
        height: 1.5rem;
        pointer-events: none;
        content: "";
        background-color: #d1d5db;
        border-radius: 0.75rem;
        transition: all 0.2s;
    }

    .custom-control-label::after {
        position: absolute;
        top: 0.125rem;
        right: -3.25rem;
        display: block;
        width: 1.25rem;
        height: 1.25rem;
        content: "";
        background-color: white;
        border-radius: 50%;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--mint-green-500);
    }

    .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(-1.5rem);
    }

    .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 3px rgba(42, 184, 141, 0.1);
    }

    .switch-label {
        font-weight: 500;
        color: #374151;
        font-size: 0.9375rem;
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 8px;
        margin-top: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .alert i {
        font-size: 1.25rem;
        margin-top: 0.125rem;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 2rem;
        border-top: 1px solid var(--mint-green-100);
    }

    .btn {
        font-family: 'Cairo', sans-serif;
        font-weight: 500;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        font-size: 1rem;
    }

    .btn i {
        font-size: 1.125rem;
    }

    .btn-primary {
        background: var(--mint-green-500);
        color: white;
    }

    .btn-primary:hover {
        background: var(--mint-green-400);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(42, 184, 141, 0.3);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    /* Dark Mode */
    body.dark-mode .kitchen-printer-form-container {
        background: #111827;
    }

    body.dark-mode .page-header {
        border-bottom-color: #374151;
    }

    body.dark-mode .back-link {
        color: #9ca3af;
    }

    body.dark-mode .back-link:hover {
        color: #d1d5db;
    }

    body.dark-mode .page-title h1 {
        color: #f9fafb;
    }

    body.dark-mode .page-subtitle {
        color: #9ca3af;
    }

    body.dark-mode .form-card {
        background: #1f2937;
        border-color: #374151;
    }

    body.dark-mode .form-section {
        border-bottom-color: #374151;
    }

    body.dark-mode .section-title {
        color: #d1d5db;
    }

    body.dark-mode .section-title::before {
        background: #d1d5db;
    }

    body.dark-mode .form-label {
        color: #d1d5db;
    }

    body.dark-mode .form-control {
        background: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    body.dark-mode .form-control:focus {
        border-color: #6b7280;
        background: #374151;
    }

    body.dark-mode .form-text {
        color: #9ca3af;
    }

    body.dark-mode .switch-label {
        color: #d1d5db;
    }

    body.dark-mode .custom-control-label::before {
        background-color: #4b5563;
    }

    body.dark-mode .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #6b7280;
    }

    body.dark-mode .alert-info {
        background: #1e3a8a;
        color: #93c5fd;
        border-color: #1e40af;
    }

    body.dark-mode .form-actions {
        border-top-color: #374151;
    }

    @media (max-width: 768px) {
        .kitchen-printer-form-container {
            padding: 1rem;
        }

        .form-card {
            padding: 1.5rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function printerForm() {
        return {
            isActive: {{ old('is_active', $kitchenPrinter->is_active) ? 'true' : 'false' }},
            isDefault: {{ old('is_default', $kitchenPrinter->is_default) ? 'true' : 'false' }}
        }
    }
</script>
@endpush
