<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $vehicleId = $this->route('vehicle');

        return [
            'plate_number' => [
                'required',
                'string',
                'max:255',
                $isUpdate ? Rule::unique('vehicles', 'plate_number')->ignore($vehicleId) : 'unique:vehicles,plate_number',
            ],
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:255',
            'chassis_number' => 'nullable|string|max:255',
            'engine_number' => 'nullable|string|max:255',
            'current_mileage' => 'required|numeric|min:0',
            'status' => 'required|in:available,in_use,maintenance,out_of_service',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'plate_number.required' => __('fleet::vehicle.plate_number.required'),
            'plate_number.unique' => __('fleet::vehicle.plate_number.unique'),
            'vehicle_type_id.required' => __('fleet::vehicle.vehicle_type_id.required'),
            'vehicle_type_id.exists' => __('fleet::vehicle.vehicle_type_id.exists'),
            'name.required' => __('fleet::vehicle.name.required'),
            'current_mileage.required' => __('fleet::vehicle.current_mileage.required'),
            'status.required' => __('fleet::vehicle.status.required'),
        ];
    }
}
