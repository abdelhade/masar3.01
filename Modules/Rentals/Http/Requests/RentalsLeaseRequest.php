<?php

namespace Modules\Rentals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Rentals\Enums\LeaseStatus;

class RentalsLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // عدل حسب الـ policy لو محتاج
    }

    public function rules(): array
    {
        return [
            'unit_id'        => ['required', 'exists:rentals_units,id'],
            'client_id'      => ['required', 'exists:acc_head,id'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['required', 'date', 'after:start_date'],
            'rent_amount'    => ['required', 'numeric', 'min:0'],
            'acc_id'         => ['required', 'exists:acc_head,id'],
            'status'         => ['required', 'in:' . implode(',', array_column(LeaseStatus::cases(), 'value'))],
            'notes'          => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required'     => 'يجب اختيار الوحدة.',
            'unit_id.exists'       => 'الوحدة المختارة غير موجودة.',
            'client_id.required'   => 'يجب اختيار العميل.',
            'client_id.exists'     => 'العميل المختار غير موجود.',
            'start_date.required'  => 'تاريخ البداية مطلوب.',
            'start_date.date'      => 'تاريخ البداية غير صالح.',
            'end_date.required'    => 'تاريخ النهاية مطلوب.',
            'end_date.date'        => 'تاريخ النهاية غير صالح.',
            'end_date.after'       => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
            'rent_amount.required' => 'قيمة الإيجار مطلوبة.',
            'rent_amount.numeric'  => 'قيمة الإيجار يجب أن تكون رقم.',
            'status.required'      => 'الحالة مطلوبة.',
            'status.in'            => 'الحالة غير صالحة.',
        ];
    }
}
