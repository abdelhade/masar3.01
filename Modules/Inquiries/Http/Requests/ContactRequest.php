<?php

namespace Modules\Inquiries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_1' => ['nullable', 'string', 'max:20'],
            'phone_2' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'in:person,company'],
            'address_1' => ['nullable', 'string', 'max:500'],
            'address_2' => ['nullable', 'string', 'max:500'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'role_id' => ['nullable', 'exists:inquiries_roles,id'],
            'parent_id' => ['nullable', 'exists:contacts,id'],
            'notes' => ['nullable', 'string'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:inquiries_roles,id'],
            'related_contacts' => 'nullable|array', // جديد
            'related_contacts.*' => 'exists:contacts,id', // جديد
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => __('Name'),
            'email' => __('Email'),
            'phone_1' => __('Phone 1'),
            'phone_2' => __('Phone 2'),
            'type' => __('Type'),
            'address_1' => __('Address 1'),
            'address_2' => __('Address 2'),
            'tax_number' => __('Tax Number'),
            'role_id' => __('Role'),
            'parent_id' => __('Parent Contact'),
            'notes' => __('Notes'),
            'roles' => __('Roles'),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
