<?php

declare(strict_types=1);

namespace Modules\Rentals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Rentals\Enums\LeaseStatus;
use Modules\Rentals\Models\RentalsLease;

class RentalsLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaseId = $this->route('lease'); // للتعديل

        return [
            'unit_id'        => [
                'required',
                'exists:rentals_units,id',
                function ($attribute, $value, $fail) use ($leaseId) {
                    $startDate = $this->input('start_date');
                    $endDate = $this->input('end_date');

                    // التحقق من وجود عقد ساري في نفس الفترة
                    $existingLease = RentalsLease::where('unit_id', $value)
                        ->where('status', LeaseStatus::ACTIVE->value)
                        ->when($leaseId, function ($query) use ($leaseId) {
                            // استثناء العقد الحالي عند التعديل
                            $query->where('id', '!=', $leaseId);
                        })
                        ->where(function ($query) use ($startDate, $endDate) {
                            // التحقق من تداخل الفترات
                            $query->whereBetween('start_date', [$startDate, $endDate])
                                  ->orWhereBetween('end_date', [$startDate, $endDate])
                                  ->orWhere(function ($q) use ($startDate, $endDate) {
                                      $q->where('start_date', '<=', $startDate)
                                        ->where('end_date', '>=', $endDate);
                                  });
                        })
                        ->first();

                    if ($existingLease) {
                        $fail(__('هذه الوحدة لديها عقد إيجار ساري من :start إلى :end. لا يمكن إنشاء عقد جديد حتى ينتهي العقد الحالي.', [
                            'start' => $existingLease->start_date->format('Y-m-d'),
                            'end' => $existingLease->end_date->format('Y-m-d'),
                        ]));
                    }
                }
            ],
            'client_id'      => ['required', 'exists:acc_head,id'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['required', 'date', 'after:start_date'],
            'rent_amount'    => ['required', 'numeric', 'min:0'],
            'rent_type'      => ['required', 'string', 'in:daily,monthly,yearly'],
            'acc_id'         => ['required', 'exists:acc_head,id'],
            'status'         => ['required', 'in:' . implode(',', array_column(LeaseStatus::cases(), 'value'))],
            'notes'          => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required'     => __('The unit field is required.'),
            'unit_id.exists'       => __('The selected unit is invalid.'),
            'client_id.required'   => __('The client field is required.'),
            'client_id.exists'     => __('The selected client is invalid.'),
            'start_date.required'  => __('The start date field is required.'),
            'start_date.date'      => __('The start date is not a valid date.'),
            'end_date.required'    => __('The end date field is required.'),
            'end_date.date'        => __('The end date is not a valid date.'),
            'end_date.after'       => __('The end date must be a date after the start date.'),
            'rent_amount.required' => __('The rent amount field is required.'),
            'rent_amount.numeric'  => __('The rent amount must be a number.'),
            'rent_amount.min'      => __('The rent amount must be at least 0.'),
            'acc_id.required'      => __('The account field is required.'),
            'acc_id.exists'        => __('The selected account is invalid.'),
            'status.required'      => __('The status field is required.'),
            'status.in'            => __('The selected status is invalid.'),
            'notes.string'         => __('The notes must be a string.'),
        ];
    }
}
