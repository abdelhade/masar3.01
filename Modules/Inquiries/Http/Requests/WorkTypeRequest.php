<?php

namespace Modules\Inquiries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $workTypeId = $this->route('work_type');
        $parentId = $this->input('parent_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                // Unique rule: name must be unique within same parent level
                Rule::unique('work_types', 'name')
                    ->ignore($workTypeId)
                    ->where(function ($query) use ($parentId) {
                        return $query->where('parent_id', $parentId);
                    })
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:work_types,id',
                // Prevent making a work type a child of itself
                function ($attribute, $value, $fail) use ($workTypeId) {
                    if ($workTypeId && $value && $workTypeId == $value) {
                        $fail('لا يمكن جعل نوع العمل فرع من نفسه');
                    }
                }
            ],
            'is_active' => [
                'boolean'
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم نوع العمل مطلوب',
            'name.string' => 'اسم نوع العمل يجب أن يكون نص',
            'name.max' => 'اسم نوع العمل يجب ألا يتجاوز 255 حرف',
            'name.min' => 'اسم نوع العمل يجب أن يكون على الأقل حرفين',
            'name.unique' => 'اسم نوع العمل موجود بالفعل في نفس المستوى',
            'parent_id.integer' => 'معرف نوع العمل الأب يجب أن يكون رقم صحيح',
            'parent_id.exists' => 'نوع العمل الأب المحدد غير موجود',
            'is_active.boolean' => 'حالة التفعيل يجب أن تكون صحيحة أو خاطئة'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم نوع العمل',
            'parent_id' => 'نوع العمل الأب',
            'is_active' => 'حالة التفعيل'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and prepare name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name'))
            ]);
        }

        // Handle is_active field
        if ($this->has('is_active')) {
            $isActive = $this->input('is_active');

            // Convert string values to boolean
            if (is_string($isActive)) {
                if (in_array(strtolower($isActive), ['true', '1', 'on', 'yes'])) {
                    $isActive = true;
                } elseif (in_array(strtolower($isActive), ['false', '0', 'off', 'no'])) {
                    $isActive = false;
                } else {
                    $isActive = (bool) $isActive;
                }
            } else {
                $isActive = (bool) $isActive;
            }

            $this->merge([
                'is_active' => $isActive
            ]);
        }

        // Handle parent_id - convert empty string to null
        if ($this->has('parent_id') && $this->input('parent_id') === '') {
            $this->merge([
                'parent_id' => null
            ]);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->ajax()) {
            $response = response()->json([
                'success' => false,
                'message' => 'يوجد أخطاء في البيانات المدخلة',
                'errors' => $validator->errors()->toArray()
            ], 422);

            throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
        }

        parent::failedValidation($validator);
    }

    /**
     * Get validated data with default values.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Set default values
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['parent_id'] = $validated['parent_id'] ?? null;

        return $validated;
    }
}
