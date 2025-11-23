<?php

namespace Modules\MyResources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resourceId = $this->route('resource') ? $this->route('resource')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'resource_category_id' => ['required', 'exists:resource_categories,id'],
            'resource_type_id' => ['required', 'exists:resource_types,id'],
            'resource_status_id' => ['required', 'exists:resource_statuses,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'model_number' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'warranty_expiry' => ['nullable', 'date'],
            'last_maintenance_date' => ['nullable', 'date'],
            'next_maintenance_date' => ['nullable', 'date', 'after_or_equal:last_maintenance_date'],
            'specifications' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المورد مطلوب',
            'resource_category_id.required' => 'التصنيف الرئيسي مطلوب',
            'resource_category_id.exists' => 'التصنيف الرئيسي غير موجود',
            'resource_type_id.required' => 'نوع المورد مطلوب',
            'resource_type_id.exists' => 'نوع المورد غير موجود',
            'resource_status_id.required' => 'حالة المورد مطلوبة',
            'resource_status_id.exists' => 'حالة المورد غير موجودة',
            'branch_id.exists' => 'الفرع غير موجود',
            'employee_id.exists' => 'الموظف غير موجود',
            'purchase_cost.numeric' => 'تكلفة الشراء يجب أن تكون رقمًا',
            'purchase_cost.min' => 'تكلفة الشراء يجب أن تكون أكبر من أو تساوي 0',
            'daily_rate.numeric' => 'التكلفة اليومية يجب أن تكون رقمًا',
            'daily_rate.min' => 'التكلفة اليومية يجب أن تكون أكبر من أو تساوي 0',
            'hourly_rate.numeric' => 'التكلفة بالساعة يجب أن تكون رقمًا',
            'hourly_rate.min' => 'التكلفة بالساعة يجب أن تكون أكبر من أو تساوي 0',
            'next_maintenance_date.after_or_equal' => 'تاريخ الصيانة القادمة يجب أن يكون بعد أو يساوي تاريخ آخر صيانة',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active') && $this->is_active === null) {
            $this->merge(['is_active' => false]);
        }
    }
}

