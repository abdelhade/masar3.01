<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')?->id;

        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:shipping_companies,email,' . $companyId,
            'phone'     => 'required|string|max:20',
            'address'   => 'required|string',
            'base_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الشركة مطلوب.',
            'name.max'      => 'اسم الشركة يجب ألا يتجاوز 255 حرف.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'يجب إدخال بريد إلكتروني صحيح.',
            'email.unique'   => 'البريد الإلكتروني مستخدم من قبل.',

            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.max'      => 'رقم الهاتف يجب ألا يتجاوز 20 رقم.',

            'address.required' => 'عنوان الشركة مطلوب.',

            'base_rate.required' => 'سعر التوصيل الأساسي مطلوب.',
            'base_rate.numeric'  => 'سعر التوصيل يجب أن يكون رقم.',
            'base_rate.min'      => 'سعر التوصيل يجب أن يكون أكبر من أو يساوي 0.',

            'is_active.boolean' => 'حالة الشركة يجب أن تكون صح أو خطأ.',
        ];
    }
}
