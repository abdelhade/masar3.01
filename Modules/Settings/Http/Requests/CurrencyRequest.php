<?php

namespace Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
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

            'decimal_places' => [
                'required',
                'integer',
                'min:0',
                'max:4'
            ],

            'is_default' => ['boolean'],
            'is_active' => ['boolean'],

            'rate_mode' => [
                'required',
                Rule::in(['automatic', 'manual'])
            ],

            'initial_rate' => [
                'nullable',
                'numeric',
                'min:0.00000001',
                'max:9999999999',
                Rule::requiredIf(function () use ($isUpdating) {
                    return !$isUpdating && !$this->boolean('is_default');
                })
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم العملة مطلوب',
            'name.max' => 'اسم العملة لا يمكن أن يتجاوز 255 حرف',

            'code.required' => 'كود العملة مطلوب',
            'code.size' => 'كود العملة يجب أن يكون 3 أحرف فقط (مثال: USD, EUR, EGP)',
            'code.unique' => 'كود العملة موجود بالفعل',

            'symbol.max' => 'رمز العملة لا يمكن أن يتجاوز 10 أحرف',

            'decimal_places.required' => 'عدد الأرقام العشرية مطلوب',
            'decimal_places.integer' => 'عدد الأرقام العشرية يجب أن يكون رقماً صحيحاً',
            'decimal_places.min' => 'عدد الأرقام العشرية يجب أن يكون 0 على الأقل',
            'decimal_places.max' => 'عدد الأرقام العشرية لا يمكن أن يتجاوز 4',

            'rate_mode.required' => 'طريقة تحديث السعر مطلوبة',
            'rate_mode.in' => 'طريقة تحديث السعر غير صحيحة',

            'initial_rate.required' => 'سعر الصرف الابتدائي مطلوب للعملات غير الافتراضية',
            'initial_rate.numeric' => 'سعر الصرف يجب أن يكون رقماً',
            'initial_rate.min' => 'سعر الصرف يجب أن يكون أكبر من صفر',
            'initial_rate.max' => 'سعر الصرف كبير جداً',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم العملة',
            'code' => 'كود العملة',
            'symbol' => 'رمز العملة',
            'decimal_places' => 'عدد الأرقام العشرية',
            'is_default' => 'عملة افتراضية',
            'is_active' => 'حالة التفعيل',
            'rate_mode' => 'طريقة التحديث',
            'initial_rate' => 'سعر الصرف',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        // تحويل الكود إلى uppercase تلقائياً
        if ($this->has('code')) {
            $data['code'] = strtoupper($this->code);
        }

        // تحويل الـ checkboxes إلى boolean
        $data['is_default'] = $this->boolean('is_default');
        $data['is_active'] = $this->boolean('is_active', true);

        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $currency = $this->route('currency');

            if ($currency && $currency->is_default) {
                // منع تعطيل العملة الافتراضية
                if (!$this->boolean('is_active')) {
                    $validator->errors()->add(
                        'is_active',
                        'لا يمكن تعطيل العملة الافتراضية'
                    );
                }
            }
        });
    }
}
