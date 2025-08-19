<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shipmentId = $this->route('shipment')?->id;

        return [
            'tracking_number'     => 'required|string|unique:shipments,tracking_number,' . $shipmentId,
            'shipping_company_id' => 'required|exists:shipping_companies,id',
            'customer_name'       => 'required|string|max:255',
            'customer_address'    => 'required|string',
            'weight'              => 'required|numeric|min:0',
            'status'              => 'required|in:pending,in_transit,delivered',
        ];
    }

    public function messages(): array
    {
        return [
            'tracking_number.required' => 'رقم التتبع مطلوب.',
            'tracking_number.unique'   => 'رقم التتبع مستخدم من قبل.',

            'shipping_company_id.required' => 'يجب اختيار شركة الشحن.',
            'shipping_company_id.exists'   => 'شركة الشحن غير موجودة.',

            'customer_name.required' => 'اسم العميل مطلوب.',
            'customer_name.max'      => 'اسم العميل يجب ألا يتجاوز 255 حرف.',

            'customer_address.required' => 'عنوان العميل مطلوب.',

            'weight.required' => 'الوزن مطلوب.',
            'weight.numeric'  => 'الوزن يجب أن يكون رقم.',
            'weight.min'      => 'الوزن يجب أن يكون أكبر من أو يساوي 0.',

            'status.required' => 'حالة الشحنة مطلوبة.',
            'status.in'       => 'حالة الشحنة غير صحيحة.',
        ];
    }
}
