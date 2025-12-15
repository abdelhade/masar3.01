<?php

namespace Modules\Checks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCheckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('check'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $check = $this->route('check');

        return [
            'check_number' => ['required', 'string', 'max:50', 'unique:checks,check_number,'.$check->id],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'in:pending,cleared,bounced,cancelled'],
            'type' => ['required', 'in:incoming,outgoing'],
            'payee_name' => ['nullable', 'string', 'max:100'],
            'payer_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string', 'max:50'],
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
        ];
    }
}
