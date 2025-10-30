<?php

namespace Modules\Inquiries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Project size name is required'),
            'name.string' => __('Project size name must be a string'),
            'name.min' => __('Project size name must be at least 2 characters'),
            'name.max' => __('Project size name must not exceed 255 characters'),

            'description.string' => __('Description must be a string'),
            'description.max' => __('Description must not exceed 1000 characters'),
        ];
    }
}
