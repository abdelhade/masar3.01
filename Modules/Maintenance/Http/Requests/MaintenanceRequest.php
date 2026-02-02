<?php

namespace Modules\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        return [
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'item_name' => 'nullable|string|max:255',
            'item_number' => 'nullable|string|max:50',
            'service_type_id' => 'required|exists:service_types,id',
            'status' => 'required|integer',
            'date' => 'nullable|date',
            'accural_date' => 'nullable|date',
            'branch_id' => $isUpdate ? 'nullable|exists:branches,id' : 'required|exists:branches,id',
            'asset_id' => 'nullable|exists:accounts_assets,id',
            'depreciation_item_id' => 'nullable|exists:depreciation_items,id',
            'spare_parts_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'maintenance_type' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.string' => __('validation.client_name.string'),
            'client_name.max' => __('validation.client_name.max'),

            'client_phone.string' => __('validation.client_phone.string'),
            'client_phone.max' => __('validation.client_phone.max'),

            'item_name.string' => __('validation.item_name.string'),
            'item_name.max' => __('validation.item_name.max'),

            'item_number.string' => __('validation.item_number.string'),
            'item_number.max' => __('validation.item_number.max'),

            'service_type_id.required' => __('validation.service_type_id.required'),
            'service_type_id.exists' => __('validation.service_type_id.exists'),

            'status.required' => __('validation.status.required'),
            'status.integer' => __('validation.status.integer'),

            'branch_id.required' => __('validation.branch_id.required'),
            'branch_id.exists' => __('validation.branch_id.exists'),
        ];
    }
}
