<?php

namespace Modules\Services\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceBookingRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bookingId = $this->route('booking') ? $this->route('booking')->id : null;

        return [
            'service_id' => 'required|exists:services,id',
            'customer_id' => 'required|exists:acc_head,id',
            'employee_id' => 'nullable|exists:acc_head,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'customer_notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_id.required' => 'الخدمة مطلوبة',
            'service_id.exists' => 'الخدمة المحددة غير موجودة',
            'customer_id.required' => 'العميل مطلوب',
            'customer_id.exists' => 'العميل المحدد غير موجود',
            'employee_id.exists' => 'الموظف المحدد غير موجود',
            'booking_date.required' => 'تاريخ الحجز مطلوب',
            'booking_date.date' => 'تاريخ الحجز يجب أن يكون تاريخ صحيح',
            'booking_date.after_or_equal' => 'تاريخ الحجز يجب أن يكون اليوم أو بعده',
            'start_time.required' => 'وقت البداية مطلوب',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بصيغة ساعة:دقيقة',
            'end_time.date_format' => 'وقت النهاية يجب أن يكون بصيغة ساعة:دقيقة',
            'end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية',
            'price.required' => 'السعر مطلوب',
            'price.numeric' => 'السعر يجب أن يكون رقماً',
            'price.min' => 'السعر يجب أن يكون أكبر من أو يساوي صفر',
            'notes.max' => 'الملاحظات يجب أن تكون أقل من 1000 حرف',
            'customer_notes.max' => 'ملاحظات العميل يجب أن تكون أقل من 1000 حرف',
            'branch_id.exists' => 'الفرع المحدد غير موجود',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_id' => 'الخدمة',
            'customer_id' => 'العميل',
            'employee_id' => 'الموظف',
            'booking_date' => 'تاريخ الحجز',
            'start_time' => 'وقت البداية',
            'end_time' => 'وقت النهاية',
            'price' => 'السعر',
            'notes' => 'الملاحظات',
            'customer_notes' => 'ملاحظات العميل',
            'branch_id' => 'الفرع',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for time conflicts
            if ($this->filled(['service_id', 'booking_date', 'start_time'])) {
                $conflictingBooking = \Modules\Services\Models\ServiceBooking::where('service_id', $this->get('service_id'))
                    ->where('booking_date', $this->get('booking_date'))
                    ->where('is_cancelled', false)
                    ->where(function ($query) {
                        $startTime = $this->get('start_time');
                        $endTime = $this->get('end_time');
                        
                        if (!$endTime) {
                            // Calculate end time based on service duration
                            $service = \Modules\Services\Models\Service::find($this->get('service_id'));
                            if ($service) {
                                $endTime = \Carbon\Carbon::parse($startTime)
                                    ->addMinutes(60) // Default 60 minutes
                                    ->format('H:i:s');
                            }
                        }

                        $query->where(function ($q) use ($startTime, $endTime) {
                            $q->whereBetween('start_time', [$startTime, $endTime])
                              ->orWhereBetween('end_time', [$startTime, $endTime])
                              ->orWhere(function ($q2) use ($startTime, $endTime) {
                                  $q2->where('start_time', '<=', $startTime)
                                     ->where('end_time', '>=', $endTime);
                              });
                        });
                    })
                    ->when($this->route('booking'), function ($query) {
                        $query->where('id', '!=', $this->route('booking')->id);
                    })
                    ->exists();

                if ($conflictingBooking) {
                    $validator->errors()->add('start_time', 'هذا الوقت محجوز مسبقاً لنفس الخدمة');
                }
            }
        });
    }
}
