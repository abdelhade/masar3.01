<?php

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUnitRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $serviceUnitId = $this->route('service_unit') ? $this->route('service_unit')->id : null;

        return [
            'code' => [
                'required',
                'integer',
                'unique:service_units,code,' . $serviceUnitId,
                'min:1',
                'max:9999'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'is_active' => [
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'كود وحدة الخدمة مطلوب',
            'code.integer' => 'كود وحدة الخدمة يجب أن يكون رقماً',
            'code.unique' => 'كود وحدة الخدمة موجود مسبقاً',
            'code.min' => 'كود وحدة الخدمة يجب أن يكون أكبر من صفر',
            'code.max' => 'كود وحدة الخدمة يجب أن يكون أقل من 10000',
            'name.required' => 'اسم وحدة الخدمة مطلوب',
            'name.string' => 'اسم وحدة الخدمة يجب أن يكون نصاً',
            'name.max' => 'اسم وحدة الخدمة يجب أن يكون أقل من 255 حرف',
            'is_active.boolean' => 'حالة النشاط يجب أن تكون صحيحة أو خاطئة',
        ];
    }
    

     /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // تحويل الكود إلى integer إذا كان يحتوي على نقطة عشرية
        if ($this->has('code')) {
            $code = $this->input('code');
            if (is_string($code) && strpos($code, '.') !== false) {
                $this->merge([
                    'code' => (int) floatval($code)
                ]);
            }
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'كود وحدة الخدمة',
            'name' => 'اسم وحدة الخدمة',
            'is_active' => 'حالة النشاط',
        ];
    }
}
