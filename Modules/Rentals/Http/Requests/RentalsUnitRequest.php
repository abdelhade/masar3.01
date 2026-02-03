<?php

namespace Modules\Rentals\Http\Requests;

use Illuminate\Validation\Rules\Enum;
use Modules\Rentals\Enums\UnitStatus;
use Illuminate\Foundation\Http\FormRequest;

class RentalsUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_type'   => 'required|string|in:building,item',
            'item_id'     => 'nullable|required_if:unit_type,item|exists:items,id',
            'building_id' => 'nullable|required_if:unit_type,building|exists:rentals_buildings,id',
            'name'        => 'required|string|max:255',
            'floor'       => 'nullable|integer',
            'area'        => 'nullable|numeric|min:0',
            'status'      => ['nullable', new Enum(UnitStatus::class)],
            'details'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'building_id.required' => __('The building field is required.'),
            'building_id.exists'   => __('The selected building is invalid.'),

            'name.required'        => __('The unit name field is required.'),
            'name.string'          => __('The unit name must be a string.'),
            'name.max'             => __('The unit name may not be greater than 255 characters.'),

            'floor.required'       => __('The floor field is required.'),
            'floor.integer'        => __('The floor must be an integer.'),

            'area.numeric'         => __('The area must be a number.'),
            'area.min'             => __('The area must be at least 0.'),

            'status.required'      => __('The status field is required.'),
            'status.enum'          => __('The selected status is invalid.'),

            'details.string'       => __('The details must be a string.'),
        ];
    }
}
