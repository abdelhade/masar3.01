<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Validation\Rules\Enum;
use Modules\CRM\Enums\TaskStatusEnum;
use Modules\CRM\Enums\TaskPriorityEnum;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        return [
            'client_id'      => ['required', 'exists:clients,id'],
            'user_id'        => ['required', 'exists:users,id'],
            'task_type_id'   => ['required', 'exists:task_types,id'],
            'title'          => ['required', 'string', 'max:255'],
            'status'         => ['required', new Enum(TaskStatusEnum::class)],
            'priority'       => ['required', new Enum(TaskPriorityEnum::class)],
            'start_date'     => ['required', 'date'],
            'due_date'  => ['required', 'date', 'after_or_equal:start_date'],
            'client_comment' => ['nullable', 'string'],
            'user_comment'   => ['nullable', 'string'],
            'attachment'     => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
            'branch_id' => $isUpdate ? 'nullable|exists:branches,id' : 'required|exists:branches,id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'client_id'      => __('Client'),
            'user_id'        => __('User'),
            'task_type_id'   => __('Task Type'),
            'title'          => __('Task Title'),
            'status'         => __('Status'),
            'priority'       => __('Priority'),
            'start_date'     => __('Start Date'),
            'delivery_date'  => __('Due Date'),
            'client_comment' => __('Client Comment'),
            'user_comment'   => __('User Comment'),
            'attachment'     => __('Attachment'),
            'branch_id'      => __('Branch'),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delivery_date.after_or_equal' => __('The due date must be a date after or equal to the start date.'),
            'attachment.mimes' => __('Unsupported file format. Allowed formats: jpg, jpeg, png, pdf, doc, docx.'),
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
