<?php

namespace Modules\Checks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \Modules\Checks\Models\Check::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'pro_date' => ['required', 'date'],
            'check_number' => ['required', 'string', 'max:50', 'unique:checks'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['nullable', 'in:pending,cleared,bounced,cancelled'],
            'type' => ['required', 'in:incoming,outgoing'],
            'payee_name' => ['nullable', 'string', 'max:100'],
            'payer_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'acc1_id' => ['required', 'integer', 'exists:acc_head,id'],
            'portfolio_id' => ['required', 'integer', 'exists:acc_head,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'acc2_before' => ['nullable', 'numeric'],
            'acc2_after' => ['nullable', 'numeric'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'check_number.required' => 'رقم الشيك مطلوب',
            'check_number.unique' => 'رقم الشيك مستخدم بالفعل',
            'bank_name.required' => 'اسم البنك مطلوب',
            'account_number.required' => 'رقم الحساب مطلوب',
            'account_holder_name.required' => 'اسم صاحب الحساب مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقم',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'issue_date.required' => 'تاريخ الإصدار مطلوب',
            'due_date.required' => 'تاريخ الاستحقاق مطلوب',
            'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ الإصدار',
            'acc1_id.required' => 'الحساب مطلوب',
            'portfolio_id.required' => 'حافظة الأوراق المالية مطلوبة',
            'branch_id.required' => 'الفرع مطلوب',
        ];
    }
}
