<?php

namespace Modules\Progress\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreIssueRequest
 * 
 * Validation rules for creating a new issue
 */
class StoreIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'status' => ['nullable', Rule::in(['New', 'In Progress', 'Testing', 'Closed'])],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'module' => ['nullable', 'string', 'max:255'],
            'reproduce_steps' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'attachments.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt'], // Max 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => __('general.project_is_required'),
            'project_id.exists' => __('general.project_not_found'),
            'title.required' => __('general.title_is_required'),
            'title.max' => __('general.title_max_length'),
            'priority.required' => __('general.priority_is_required'),
            'priority.in' => __('general.invalid_priority'),
            'status.in' => __('general.invalid_status'),
            'assigned_to.exists' => __('general.user_not_found'),
            'deadline.date' => __('general.invalid_date'),
            'deadline.after_or_equal' => __('general.deadline_must_be_future'),
            'attachments.*.file' => __('general.invalid_file'),
            'attachments.*.max' => __('general.file_too_large'),
            'attachments.*.mimes' => __('general.invalid_file_type'),
        ];
    }
}
