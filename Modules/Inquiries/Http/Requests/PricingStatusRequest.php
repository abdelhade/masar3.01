<?php

namespace Modules\Inquiries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PricingStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('pricing_status') ? $this->route('pricing_status')->id : null;

        return [
            'name' => 'required|string|max:255|unique:pricing_statuses,name,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('Name is required'),
            'name.unique' => __('This name already exists'),
            'color.required' => __('Color is required'),
        ];
    }
}
