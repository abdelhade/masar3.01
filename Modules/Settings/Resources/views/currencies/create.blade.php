@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.settings')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Add New Currency'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Currency Management'), 'url' => route('currencies.index')],
            ['label' => __('Add New Currency')],
        ],
    ])
    @push('styles')
        <style>
            /* تكبير الـ dropdown */
            .select2-container--bootstrap-5 .select2-dropdown {
                font-size: 1rem;
            }

            /* تحسين عرض النتائج */
            .select2-container--bootstrap-5 .select2-results__option {
                padding: 10px 12px;
            }

            /* Highlight عند الـ hover */
            .select2-container--bootstrap-5 .select2-results__option--highlighted {
                background-color: #0d6efd;
                color: white;
            }

            /* عرض الـ search box */
            .select2-container--bootstrap-5 .select2-search__field {
                padding: 8px 12px;
                font-size: 0.95rem;
            }
        </style>
    @endpush

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('currencies.store') }}" method="POST" id="currency-form">
                        @csrf

                        <div class="row">


                        </div>

                        <div class="row">

                            <!-- Search & Select Currency -->
                            <div class="col-md-3 mb-5">
                                <label class="form-label required">{{ __('Select Currency') }}</label>
                                <select name="code" id="currency-select"
                                    class="form-select @error('code') is-invalid @enderror" required>
                                    <option value="">-- {{ __('Loading currencies...') }} --</option>
                                </select>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Search and select currency from available list') }}</small>
                            </div>

                            <!-- Currency Name (Auto-filled) -->
                            <div class="col-md-3 mb-5">
                                <label class="form-label required">{{ __('Currency Name') }}</label>
                                <input type="text" name="name" id="currency-name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    placeholder="{{ __('Currency Name') }}" readonly required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Currency Symbol (Auto-filled) -->
                            <div class="col-md-3 mb-5">
                                <label class="form-label">{{ __('Currency Symbol') }}</label>
                                <input type="text" name="symbol" id="currency-symbol"
                                    class="form-control @error('symbol') is-invalid @enderror" value="{{ old('symbol') }}"
                                    placeholder="{{ __('Example: $, €, £') }}">
                                @error('symbol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Decimal Places -->
                            <div class="col-md-3 mb-5">
                                <label class="form-label required">{{ __('Decimal Places') }}</label>
                                <select name="decimal_places"
                                    class="form-select @error('decimal_places') is-invalid @enderror" required>
                                    <option value="0" {{ old('decimal_places') == 0 ? 'selected' : '' }}>0</option>
                                    <option value="2" {{ old('decimal_places', 2) == 2 ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('decimal_places') == 3 ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('decimal_places') == 4 ? 'selected' : '' }}>4</option>
                                </select>
                                @error('decimal_places')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Number of digits after decimal (0-4)') }}</small>
                            </div>
                        </div>

                        <div class="row">

                            <!-- Rate Mode -->
                            <div class="col-md-3 mb-5">
                                <label class="form-label required">{{ __('Rate Mode') }}</label>
                                <select name="rate_mode" id="rate_mode"
                                    class="form-select @error('rate_mode') is-invalid @enderror" required>
                                    <option value="automatic"
                                        {{ old('rate_mode', 'automatic') == 'automatic' ? 'selected' : '' }}>
                                        {{ __('Automatic (API)') }}
                                    </option>
                                    <option value="manual" {{ old('rate_mode') == 'manual' ? 'selected' : '' }}>
                                        {{ __('Manual') }}
                                    </option>
                                </select>
                                @error('rate_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Initial Rate -->
                            <div class="col-md-3 mb-5" id="initial_rate_container">
                                <label class="form-label" id="initial_rate_label">{{ __('Initial Rate') }}</label>
                                <input type="number" name="initial_rate" id="initial_rate"
                                    class="form-control @error('initial_rate') is-invalid @enderror"
                                    value="{{ old('initial_rate') }}" placeholder="{{ __('Initial Rate') }}"
                                    step="0.00000001">
                                @error('initial_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small
                                    class="text-muted">{{ __('Initial exchange rate against default currency') }}</small>
                            </div>

                            <!-- Is Default -->
                            <div class="col-md-3 mb-5">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_default" id="is_default"
                                        value="1" {{ old('is_default') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        {{ __('Set as Default Currency') }}
                                    </label>
                                </div>

                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('currencies.index') }}" class="btn btn-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ========== Load Available Currencies ==========
                fetch('/currencies/available')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const select = document.getElementById('currency-select');
                            select.innerHTML = '<option value="">-- {{ __('Select Currency') }} --</option>';

                            data.currencies.forEach(currency => {
                                const option = document.createElement('option');
                                option.value = currency.code;
                                option.textContent = `${currency.code} - ${currency.name}`;
                                option.dataset.name = currency.name;
                                option.dataset.symbol = currency.symbol;
                                select.appendChild(option);
                            });

                            // ✅ تفعيل Select2 بعد تحميل البيانات
                            $('#currency-select').select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: '{{ __('Search by code or name...') }}',
                                allowClear: true,
                                language: {
                                    noResults: function() {
                                        return "{{ __('No currency found') }}";
                                    },
                                    searching: function() {
                                        return "{{ __('Searching...') }}";
                                    }
                                }
                            });

                            // ✅ Event عند الاختيار
                            $('#currency-select').on('select2:select', function(e) {
                                const selectedData = e.params.data;
                                const option = $(this).find('option:selected');

                                document.getElementById('currency-name').value = option.data('name') || '';
                                document.getElementById('currency-symbol').value = option.data('symbol') ||
                                    selectedData.id;
                            });

                        } else {
                            alert('Failed to load currencies: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load currencies from API');
                    });


                // ========== Toggle Initial Rate Field ==========
                const isDefaultCheckbox = document.getElementById('is_default');
                const initialRateContainer = document.getElementById('initial_rate_container');
                const initialRateInput = document.getElementById('initial_rate');
                const initialRateLabel = document.getElementById('initial_rate_label');

                function toggleInitialRate() {
                    if (isDefaultCheckbox.checked) {
                        initialRateContainer.style.display = 'none';
                        initialRateInput.removeAttribute('required');
                    } else {
                        initialRateContainer.style.display = 'block';
                        initialRateInput.setAttribute('required', 'required');
                        initialRateLabel.classList.add('required');
                    }
                }

                toggleInitialRate();
                isDefaultCheckbox.addEventListener('change', toggleInitialRate);
            });
        </script>
    @endpush
@endsection
