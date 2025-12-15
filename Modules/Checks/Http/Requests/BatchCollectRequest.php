<?php

namespace Modules\Checks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchCollectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('clear', \Modules\Checks\Models\Check::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:checks,id'],
            'bank_account_id' => ['required', 'integer', 'exists:acc_head,id'],
            'collection_date' => ['required', 'date'],
            'branch_id' => ['required', 'exists:branches,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'يجب اختيار شيك واحد على الأقل',
            'ids.array' => 'الشيكات المحددة غير صحيحة',
            'ids.min' => 'يجب اختيار شيك واحد على الأقل',
            'bank_account_id.required' => 'حساب البنك مطلوب',
            'collection_date.required' => 'تاريخ التحصيل مطلوب',
            'branch_id.required' => 'الفرع مطلوب',
        ];
    }
}
