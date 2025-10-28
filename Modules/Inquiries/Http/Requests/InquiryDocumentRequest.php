<?php

namespace Modules\Inquiries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InquiryDocumentRequest extends FormRequest
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
     * Get the validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Document name is required'),
            'name.string'   => __('Document name must be a string'),
            'name.max'      => __('Document name must not exceed 255 characters'),
        ];
    }
}
