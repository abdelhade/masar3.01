<?php

namespace Modules\Inquiries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquirySourceRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:inquiry_sources,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المصدر مطلوب',
            'name.string' => 'اسم المصدر يجب أن يكون نص',
            'name.max' => 'اسم المصدر يجب ألا يزيد عن 255 حرف',
            'parent_id.exists' => 'المصدر الأب غير موجود',
            'is_active.required' => 'حقل الحالة مطلوب',
            'is_active.boolean' => 'حقل الحالة يجب أن يكون صحيح أو خطأ',
        ];
    }
}
