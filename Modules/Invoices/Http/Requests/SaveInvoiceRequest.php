<?php

declare(strict_types=1);

namespace Modules\Invoices\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for saving invoices (create/update)
 */
class SaveInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic fields
            'type' => ['required', 'integer'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'template_id' => ['nullable', 'integer', 'exists:invoice_templates,id'],
            'acc1_id' => ['required', 'integer', 'exists:acc_head,id'],
            'acc2_id' => ['required', 'integer', 'exists:acc_head,id'],
            'pro_date' => ['required', 'date'],
            'emp_id' => ['nullable', 'integer'],
            'delivery_id' => ['nullable', 'integer'],
            'accural_date' => ['nullable', 'date'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'cash_box_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'currency_id' => ['nullable', 'integer'],
            'currency_rate' => ['required', 'numeric', 'min:0.001'],
            'op2' => ['nullable', 'integer'],

            // Calculations
            'subtotal' => ['required', 'numeric'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'additional_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_value' => ['nullable', 'numeric', 'min:0'],
            'vat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_value' => ['nullable', 'numeric', 'min:0'],
            'withholding_tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'withholding_tax_value' => ['nullable', 'numeric', 'min:0'],
            'total_after_additional' => ['required', 'numeric'],
            'received_from_client' => ['nullable', 'numeric', 'min:0'],
            'remaining' => ['nullable', 'numeric'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.additional' => ['nullable', 'numeric', 'min:0'],
            'items.*.sub_value' => ['required', 'numeric'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'type.required' => 'نوع الفاتورة مطلوب',
            'branch_id.required' => 'الفرع مطلوب',
            'acc1_id.required' => 'الحساب الأول مطلوب',
            'acc2_id.required' => 'الحساب الثاني مطلوب',
            'pro_date.required' => 'تاريخ الفاتورة مطلوب',
            'items.required' => 'يجب إضافة أصناف للفاتورة',
            'items.min' => 'يجب إضافة صنف واحد على الأقل',
            'currency_rate.required' => 'سعر الصرف مطلوب',
            'currency_rate.min' => 'سعر الصرف يجب أن يكون أكبر من صفر',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::error('SaveInvoiceRequest: Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all(),
        ]);

        parent::failedValidation($validator);
    }
}
