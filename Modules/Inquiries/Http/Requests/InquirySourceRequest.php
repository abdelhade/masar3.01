<?php

namespace Modules\Inquiries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquirySourceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:inquiry_sources,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Get the validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Source name is required'),
            'name.string' => __('Source name must be a string'),
            'name.max' => __('Source name must not exceed 255 characters'),
            'parent_id.exists' => __('Parent source does not exist'),
            'is_active.required' => __('Status field is required'),
            'is_active.boolean' => __('Status must be true or false'),
        ];
    }
}
