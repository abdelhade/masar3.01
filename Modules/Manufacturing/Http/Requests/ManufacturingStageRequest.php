<?php

namespace Modules\Manufacturing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManufacturingStageRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'estimated_duration' => 'nullable|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'branch_id' => 'nullable|exists:branches,id',
        ];
        $this->merge([
            'order' => (int) $this->input('order'),
        ]);

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المرحلة',
            'description' => 'الوصف',
            'order' => 'الترتيب',
            'estimated_duration' => 'المدة التقديرية',
            'cost' => 'التكلفة',
            'is_active' => 'حالة التفعيل',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المرحلة مطلوب',
            'name.max' => 'اسم المرحلة لا يجب أن يتجاوز 255 حرف',
            'order.required' => 'ترتيب المرحلة مطلوب',
            'order.integer' => 'ترتيب المرحلة يجب أن يكون رقم صحيح',
            'order.min' => 'ترتيب المرحلة يجب أن يكون أكبر من أو يساوي 0',
            'estimated_duration.numeric' => 'المدة التقديرية يجب أن تكون رقم',
            'estimated_duration.min' => 'المدة التقديرية يجب أن تكون أكبر من أو تساوي 0',
            'cost.required' => 'تكلفة المرحلة مطلوبة',
            'cost.numeric' => 'التكلفة يجب أن تكون رقم',
            'cost.min' => 'التكلفة يجب أن تكون أكبر من أو تساوي 0',
        ];
    }
}
