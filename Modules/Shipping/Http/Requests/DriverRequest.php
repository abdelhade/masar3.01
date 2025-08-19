<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'vehicle_type' => 'required|string|max:100',
            'is_available' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم السائق مطلوب.',
            'name.string'   => 'اسم السائق يجب أن يكون نص.',
            'name.max'      => 'اسم السائق يجب ألا يتجاوز 255 حرف.',

            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.string'   => 'رقم الهاتف يجب أن يكون نص.',
            'phone.max'      => 'رقم الهاتف يجب ألا يتجاوز 20 رقم.',

            'vehicle_type.required' => 'نوع المركبة مطلوب.',
            'vehicle_type.string'   => 'نوع المركبة يجب أن يكون نص.',
            'vehicle_type.max'      => 'نوع المركبة يجب ألا يتجاوز 100 حرف.',

            'is_available.boolean'  => 'حالة السائق يجب أن تكون صح أو خطأ.',
        ];
    }
}
