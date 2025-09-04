<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client') ?? $this->id; // الحصول على ID من route model binding أو الـ Request

        $rules = [
            'cname'            => 'required|string',
            'phone'            => 'nullable|string|max:20',
            'phone2'           => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:250',
            'address2'         => 'nullable|string|max:150',
            'date_of_birth'    => 'nullable|date',
            'national_id'      => 'nullable|string|max:50',
            'contact_person'   => 'nullable|string|max:100',
            'contact_phone'    => 'nullable|string|max:20',
            'contact_relation' => 'nullable|string|max:50',
            'info'             => 'nullable|string|max:200',
            'job'              => 'nullable|string|max:50',
            'gender'           => 'nullable|in:male,female',
            'isdeleted'        => 'boolean',
            'is_active'        => 'boolean',
            'tenant'           => 'nullable|integer',
            'branch'           => 'nullable|integer',
            'type'       => 'required|in:person,company',

        ];

        // إضافة قاعدة التحقق لـ email فقط إذا تم إدخال قيمة
        $this->whenFilled('email', function ($input) use (&$rules, $clientId) {
            $rules['email'] = [
                'nullable',
                'email',
                Rule::unique('clients', 'email')->ignore($clientId),
            ];
        }, function ($input) use (&$rules) {
            // إذا كان email فارغاً، لا تضف قاعدة unique
            $rules['email'] = ['nullable', 'email'];
        });

        return $rules;
    }

    public function messages(): array
    {
        return [
            'cname.required' => 'اسم العميل مطلوب',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique'   => 'البريد الإلكتروني مستخدم بالفعل',
            'gender.in'      => 'النوع يجب أن يكون ذكر أو أنثى فقط',
        ];
    }
}
