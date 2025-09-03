<?php

namespace Modules\Progress\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectTypeRequest extends FormRequest
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
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * رسائل الفالياديشن
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم النوع مطلوب',
            'name.string'   => 'الاسم لازم يكون نص',
            'name.max'      => 'الاسم لازم مايزدش عن 255 حرف',
        ];
    }
}
