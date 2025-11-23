<?php

namespace Modules\MyResources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_category_id' => ['required', 'exists:resource_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'resource_category_id.required' => 'التصنيف الرئيسي مطلوب',
            'resource_category_id.exists' => 'التصنيف الرئيسي غير موجود',
            'name.required' => 'الاسم بالإنجليزية مطلوب',
            'name_ar.required' => 'الاسم بالعربية مطلوب',
        ];
    }
}

