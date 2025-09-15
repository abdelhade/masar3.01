<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pro_id' => 'required|integer',
            'type' => 'required|in:30,31',
            'acc1' => 'required|exists:acc_head,id',
            'acc2' => 'required|exists:acc_head,id',
            'pro_date' => 'required|date',
            'info' => 'nullable|string',
            'pro_value' => 'required|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع الخصم مطلوب.',
            'type.in' => 'نوع الخصم يجب أن يكون إما خصم مسموح به أو خصم مكتسب.',

            'acc1.required' => 'الحساب الأول مطلوب.',
            'acc1.exists' => 'الحساب الأول غير موجود.',

            'acc2.required' => 'الحساب الثاني مطلوب.',
            'acc2.exists' => 'الحساب الثاني غير موجود.',

            'pro_date.required' => 'تاريخ العملية مطلوب.',
            'pro_date.date' => 'صيغة التاريخ غير صحيحة.',

            'info.string' => 'الوصف يجب أن يكون نصاً.',

            'pro_value.required' => 'قيمة الخصم مطلوبة.',
            'pro_value.numeric' => 'قيمة الخصم يجب أن تكون رقماً.',
            'pro_value.min' => 'قيمة الخصم يجب أن تكون على الأقل 0.',

            'branch_id.required' => 'الفرع مطلوب.',
            'branch_id.exists' => 'الفرع المختار غير صحيح.',
        ];
    }
}
