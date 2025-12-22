@props([
    'fromCurrency' => '',
    'toCurrency' => '',
    'amount' => 1,
    'sourceField' => null,
    'targetField' => null,
    'editableRate' => true,
    'class' => '',
    'useInverseRate' => true,
])

<div x-data="currencyConverter({
    fromCurrency: '{{ $fromCurrency }}',
    toCurrency: '{{ $toCurrency }}',
    amount: {{ $amount }},
    sourceField: '{{ $sourceField }}',
    targetField: '{{ $targetField }}',
    editableRate: {{ $editableRate ? 'true' : 'false' }},
    useInverseRate: {{ $useInverseRate ? 'true' : 'false' }}
})" 
x-effect="amount = {{ $amount }}"
{{ $attributes->merge(['class' => 'currency-converter-mini ' . $class]) }}>
    {{-- Hidden/Background Logic for base values --}}
    <input type="hidden" x-model="fromCurrency">
    <input type="hidden" x-model.number="amount">

    <div class="input-group input-group-sm flex-nowrap align-items-center bg-light rounded-pill p-1 border" style="max-width: 350px;">
        {{-- 1. To Currency Select --}}
        <div class="d-flex align-items-center px-2 border-end" title="{{ __('To Currency') }}">
            <select x-model="toCurrency" @change="convert()" 
                class="form-select border-0 bg-transparent fw-bold p-0" style="width: 60px; min-width: 60px;">
                <template x-for="currency in currencies" :key="currency.code">
                    <option :value="currency.code" x-text="currency.code"></option>
                </template>
            </select>
        </div>

        {{-- 2. Exchange Rate Input (Price of 1 Target Currency) --}}
        <div class="d-flex align-items-center px-2 border-end" title="{{ __('Exchange Price') }}">
            {{-- Unified label: 1 [Target] = ? [Base] --}}
            <span class="text-muted fs-10 me-1">1</span>
            <span class="fw-bold fs-9 me-1" x-text="toCurrency"></span>
            <span class="text-muted fs-10 me-1">=</span>
            
            <input type="number" x-model.number="customRate" @input="convertWithCustomRate()"
                x-on:blur="customRate = parseFloat(Number(customRate).toFixed(3))"
                class="form-control border-0 bg-transparent fw-bold p-0 text-center" 
                placeholder="0.000" step="0.001" min="0" style="width: 70px;">
                
            <span class="text-muted fs-10 ms-1" x-text="fromCurrency"></span>
        </div>

        {{-- 3. Result / Converted Amount --}}
        <div class="d-flex align-items-center px-2 flex-grow-1" title="{{ __('Converted Amount') }}">
            <input type="text" :value="formatAmount(convertedAmount, toCurrency)"
                class="form-control border-0 bg-transparent fw-bolder p-0 text-primary text-end" 
                readonly style="width: 80px;">
            
            <div x-show="loading" class="spinner-border spinner-border-sm text-primary ms-1" style="width: 10px; height: 10px;"></div>
        </div>
    </div>
</div>
