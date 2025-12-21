@props([
    'fromCurrency' => '',
    'toCurrency' => '',
    'amount' => 0,
    'sourceField' => null,
    'targetField' => null,
    'showAmount' => true,
    'showResult' => true,
    'showRate' => true,
    'editableRate' => true,
    'inline' => false,
    'size' => 'default', // default, sm, lg
])

<div x-data="currencyConverter({
    fromCurrency: '{{ $fromCurrency }}',
    toCurrency: '{{ $toCurrency }}',
    amount: {{ $amount }},
    sourceField: '{{ $sourceField }}',
    targetField: '{{ $targetField }}',
    editableRate: {{ $editableRate ? 'true' : 'false' }}
})" {{ $attributes->merge(['class' => 'currency-converter']) }}>
    @if ($inline)
        {{-- Inline Mode --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if ($showAmount)
                <div class="flex-grow-1" style="min-width: 120px;">
                    <input type="number" x-model.number="amount" @input="convert()"
                        class="form-control form-control-{{ $size }}" placeholder="{{ __('Amount') }}"
                        step="0.01" min="0">
                </div>
            @endif

            <div style="min-width: 100px;">
                <select x-model="fromCurrency" @change="convert()" class="form-select form-select-{{ $size }}">
                    <option value="">{{ __('From') }}</option>
                    <template x-for="currency in currencies" :key="currency.code">
                        <option :value="currency.code" x-text="currency.code"></option>
                    </template>
                </select>
            </div>

            <button type="button" @click="swapCurrencies()" class="btn btn-light btn-{{ $size }} btn-icon"
                title="{{ __('Swap') }}">
                <i class="ki-outline ki-arrow-left-right fs-2"></i>
            </button>

            <div style="min-width: 100px;">
                <select x-model="toCurrency" @change="convert()" class="form-select form-select-{{ $size }}">
                    <option value="">{{ __('To') }}</option>
                    <template x-for="currency in currencies" :key="currency.code">
                        <option :value="currency.code" x-text="currency.code"></option>
                    </template>
                </select>
            </div>

            @if ($showRate)
                {{-- Editable Rate --}}
                <div style="min-width: 100px;">
                    <div class="input-group input-group-{{ $size }}">
                        <span class="input-group-text" title="{{ __('Rate') }}">
                            <i class="fas fa-percentage"></i>
                        </span>
                        @if ($editableRate)
                            <input type="number" x-model.number="customRate" @input="convertWithCustomRate()"
                                class="form-control form-control-{{ $size }}" step="0.0001" min="0"
                                title="{{ __('Exchange Rate') }}">
                        @else
                            <input type="text" :value="conversionRate"
                                class="form-control form-control-{{ $size }}" readonly
                                title="{{ __('Exchange Rate') }}">
                        @endif
                    </div>
                </div>
            @endif

            @if ($showResult)
                <div class="flex-grow-1" style="min-width: 120px;">
                    <input type="text" :value="formatAmount(convertedAmount, toCurrency)"
                        class="form-control form-control-{{ $size }}" readonly>
                </div>
            @endif

            <div x-show="loading" class="spinner-border spinner-border-sm text-primary"></div>
        </div>
    @else
        {{-- Card Mode --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="ki-outline ki-arrows-loop fs-2 me-2"></i>
                    {{ __('Currency Converter') }}
                </h5>

                {{-- From Currency --}}
                <div class="mb-4">
                    <label class="form-label required">{{ __('From Currency') }}</label>
                    <div class="input-group">
                        <select x-model="fromCurrency" @change="convert()" class="form-select">
                            <option value="">{{ __('Select Currency') }}</option>
                            <template x-for="currency in currencies" :key="currency.code">
                                <option :value="currency.code" x-text="`${currency.code} - ${currency.name}`"></option>
                            </template>
                        </select>
                        <span class="input-group-text" x-text="getCurrencySymbol(fromCurrency)"></span>
                    </div>
                </div>

                @if ($showAmount)
                    {{-- Amount --}}
                    <div class="mb-4">
                        <label class="form-label required">{{ __('Amount') }}</label>
                        <input type="number" x-model.number="amount" @input="convert()"
                            class="form-control form-control-lg" placeholder="0.00" step="0.01" min="0">
                    </div>
                @endif

                {{-- Swap Button --}}
                <div class="text-center mb-4">
                    <button type="button" @click="swapCurrencies()" class="btn btn-light-primary btn-sm">
                        <i class="ki-outline ki-arrows-loop fs-3"></i>
                        {{ __('Swap') }}
                    </button>
                </div>

                {{-- To Currency --}}
                <div class="mb-4">
                    <label class="form-label required">{{ __('To Currency') }}</label>
                    <div class="input-group">
                        <select x-model="toCurrency" @change="convert()" class="form-select">
                            <option value="">{{ __('Select Currency') }}</option>
                            <template x-for="currency in currencies" :key="currency.code">
                                <option :value="currency.code" x-text="`${currency.code} - ${currency.name}`"></option>
                            </template>
                        </select>
                        <span class="input-group-text" x-text="getCurrencySymbol(toCurrency)"></span>
                    </div>
                </div>

                @if ($showRate)
                    {{-- Exchange Rate (Editable) --}}
                    <div class="mb-4">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>{{ __('Exchange Rate') }}</span>
                            <button type="button" @click="fetchRate()" class="btn btn-sm btn-light-primary"
                                :disabled="loading || !fromCurrency || !toCurrency"
                                title="{{ __('Fetch rate from API') }}">
                                <i class="fas fa-sync-alt" :class="loading && 'fa-spin'"></i>
                            </button>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">1 <span x-text="fromCurrency || '?'" class="ms-1"></span>
                                =</span>
                            @if ($editableRate)
                                <input type="number" x-model.number="customRate" @input="convertWithCustomRate()"
                                    class="form-control" step="0.0001" min="0"
                                    placeholder="{{ __('Enter rate') }}">
                            @else
                                <input type="text" :value="conversionRate" class="form-control" readonly>
                            @endif
                            <span class="input-group-text" x-text="toCurrency || '?'"></span>
                        </div>
                        <small class="text-muted" x-show="lastRateUpdate">
                            {{ __('Last update') }}: <span x-text="lastRateUpdate"></span>
                        </small>
                    </div>
                @endif

                @if ($showResult)
                    {{-- Result --}}
                    <div class="alert alert-primary d-flex align-items-center" x-show="convertedAmount > 0">
                        <i class="ki-outline ki-information fs-2 me-3"></i>
                        <div>
                            <div class="fw-bold fs-3">
                                <span x-text="formatAmount(convertedAmount, toCurrency)"></span>
                                <span x-text="getCurrencySymbol(toCurrency)"></span>
                            </div>
                            <div class="text-muted fs-7">
                                {{ __('Rate') }}:
                                <span
                                    x-text="`1 ${fromCurrency} = ${customRate || conversionRate} ${toCurrency}`"></span>
                                <template x-if="customRate && customRate !== conversionRate">
                                    <span class="badge bg-warning ms-2">{{ __('Custom Rate') }}</span>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Loading --}}
                <div x-show="loading" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                </div>

                {{-- Error --}}
                <div x-show="error" class="alert alert-danger" x-text="error"></div>
            </div>
        </div>
    @endif
</div>
