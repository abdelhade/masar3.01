<?php

namespace Modules\Progress\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

/**
 * @method mixed input($key = null, $default = null)
 */
class ProjectProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // سيتم التحقق من الصلاحيات في الـ Policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:to_date',
            ],
            'to_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
            ],
            'as_of_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from_date.date' => __('validation.date'),
            'from_date.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
            'from_date.before_or_equal' => 'تاريخ "من" يجب أن يكون قبل أو يساوي تاريخ "إلى"',
            'to_date.date' => __('validation.date'),
            'to_date.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
            'as_of_date.date' => __('validation.date'),
            'as_of_date.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
        ];
    }

    /**
     * Get the validated from_date or default to 1 week ago.
     */
    public function getFromDate(): Carbon
    {
        $fromDate = $this->input('from_date');
        
        if (!empty($fromDate)) {
            try {
                return Carbon::parse($fromDate)->startOfDay();
            } catch (\Exception $e) {
                // Fallback to default
            }
        }
        
        return Carbon::today()->subWeek()->startOfDay();
    }

    /**
     * Get the validated to_date or default to today.
     */
    public function getToDate(): Carbon
    {
        $toDate = $this->input('to_date');
        
        if (!empty($toDate)) {
            try {
                return Carbon::parse($toDate)->endOfDay();
            } catch (\Exception $e) {
                // Fallback to default
            }
        }
        
        // Use as_of_date if provided, otherwise use today
        $asOfDate = $this->getAsOfDate();
        return $asOfDate->copy()->endOfDay();
    }

    /**
     * Get the validated as_of_date or default to today.
     * This date represents the "current" date for progress calculations.
     */
    public function getAsOfDate(): Carbon
    {
        $asOfDate = $this->input('as_of_date');
        
        if (!empty($asOfDate)) {
            try {
                return Carbon::parse($asOfDate)->startOfDay();
            } catch (\Exception $e) {
                // Fallback to default
            }
        }
        
        return Carbon::today()->startOfDay();
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // يمكن إضافة logging هنا إذا لزم الأمر
        parent::failedValidation($validator);
    }
}

