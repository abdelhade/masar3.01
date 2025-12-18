<?php

namespace Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currencyId = $this->route('currency') ? $this->route('currency')->id : null;
        $isUpdating = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'size:3',
                $isUpdating
                    ? Rule::unique('currencies', 'code')->ignore($currencyId)
                    : 'unique:currencies,code'
            ],
            'symbol' => ['nullable', 'string', 'max:10'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'rate_mode' => ['required', Rule::in(['automatic', 'manual'])],
            'initial_rate' => [
                'nullable',
                'numeric',
                'min:0.00000001',
                'max:9999999999',
                Rule::requiredIf(fn() => !$isUpdating && !$this->boolean('is_default'))
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Currency name is required'),
            'name.max' => __('Currency name cannot exceed 255 characters'),
            'code.required' => __('Currency code is required'),
            'code.size' => __('Currency code must be exactly 3 characters (e.g., USD, EUR, EGP)'),
            'code.unique' => __('This currency code already exists'),
            'symbol.max' => __('Currency symbol cannot exceed 10 characters'),
            'decimal_places.required' => __('Decimal places is required'),
            'decimal_places.integer' => __('Decimal places must be a whole number'),
            'decimal_places.min' => __('Decimal places must be at least 0'),
            'decimal_places.max' => __('Decimal places cannot exceed 4'),
            'rate_mode.required' => __('Rate update mode is required'),
            'rate_mode.in' => __('Invalid rate update mode'),
            'initial_rate.required' => __('Initial exchange rate is required for non-default currencies'),
            'initial_rate.numeric' => __('Exchange rate must be a number'),
            'initial_rate.min' => __('Exchange rate must be greater than zero'),
            'initial_rate.max' => __('Exchange rate is too large'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('currency name'),
            'code' => __('currency code'),
            'symbol' => __('currency symbol'),
            'decimal_places' => __('decimal places'),
            'is_default' => __('default currency'),
            'is_active' => __('activation status'),
            'rate_mode' => __('update mode'),
            'initial_rate' => __('exchange rate'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->has('code') ? strtoupper($this->code) : null,
            'is_default' => $this->boolean('is_default'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $currency = $this->route('currency');

            if ($currency?->is_default && !$this->boolean('is_active')) {
                $validator->errors()->add(
                    'is_active',
                    __('Cannot deactivate the default currency')
                );
            }
        });
    }
}
