<?php

namespace Modules\Rentals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalsBuildingRequest extends FormRequest
{

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
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'floors' => 'nullable|integer|min:1',
            'area' => 'nullable|numeric|min:0',
            'details' => 'nullable|string',
        ];
    }

    protected function prepareForValidation()
    {
        // Check if the 'floors' input exists and merge it back as an integer
        if ($this->has('floors')) {
            $this->merge([
                'floors' => (int) $this->input('floors'),
            ]);
        }
    }
    /**
     * Customize error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المبنى مطلوب.',
            'name.max' => 'اسم المبنى يجب ألا يزيد عن 255 حرف.',
            'floors.required' => 'عدد الطوابق مطلوب.',
            'floors.integer' => 'عدد الطوابق يجب أن يكون رقم صحيح.',
            'floors.min' => 'عدد الطوابق يجب أن يكون على الأقل 1.',
            'area.numeric' => 'المساحة يجب أن تكون رقم.',
            'area.min' => 'المساحة لا يمكن أن تكون أقل من 0.',
        ];
    }
}
