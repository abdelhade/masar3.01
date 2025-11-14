<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CRM\Enums\ActivityTypeEnum;

class ActivityRequest extends FormRequest
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
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|in:' . implode(',', ActivityTypeEnum::values()),
            'activity_date' => 'required|date',
            'scheduled_at'  => 'nullable|date_format:H:i',
            'client_id'     => 'nullable|exists:clients,id',
            'assigned_to'   => 'nullable|exists:users,id',
            'branch_id'     => 'nullable|exists:branches,id',
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
            'title'         => __('Activity Title'),
            'description'   => __('Description'),
            'type'          => __('Type'),
            'activity_date' => __('Activity Date'),
            'scheduled_at'  => __('Time'),
            'client_id'     => __('Client'),
            'assigned_to'   => __('Assigned To'),
            'branch_id'     => __('Branch'),
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
            'type.in' => __('The selected type is invalid. It must be a call, message, or meeting.'),
            'scheduled_at.date_format' => __('The time does not match the correct format (HH:MM).'),
        ];
    }
}
