<?php

namespace Modules\Progress\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isDraft = (bool) $this->input('save_as_draft');

        // ✅ للـ draft: name فقط مطلوب، باقي الحقول optional
        if ($isDraft) {
            return [
                'name' => 'required|string|max:255',
                'client_id' => 'nullable|exists:clients,id',
                'status' => 'nullable|in:active,completed,pending',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'description' => 'nullable|string',
                'working_zone' => 'nullable|string|max:255',
                'project_type_id' => 'nullable|exists:project_types,id',
                'working_days' => 'nullable|integer|min:1|max:7',
                'daily_work_hours' => 'nullable|integer|min:1|max:24',
                'weekly_holidays' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.work_item_id' => 'nullable|exists:work_items,id',
                'items.*.total_quantity' => 'nullable|numeric|min:0',
                'items.*.estimated_daily_qty' => 'nullable|numeric|min:0',
                'items.*.duration' => 'nullable|integer|min:0',
                'items.*.predecessor' => 'nullable|string',
                'items.*.start_date' => 'nullable|date',
                'items.*.end_date' => 'nullable|date',
                'items.*.dependency_type' => 'nullable|in:end_to_start,start_to_start',
                'items.*.lag' => 'nullable|integer',
                'items.*.notes' => 'nullable|string',
                'items.*.is_measurable' => 'nullable|boolean',
                'items.*.subproject_name' => 'nullable|string|max:255',
                'employees' => 'nullable|array',
                'employees.*' => 'nullable|exists:employees,id',
                'subprojects' => 'nullable|array',
                'subprojects.*.name' => 'nullable|string|max:255',
                'subprojects.*.start_date' => 'nullable|date',
                'subprojects.*.end_date' => 'nullable|date',
                'subprojects.*.total_quantity' => 'nullable|numeric',
                'subprojects.*.unit' => 'nullable|string|max:50',
            ];
        }

        // ✅ للمشروع الكامل: validations كاملة
        return [
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:active,completed,pending',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'working_zone' => 'required|string|max:255',
            'project_type_id' => 'required|exists:project_types,id',
            'working_days' => 'required|integer|min:1|max:7',
            'daily_work_hours' => 'required|integer|min:1|max:24',
            'weekly_holidays' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.work_item_id' => 'required|exists:work_items,id',
            'items.*.total_quantity' => 'required|numeric|min:0',
            'items.*.estimated_daily_qty' => 'required|numeric|min:0',
            'items.*.duration' => 'nullable|integer|min:0',
            'items.*.predecessor' => 'nullable|string',
            'items.*.start_date' => 'required|date',
            'items.*.end_date' => 'required|date',
            'items.*.dependency_type' => 'nullable|in:end_to_start,start_to_start',
            'items.*.lag' => 'nullable|integer',
            'items.*.notes' => 'nullable|string',
            'items.*.is_measurable' => 'nullable|boolean',
            'items.*.subproject_name' => 'nullable|string|max:255',
            'employees' => 'required|array|min:1',
            'employees.*' => 'nullable|exists:employees,id',
            'subprojects' => 'nullable|array',
            'subprojects.*.name' => 'nullable|string|max:255',
            'subprojects.*.start_date' => 'nullable|date',
            'subprojects.*.end_date' => 'nullable|date',
            'subprojects.*.total_quantity' => 'nullable|numeric',
            'subprojects.*.unit' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المشروع مطلوب',
            'client_id.required' => 'العميل مطلوب',
            'start_date.required' => 'تاريخ البداية مطلوب',
            'end_date.required' => 'تاريخ النهاية مطلوب',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية',
            'items.required' => 'يجب إضافة بند واحد على الأقل',
            'employees.required' => 'يجب اختيار موظف واحد على الأقل',
        ];
    }
}
