<?php

namespace Modules\MyResources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_id' => ['required', 'exists:resources,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'actual_end_date' => ['nullable', 'date'],
            'status' => ['required', 'in:scheduled,active,completed,cancelled'],
            'assignment_type' => ['required', 'in:current,upcoming,past'],
            'daily_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'resource_id.required' => 'المورد مطلوب',
            'resource_id.exists' => 'المورد غير موجود',
            'project_id.required' => 'المشروع مطلوب',
            'project_id.exists' => 'المشروع غير موجود',
            'start_date.required' => 'تاريخ البداية مطلوب',
            'start_date.date' => 'تاريخ البداية غير صحيح',
            'end_date.date' => 'تاريخ النهاية غير صحيح',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
            'status.required' => 'الحالة مطلوبة',
            'status.in' => 'الحالة غير صحيحة',
            'assignment_type.required' => 'نوع التعيين مطلوب',
            'assignment_type.in' => 'نوع التعيين غير صحيح',
            'daily_cost.numeric' => 'التكلفة اليومية يجب أن تكون رقمًا',
            'daily_cost.min' => 'التكلفة اليومية يجب أن تكون أكبر من أو تساوي 0',
        ];
    }
}

