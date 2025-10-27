<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'branch_id' => 'nullable|exists:branches,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'حقل العنوان مطلوب.',
            'title.string'   => 'يجب أن يكون العنوان نصاً.',
            'title.max'      => 'لا يمكن أن يزيد العنوان عن 255 حرفاً.',
            'branch_id.exists' => 'الفرع المختار غير صحيح.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
