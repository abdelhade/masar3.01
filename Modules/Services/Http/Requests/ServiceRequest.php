<?php

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
        $serviceId = $this->route('service') ? $this->route('service')->id : null;

        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $serviceId,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'service_type_id' => 'required|exists:service_categories,id',
            'service_unit_id' => 'required|exists:service_units,id',
            'branch_id' => 'nullable|exists:branches,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الخدمة مطلوب',
            'name.max' => 'اسم الخدمة يجب أن يكون أقل من 255 حرف',
            'code.required' => 'كود الخدمة مطلوب',
            'code.unique' => 'كود الخدمة موجود مسبقاً',
            'code.max' => 'كود الخدمة يجب أن يكون أقل من 50 حرف',
            'price.required' => 'سعر الخدمة مطلوب',
            'price.numeric' => 'سعر الخدمة يجب أن يكون رقماً',
            'price.min' => 'سعر الخدمة يجب أن يكون أكبر من أو يساوي صفر',
            'cost.numeric' => 'تكلفة الخدمة يجب أن تكون رقماً',
            'cost.min' => 'تكلفة الخدمة يجب أن تكون أكبر من أو تساوي صفر',
            'service_type_id.required' => 'تصنيف الخدمة مطلوب',
            'service_type_id.exists' => 'تصنيف الخدمة المحدد غير موجود',
            'service_unit_id.required' => 'وحدة الخدمة مطلوبة',
            'service_unit_id.exists' => 'وحدة الخدمة المحددة غير موجودة',
            'branch_id.exists' => 'الفرع المحدد غير موجود',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الخدمة',
            'code' => 'كود الخدمة',
            'description' => 'وصف الخدمة',
            'price' => 'سعر الخدمة',
            'cost' => 'تكلفة الخدمة',
            'is_active' => 'حالة النشاط',
            'is_taxable' => 'خاضع للضريبة',
            'service_type_id' => 'تصنيف الخدمة',
            'service_unit_id' => 'وحدة الخدمة',
            'branch_id' => 'الفرع',
        ];
    }
}