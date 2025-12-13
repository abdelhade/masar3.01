<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name' => 'required|string|max:255' . ($isUpdate ? '|unique:vehicle_types,name,' . $this->route('vehicle_type') : '|unique:vehicle_types,name'),
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('fleet::vehicle_type.name.required'),
            'name.unique' => __('fleet::vehicle_type.name.unique'),
            'is_active.boolean' => __('fleet::vehicle_type.is_active.boolean'),
        ];
    }
}
