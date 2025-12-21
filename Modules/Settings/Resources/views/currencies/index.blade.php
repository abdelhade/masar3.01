@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.settings')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Currency Management'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Currency Management')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Currencies')
                <a href="{{ route('currencies.create') }}" type="button" class="btn btn-main font-hold fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="currencies-table" filename="currencies-table"
                            excel-label="Export Excel" pdf-label="Export PDF" print-label="Print" />

                        <table id="currencies-table" class="table table-striped mb-0" style="min-width: 1400px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Currency Name') }}</th>
                                    <th>{{ __('Currency Code') }}</th>
                                    <th>{{ __('Currency Symbol') }}</th>
                                    <th>{{ __('Decimal Places') }}</th>
                                    <th>{{ __('Rate Mode') }}</th>
                                    <th>{{ __('Exchange Rate') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @canany(['edit Currencies', 'delete Currencies'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($currencies as $currency)
                                    <tr class="text-center align-middle">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $currency->name }}
                                            @if ($currency->is_default)
                                                <span class="badge bg-success ms-2">{{ __('Default Currency') }}</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-primary">{{ $currency->code }}</span></td>
                                        <td>{{ $currency->symbol ?? '-' }}</td>
                                        <td>{{ $currency->decimal_places }}</td>
                                        <td>
                                            @if ($currency->is_default)
                                                <span class="text-muted">-</span>
                                            @else
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input rate-mode-switch" type="checkbox"
                                                        role="switch" id="rate_mode_{{ $currency->id }}"
                                                        data-currency-id="{{ $currency->id }}"
                                                        {{ $currency->rate_mode === 'automatic' ? 'checked' : '' }}
                                                        style="cursor: pointer;">
                                                    <label class="form-check-label ms-2"
                                                        for="rate_mode_{{ $currency->id }}" style="cursor: pointer;">
                                                        <span class="mode-text-{{ $currency->id }}">
                                                            {{ $currency->rate_mode === 'automatic' ? __('Automatic (API)') : __('Manual') }}
                                                        </span>
                                                    </label>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($currency->is_default)
                                                <span class="text-muted">-</span>
                                            @else
                                                <div class="rate-container-{{ $currency->id }}">
                                                    @if ($currency->rate_mode === 'automatic')
                                                        <!-- Automatic Mode -->
                                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                                            <span
                                                                class="rate-display-{{ $currency->id }} fw-bold text-success"
                                                                data-decimal-places="{{ $currency->decimal_places }}">
                                                                {{ $currency->latestRate ? number_format($currency->latestRate->rate, $currency->decimal_places) : '-' }}
                                                            </span>

                                                            <button type="button"
                                                                class="btn btn-sm btn-primary fetch-rate-btn"
                                                                data-currency-id="{{ $currency->id }}">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <!-- Manual Mode -->
                                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                                            <input type="number"
                                                                class="form-control form-control-sm manual-rate-input"
                                                                style="max-width: 150px;" step="0.00000001"
                                                                value="{{ $currency->latestRate?->rate ?? '' }}"
                                                                placeholder="{{ __('Enter Rate') }}"
                                                                data-currency-id="{{ $currency->id }}">
                                                            <button type="button"
                                                                class="btn btn-sm btn-success save-manual-rate"
                                                                data-currency-id="{{ $currency->id }}">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if ($currency->latestRate)
                                                        <small class="text-muted d-block mt-1">
                                                            {{ __('Last Update') }}:
                                                            {{ $currency->latestRate->rate_date->format('Y-m-d') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($currency->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        @canany(['edit Currencies', 'delete Currencies'])
                                            <td>
                                                @can('edit Currencies')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('currencies.edit', $currency->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Currencies')
                                                    @unless ($currency->is_default)
                                                        <form action="{{ route('currencies.destroy', $currency->id) }}" method="POST"
                                                            style="display:inline-block;"
                                                            onsubmit="return confirm('{{ __('Are you sure you want to delete?') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                                <i class="las la-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endunless
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No data available') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ========== Switch Rate Mode (Manual/Automatic) ==========
                document.querySelectorAll('.rate-mode-switch').forEach(switchBtn => {
                    switchBtn.addEventListener('change', function() {
                        const currencyId = this.dataset.currencyId;
                        const isAutomatic = this.checked;
                        const mode = isAutomatic ? 'automatic' : 'manual';

                        // Show loading
                        const modeText = document.querySelector(`.mode-text-${currencyId}`);
                        const originalText = modeText.textContent;
                        modeText.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        // Update in database via AJAX
                        fetch(`/currencies/${currencyId}/update-mode`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    rate_mode: mode
                                })
                            })
                            .then(response => {
                                const contentType = response.headers.get('content-type');
                                if (contentType && contentType.includes('text/html')) {
                                    throw new Error('{{ __("Session expired. Please refresh the page.") }}');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // Update label text
                                    modeText.textContent = isAutomatic ?
                                        '{{ __('Automatic (API)') }}' : '{{ __('Manual') }}';

                                    // Reload page to update rate input UI
                                    setTimeout(() => location.reload(), 500);
                                } else {
                                    throw new Error(data.message);
                                }
                            })
                            .catch(error => {
                                modeText.textContent = originalText;
                                this.checked = !this.checked; // Revert switch

                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('Error') }}',
                                    text: error.message ||
                                        '{{ __('Failed to update mode') }}'
                                });
                            });
                    });
                });

                // ========== Fetch Live Rate (Automatic) ==========
                // ========== Fetch Live Rate (Automatic) ==========
                document.querySelectorAll('.fetch-rate-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const currencyId = this.dataset.currencyId;
                        const btnElement = this;
                        const originalHtml = btnElement.innerHTML;

                        btnElement.disabled = true;
                        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        fetch(`/currencies/${currencyId}/fetch-live-rate`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                }
                            })
                            .then(response => {
                                // Check if response is HTML (authentication error)
                                const contentType = response.headers.get('content-type');
                                if (contentType && contentType.includes('text/html')) {
                                    throw new Error('{{ __("Session expired. Please refresh the page and login again.") }}');
                                }
                                if (!response.ok) {
                                    return response.json().then(err => {
                                        throw new Error(err.message || '{{ __("Request failed") }}');
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // ✅ استخدم decimal_places من الـ data-attribute
                                    const rateDisplay = document.querySelector(
                                        `.rate-display-${currencyId}`);
                                    const decimalPlaces = parseInt(rateDisplay.dataset
                                        .decimalPlaces) || 2;

                                    // ✅ فورمات السعر حسب decimal_places
                                    const formattedRate = parseFloat(data.rate_raw).toLocaleString(
                                        'en-US', {
                                            minimumFractionDigits: decimalPlaces,
                                            maximumFractionDigits: decimalPlaces
                                        });

                                    rateDisplay.textContent = formattedRate;

                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ __('Success') }}',
                                        text: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    throw new Error(data.message);
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('Error') }}',
                                    text: error.message ||
                                        '{{ __('Failed to fetch rate from API') }}'
                                });
                            })
                            .finally(() => {
                                btnElement.disabled = false;
                                btnElement.innerHTML = originalHtml;
                            });
                    });
                });

                // ========== Save Manual Rate ==========
                document.querySelectorAll('.save-manual-rate').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const currencyId = this.dataset.currencyId;
                        const input = document.querySelector(
                            `.manual-rate-input[data-currency-id="${currencyId}"]`);
                        const rate = input.value;

                        if (!rate || rate <= 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: '{{ __('Warning') }}',
                                text: '{{ __('Please enter a valid rate') }}'
                            });
                            return;
                        }

                        const btnElement = this;
                        const originalHtml = btnElement.innerHTML;

                        btnElement.disabled = true;
                        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        fetch(`/currencies/${currencyId}/update-rate`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    rate: rate
                                })
                            })
                            .then(response => {
                                const contentType = response.headers.get('content-type');
                                if (contentType && contentType.includes('text/html')) {
                                    throw new Error('{{ __("Session expired. Please refresh the page.") }}');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{ __('Success') }}',
                                        text: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });

                                    // ✅ استخدم السعر المنسق من السيرفر
                                    input.value = data.rate_raw; // الخام للـ input
                                } else {
                                    throw new Error(data.message);
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('Error') }}',
                                    text: error.message ||
                                        '{{ __('Failed to save rate') }}'
                                });
                            })
                            .finally(() => {
                                btnElement.disabled = false;
                                btnElement.innerHTML = originalHtml;
                            });
                    });
                });

            });
        </script>
    @endpush
@endsection
