<?php

namespace Modules\Invoices\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // خليه true عشان يسمح لأي مستخدم مصرح له بالدخول
        return true;
    }

    public function rules()
    {
        // لو في تعديل → اجنور الـ ID الحالي
        $templateId = $this->route('template')?->id ?? $this->route('invoice_template')?->id;

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                // اجنور الـ ID الحالي في التعديل
                \Illuminate\Validation\Rule::unique('invoice_templates', 'code')->ignore($templateId),
            ],
            'description' => 'nullable|string',
            'visible_columns' => 'required|array',
            'visible_columns.*' => 'string',
            'invoice_types' => 'required|array',
            'invoice_types.*' => 'integer|in:10,11,12,13,14,15,16,17,18,19,20,21,22,24,25',
            'is_default' => 'nullable|array',
            'is_default.*' => 'integer',
            'sort_order' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم النموذج مطلوب.',
            'code.required' => 'كود النموذج مطلوب.',
            'code.unique' => 'كود النموذج مستخدم من قبل.',
            'visible_columns.required' => 'يجب اختيار الأعمدة الظاهرة.',
            'invoice_types.required' => 'يجب تحديد أنواع الفواتير المرتبطة.',
        ];
    }
}
