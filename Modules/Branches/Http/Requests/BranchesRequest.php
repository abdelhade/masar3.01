<?php

namespace Modules\Branches\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // هنا ممكن تستخدم صلاحيات مختلفة حسب الدور
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Branch name is required'),
            'name.string' => __('Branch name must be text'),
            'name.max' => __('Branch name must not exceed 255 characters'),
            'code.string' => __('Branch code must be text'),
            'code.max' => __('Branch code must not exceed 50 characters'),
            'address.string' => __('Address must be text'),
            'address.max' => __('Address must not exceed 255 characters'),
            'is_active.required' => __('Status is required'),
            'is_active.boolean' => __('Status must be active or inactive'),
        ];
    }
}
