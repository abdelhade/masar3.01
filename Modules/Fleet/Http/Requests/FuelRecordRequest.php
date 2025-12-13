<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FuelRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'trip_id' => 'nullable|exists:trips,id',
            'branch_id' => 'nullable|exists:branches,id',
            'fuel_date' => 'required|date',
            'fuel_type' => 'required|in:gasoline,diesel,electric',
            'quantity' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'mileage_at_fueling' => 'required|numeric|min:0',
            'station_name' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => __('fleet::fuel_record.vehicle_id.required'),
            'vehicle_id.exists' => __('fleet::fuel_record.vehicle_id.exists'),
            'fuel_date.required' => __('fleet::fuel_record.fuel_date.required'),
            'fuel_type.required' => __('fleet::fuel_record.fuel_type.required'),
            'quantity.required' => __('fleet::fuel_record.quantity.required'),
            'cost.required' => __('fleet::fuel_record.cost.required'),
            'mileage_at_fueling.required' => __('fleet::fuel_record.mileage_at_fueling.required'),
        ];
    }
}
