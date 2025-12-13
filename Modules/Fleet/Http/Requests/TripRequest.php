<?php

declare(strict_types=1);

namespace Modules\Fleet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => $isUpdate ? 'nullable|date|after_or_equal:start_date' : 'nullable|date',
            'start_mileage' => 'required|numeric|min:0',
            'end_mileage' => $isUpdate ? 'nullable|numeric|min:0|gte:start_mileage' : 'nullable|numeric|min:0',
            'purpose' => 'nullable|string|max:255',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => __('fleet::trip.vehicle_id.required'),
            'vehicle_id.exists' => __('fleet::trip.vehicle_id.exists'),
            'driver_id.required' => __('fleet::trip.driver_id.required'),
            'driver_id.exists' => __('fleet::trip.driver_id.exists'),
            'start_location.required' => __('fleet::trip.start_location.required'),
            'end_location.required' => __('fleet::trip.end_location.required'),
            'start_date.required' => __('fleet::trip.start_date.required'),
            'start_mileage.required' => __('fleet::trip.start_mileage.required'),
            'status.required' => __('fleet::trip.status.required'),
        ];
    }
}
