<?php

namespace Modules\Accounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in controller middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'search' => 'البحث',
            'type' => 'نوع الحساب',
            'per_page' => 'عدد العناصر في الصفحة',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'search.string' => 'يجب أن يكون :attribute نصاً صحيحاً',
            'search.max' => ':attribute طويل جداً (الحد الأقصى 255 حرف)',
            'type.string' => 'يجب أن يكون :attribute نصاً صحيحاً',
            'type.max' => ':attribute طويل جداً (الحد الأقصى 50 حرف)',
            'per_page.integer' => 'يجب أن يكون :attribute رقماً صحيحاً',
            'per_page.min' => 'الحد الأدنى لـ :attribute هو 5',
            'per_page.max' => 'الحد الأقصى لـ :attribute هو 100',
        ];
    }

    /**
     * Get the validated search query
     */
    public function getSearch(): ?string
    {
        return $this->validated('search');
    }

    /**
     * Get the validated type
     */
    public function getType(): ?string
    {
        return $this->validated('type');
    }

    /**
     * Get the validated per_page value with default
     */
    public function getPerPage(): int
    {
        return $this->validated('per_page', 15);
    }
}

