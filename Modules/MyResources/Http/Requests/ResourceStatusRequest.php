<?php

namespace Modules\MyResources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم بالإنجليزية مطلوب',
            'name_ar.required' => 'الاسم بالعربية مطلوب',
            'sort_order.integer' => 'ترتيب العرض يجب أن يكون رقمًا',
            'sort_order.min' => 'ترتيب العرض يجب أن يكون أكبر من أو يساوي 0',
        ];
    }
}

