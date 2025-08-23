<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CRM\Enums\ActivityTypeEnum;

class ActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // لو عايز تحدد صلاحيات ممكن تضيف هنا check للمستخدم
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|in:' . implode(',', ActivityTypeEnum::values()),
            'activity_date' => 'required|date',         // اليوم
            'scheduled_at' => 'nullable|date_format:H:i',
            'client_id'     => 'nullable|exists:crm_clients,id',
            'assigned_to'   => 'nullable|exists:users,id',
        ];
    }

    /**
     * رسائل التحقق
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان النشاط مطلوب.',
            'title.string'   => 'العنوان يجب أن يكون نص.',
            'type.required'  => 'نوع النشاط مطلوب.',
            'type.in'        => 'النوع يجب أن يكون مكالمة أو رسالة أو اجتماع.',
            'activity_date.required' => 'تاريخ النشاط مطلوب.',
            'activity_date.date'     => 'تاريخ النشاط غير صالح.',
            'scheduled_at.date_format' => 'الوقت يجب أن يكون بالصيغة الصحيحة (Y-m-d H:i:s).',
            'client_id.required' => 'العميل مطلوب.',
            'client_id.exists'   => 'العميل غير موجود.',
            'assigned_to.required' => 'الموظف المسؤول مطلوب.',
            'assigned_to.exists'   => 'الموظف غير موجود.',
        ];
    }
}
