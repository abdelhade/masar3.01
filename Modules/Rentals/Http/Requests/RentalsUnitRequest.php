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
            'building_id' => $this->isMethod('post')
                ? 'required|exists:rentals_buildings,id'
                : 'sometimes|exists:rentals_buildings,id',

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
            'building_id.required' => 'يجب اختيار المبنى المرتبط بالوحدة.',
            'building_id.exists'   => 'المبنى المحدد غير موجود.',

            'name.required'        => 'اسم الوحدة مطلوب.',
            'name.string'          => 'اسم الوحدة يجب أن يكون نص.',
            'name.max'             => 'اسم الوحدة يجب ألا يتجاوز 255 حرف.',

            'floor.integer'        => 'رقم الطابق يجب أن يكون عدد صحيح.',

            'area.numeric'         => 'المساحة يجب أن تكون رقم.',
            'area.min'             => 'المساحة يجب ألا تقل عن 0.',
        ];
    }
}
