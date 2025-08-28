<?php

namespace Modules\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:service_types,name,' . $this->id,
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم نوع الصيانة مطلوب.',
            'name.string' => 'اسم نوع الصيانة يجب أن يكون نصًا.',
            'name.max' => 'اسم نوع الصيانة لا يجب أن يزيد عن 255 حرفًا.',
            'name.unique' => 'اسم نوع الصيانة مسجل بالفعل.',

            'description.string' => 'الوصف يجب أن يكون نصًا.',
        ];
    }
}
