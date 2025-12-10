<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMultiJournalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create multi-journals');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pro_type' => ['required', 'integer'],
            'pro_date' => ['required', 'date'],
            'pro_num' => ['nullable', 'string', 'max:255'],
            'emp_id' => ['required', 'exists:acc_head,id'],
            'cost_center' => ['nullable', 'exists:cost_centers,id'],
            'details' => ['required', 'string', 'max:255'],
            'info' => ['nullable', 'string', 'max:255'],
            'info2' => ['nullable', 'string', 'max:255'],
            'info3' => ['nullable', 'string', 'max:255'],
            'account_id' => ['required', 'array', 'min:1'],
            'account_id.*' => ['required', 'exists:acc_head,id'],
            'debit' => ['required', 'array', 'min:1'],
            'debit.*' => ['required', 'numeric', 'min:0'],
            'credit' => ['required', 'array', 'min:1'],
            'credit.*' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'array'],
            'note.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pro_type' => 'نوع العملية',
            'pro_date' => 'التاريخ',
            'pro_num' => 'الرقم الدفتري',
            'emp_id' => 'الموظف',
            'cost_center' => 'مركز التكلفة',
            'details' => 'البيان',
            'info' => 'ملاحظات عامة',
            'info2' => 'ملاحظات 2',
            'info3' => 'ملاحظات 3',
            'account_id' => 'الحساب',
            'account_id.*' => 'الحساب',
            'debit' => 'مدين',
            'debit.*' => 'مدين',
            'credit' => 'دائن',
            'credit.*' => 'دائن',
            'note' => 'ملاحظات',
            'note.*' => 'ملاحظات',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pro_type.required' => 'نوع العملية مطلوب.',
            'pro_type.integer' => 'نوع العملية يجب أن يكون رقم.',
            'pro_date.required' => 'التاريخ مطلوب.',
            'pro_date.date' => 'التاريخ غير صحيح.',
            'pro_num.string' => 'الرقم الدفتري يجب أن يكون نص.',
            'pro_num.max' => 'الرقم الدفتري طويل جداً.',
            'emp_id.required' => 'الموظف مطلوب.',
            'emp_id.exists' => 'الموظف المحدد غير موجود.',
            'cost_center.exists' => 'مركز التكلفة المحدد غير موجود.',
            'details.required' => 'البيان مطلوب.',
            'details.string' => 'البيان يجب أن يكون نص.',
            'details.max' => 'البيان طويل جداً.',
            'info.string' => 'ملاحظات عامة يجب أن تكون نص.',
            'info.max' => 'ملاحظات عامة طويلة جداً.',
            'account_id.required' => 'يجب إدخال حساب واحد على الأقل.',
            'account_id.array' => 'الحسابات يجب أن تكون مصفوفة.',
            'account_id.min' => 'يجب إدخال حساب واحد على الأقل.',
            'account_id.*.required' => 'يجب اختيار حساب لكل صف.',
            'account_id.*.exists' => 'الحساب المحدد غير موجود.',
            'debit.required' => 'يجب إدخال قيم مدينة.',
            'debit.array' => 'القيم المدينة يجب أن تكون مصفوفة.',
            'debit.min' => 'يجب إدخال قيمة مدينة واحدة على الأقل.',
            'debit.*.required' => 'القيمة المدينة مطلوبة.',
            'debit.*.numeric' => 'القيمة المدينة يجب أن تكون رقم.',
            'debit.*.min' => 'القيمة المدينة لا يمكن أن تكون سالبة.',
            'credit.required' => 'يجب إدخال قيم دائنة.',
            'credit.array' => 'القيم الدائنة يجب أن تكون مصفوفة.',
            'credit.min' => 'يجب إدخال قيمة دائنة واحدة على الأقل.',
            'credit.*.required' => 'القيمة الدائنة مطلوبة.',
            'credit.*.numeric' => 'القيمة الدائنة يجب أن تكون رقم.',
            'credit.*.min' => 'القيمة الدائنة لا يمكن أن تكون سالبة.',
            'note.array' => 'الملاحظات يجب أن تكون مصفوفة.',
            'note.*.string' => 'الملاحظة يجب أن تكون نص.',
            'note.*.max' => 'الملاحظة طويلة جداً.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $totalDebit = collect($this->debit ?? [])->sum();
            $totalCredit = collect($this->credit ?? [])->sum();
            $diff = abs($totalDebit - $totalCredit);

            if ($diff >= 0.01) {
                $validator->errors()->add(
                    'balance',
                    'يجب أن تتساوى المجاميع المدينة والدائنة. الفرق الحالي: '.number_format($diff, 2)
                );
            }

            // Check if at least one entry has a value
            $hasValue = false;
            foreach ($this->debit ?? [] as $debit) {
                if (floatval($debit) > 0) {
                    $hasValue = true;
                    break;
                }
            }
            foreach ($this->credit ?? [] as $credit) {
                if (floatval($credit) > 0) {
                    $hasValue = true;
                    break;
                }
            }

            if (! $hasValue) {
                $validator->errors()->add(
                    'entries',
                    'يجب إدخال مبلغ واحد على الأقل.'
                );
            }

            // Check arrays have same length
            $accountCount = count($this->account_id ?? []);
            $debitCount = count($this->debit ?? []);
            $creditCount = count($this->credit ?? []);

            if ($accountCount !== $debitCount || $accountCount !== $creditCount) {
                $validator->errors()->add(
                    'arrays',
                    'عدد الحسابات والقيم المدينة والدائنة يجب أن يكون متساوياً.'
                );
            }
        });
    }
}
