<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Validation\Rules\Enum;
use Modules\CRM\Enums\TaskStatusEnum;
use Modules\CRM\Enums\TaskPriorityEnum;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'client_id'      => ['required', 'exists:clients,id'],
            'user_id'        => ['required', 'exists:users,id'],
            'task_type_id' => ['required', 'exists:task_types,id'],
            'title'          => ['required', 'string', 'max:255'],
            'status' => ['required', new Enum(TaskStatusEnum::class)],
            'priority' => ['required', new Enum(TaskPriorityEnum::class)],
            'start_date'  => ['required', 'date'],
            'delivery_date'  => ['required', 'date'],
            'client_comment' => ['nullable', 'string'],
            'user_comment'   => ['nullable', 'string'],
            'attachment'     => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'العميل مطلوب.',
            'client_id.exists'   => 'العميل غير موجود.',

            'user_id.required' => 'المستخدم مطلوب.',
            'user_id.exists'   => 'المستخدم غير موجود.',

            'task_type.required' => 'نوع المهمة مطلوب.',
            'task_type.string'   => 'نوع المهمة يجب أن يكون نصاً.',

            'title.required' => 'عنوان المهمة مطلوب.',
            'title.string'   => 'عنوان المهمة يجب أن يكون نصاً.',
            'title.max'      => 'عنوان المهمة يجب ألا يتجاوز 255 حرفاً.',

            'status.required' => 'حالة المهمة مطلوبة.',
            'status.in'       => 'حالة المهمة غير صحيحة.',

            'delivery_date.required' => 'تاريخ التسليم مطلوب.',
            'delivery_date.date'     => 'تاريخ التسليم يجب أن يكون تاريخاً صالحاً.',

            'start_date.required' => 'تاريخ البدايه مطلوب.',
            'start_date.date'     => 'تاريخ البدايه يجب أن يكون تاريخاً صالحاً.',

            'client_comment.string' => 'تعليق العميل يجب أن يكون نصاً.',
            'user_comment.string'   => 'تعليق المستخدم يجب أن يكون نصاً.',

            'attachment.file'  => 'الملف المرفق يجب أن يكون ملفاً.',
            'attachment.max'   => 'الملف لا يجب أن يتجاوز 5 ميجابايت.',
            'attachment.mimes' => 'صيغة الملف غير مدعومة. الصيغ المسموحة: jpg, jpeg, png, pdf, doc, docx.',
        ];
    }


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
