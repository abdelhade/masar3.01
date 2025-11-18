<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'pro_id' => 'required|integer',
            'type' => 'required|in:30,31',
            'pro_date' => 'required|date',
            'info' => 'nullable|string',
            'pro_value' => 'required|numeric|min:0.01',
            'branch_id' => 'nullable|exists:branches,id',
        ];

        if ($this->input('type') == 30) {
            $rules['acc1'] = 'required|exists:acc_head,id';
            $rules['acc2'] = 'nullable|exists:acc_head,id';
        } elseif ($this->input('type') == 31) {
            $rules['acc1'] = 'nullable|exists:acc_head,id';
            $rules['acc2'] = 'required|exists:acc_head,id';
        }

        return $rules;
    }


    public function messages(): array
    {
        return [
            'type.required' => __('The discount type is required.'),
            'type.in' => __('The discount type must be either allowed discount or earned discount.'),

            'acc1.required' => __('The first account is required.'),
            'acc1.exists' => __('The first account does not exist.'),

            'acc2.required' => __('The second account is required.'),
            'acc2.exists' => __('The second account does not exist.'),

            'pro_date.required' => __('The operation date is required.'),
            'pro_date.date' => __('The date format is incorrect.'),

            'info.string' => __('The description must be text.'),

            'pro_value.required' => __('The discount value is required.'),
            'pro_value.numeric' => __('The discount value must be a number.'),
            'pro_value.min' => __('The discount value must be at least 0.01.'),

            'branch_id.exists' => __('The selected branch is incorrect.'),
        ];
    }
}
