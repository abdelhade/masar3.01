<?php

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTypeRequest extends FormRequest
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
        $serviceTypeId = $this->route('service_type') ? $this->route('service_type')->id : null;

        return [
            'code' => [
                'required',
                'integer',
                'unique:service_categories,code,' . $serviceTypeId,
                'min:1',
                'max:9999'
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:service_categories,name,' . $serviceTypeId
            ],
            'branch_id' => [
                'nullable',
                'exists:branches,id'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'كود نوع الخدمة مطلوب',
            'code.integer' => 'كود نوع الخدمة يجب أن يكون رقماً',
            'code.unique' => 'كود نوع الخدمة موجود مسبقاً',
            'code.min' => 'كود نوع الخدمة يجب أن يكون أكبر من صفر',
            'code.max' => 'كود نوع الخدمة يجب أن يكون أقل من 10000',
            'name.required' => 'اسم نوع الخدمة مطلوب',
            'name.string' => 'اسم نوع الخدمة يجب أن يكون نصاً',
            'name.max' => 'اسم نوع الخدمة يجب أن يكون أقل من 255 حرف',
            'name.unique' => 'اسم نوع الخدمة موجود مسبقاً',
            'branch_id.exists' => 'الفرع المحدد غير موجود',
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
            'code' => 'كود نوع الخدمة',
            'name' => 'اسم نوع الخدمة',
            'branch_id' => 'الفرع',
        ];
    }
}
