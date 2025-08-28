<?php

namespace Modules\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'item_name' => 'nullable|string|max:255',
            'item_number' => 'nullable|string|max:50',
            'service_type_id' => 'required|exists:service_types,id',
            'status' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.string' => 'اسم العميل يجب أن يكون نصًا.',
            'client_name.max' => 'اسم العميل لا يجب أن يزيد عن 255 حرفًا.',

            'client_phone.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'client_phone.max' => 'رقم الهاتف لا يجب أن يزيد عن 20 رقمًا.',

            'item_name.string' => 'اسم البند يجب أن يكون نصًا.',
            'item_name.max' => 'اسم البند لا يجب أن يزيد عن 255 حرفًا.',

            'item_number.string' => 'رقم البند يجب أن يكون نصًا.',
            'item_number.max' => 'رقم البند لا يجب أن يزيد عن 50 حرفًا.',

            'service_type_id.required' => 'نوع الصيانة مطلوب.',
            'service_type_id.exists' => 'نوع الصيانة غير موجود في النظام.',

            'status.required' => 'الحالة مطلوبة.',
            'status.integer' => 'الحالة يجب أن تكون رقم صحيح.',
        ];
    }
}
