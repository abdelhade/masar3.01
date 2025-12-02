<?php

namespace Modules\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodicMaintenanceRequest extends FormRequest
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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        return [
            'item_name' => 'required|string|max:255',
            'item_number' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:255',
            'service_type_id' => 'required|exists:service_types,id',
            'frequency_type' => 'required|in:daily,weekly,monthly,quarterly,semi_annual,annual,custom_days',
            'frequency_value' => 'required_if:frequency_type,custom_days|nullable|integer|min:1',
            'start_date' => 'required|date',
            'notification_days_before' => 'required|integer|min:1|max:365',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'branch_id' => $isUpdate ? 'nullable|exists:branches,id' : 'required|exists:branches,id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'item_name' => __('periodic.attributes.item_name'),
            'item_number' => __('periodic.attributes.item_number'),
            'client_name' => __('periodic.attributes.client_name'),
            'client_phone' => __('periodic.attributes.client_phone'),
            'service_type_id' => __('periodic.attributes.service_type_id'),
            'frequency_type' => __('periodic.attributes.frequency_type'),
            'frequency_value' => __('periodic.attributes.frequency_value'),
            'start_date' => __('periodic.attributes.start_date'),
            'notification_days_before' => __('periodic.attributes.notification_days_before'),
            'is_active' => __('periodic.attributes.is_active'),
            'notes' => __('periodic.attributes.notes'),
            'branch_id' => __('periodic.attributes.branch_id'),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'item_name.required' => __('periodic.item_name.required'),
            'item_number.required' => __('periodic.item_number.required'),
            'client_name.required' => __('periodic.client_name.required'),
            'client_phone.required' => __('periodic.client_phone.required'),
            'service_type_id.required' => __('periodic.service_type_id.required'),
            'service_type_id.exists' => __('periodic.service_type_id.exists'),
            'frequency_type.required' => __('periodic.frequency_type.required'),
            'frequency_type.in' => __('periodic.frequency_type.in'),
            'frequency_value.required_if' => __('periodic.frequency_value.required_if'),
            'frequency_value.integer' => __('periodic.frequency_value.integer'),
            'frequency_value.min' => __('periodic.frequency_value.min'),
            'start_date.required' => __('periodic.start_date.required'),
            'start_date.date' => __('periodic.start_date.date'),
            'notification_days_before.required' => __('periodic.notification_days_before.required'),
            'notification_days_before.integer' => __('periodic.notification_days_before.integer'),
            'notification_days_before.min' => __('periodic.notification_days_before.min'),
            'notification_days_before.max' => __('periodic.notification_days_before.max'),
            'is_active.boolean' => __('periodic.is_active.boolean'),
            'branch_id.exists' => __('periodic.branch_id.exists'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // تحويل القيم لـ integer للتأكد
        if ($this->has('notification_days_before')) {
            $this->merge([
                'notification_days_before' => (int) $this->notification_days_before,
            ]);
        }

        if ($this->has('frequency_value') && $this->frequency_value) {
            $this->merge([
                'frequency_value' => (int) $this->frequency_value,
            ]);
        }

        // تحويل is_active من string إلى boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
