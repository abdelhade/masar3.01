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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        return [
            'name' => 'required|string|max:255|unique:service_types,name,' . $this->id,
            'description' => 'nullable|string',
            'branch_id' => $isUpdate ? 'nullable|exists:branches,id' : 'required|exists:branches,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('service_type.name.required'),
            'name.string' => __('service_type.name.string'),
            'name.max' => __('service_type.name.max'),
            'name.unique' => __('service_type.name.unique'),
            'description.string' => __('service_type.description.string'),
            'branch_id.required' => __('service_type.branch_id.required'),
            'branch_id.exists' => __('service_type.branch_id.exists'),
        ];
    }
}
