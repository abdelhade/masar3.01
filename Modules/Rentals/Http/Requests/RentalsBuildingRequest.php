<?php

namespace Modules\Rentals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalsBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:500',
            'floors'    => 'nullable|integer|min:1',
            'area'      => 'nullable|numeric|min:0',
            'details'   => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('floors')) {
            $this->merge([
                'floors' => (int) $this->input('floors'),
            ]);
        }
    }

    /**
     * Customize error messages using translation files.
     */
    public function messages(): array
    {
        return [
            'name.required'      => __('The building name field is required.'),
            'name.string'        => __('The building name must be a string.'),
            'name.max'           => __('The building name must not be greater than 255 characters.'),

            'address.string'     => __('The address must be a string.'),
            'address.max'        => __('The address must not be greater than 500 characters.'),

            'floors.required'    => __('The number of floors field is required.'),
            'floors.integer'     => __('The number of floors must be an integer.'),
            'floors.min'         => __('The number of floors must be at least 1.'),

            'area.numeric'       => __('The area must be a number.'),
            'area.min'           => __('The area must not be less than 0.'),

            'details.string'     => __('The details must be a string.'),

            'branch_id.required' => __('The branch field is required.'),
            'branch_id.exists'   => __('The selected branch is invalid.'),
        ];
    }
}
