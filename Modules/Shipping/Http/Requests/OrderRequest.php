<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orderId = $this->route('order')?->id;

        return [
            'order_number'   => 'required|string|unique:orders,order_number,' . $orderId,
            'driver_id'      => 'required|exists:drivers,id',
            'shipment_id'    => 'required|exists:shipments,id',
            'customer_name'  => 'required|string|max:255',
            'customer_address' => 'required|string',
            'delivery_status'  => 'required|in:pending,assigned,in_transit,delivered',
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.required' => 'رقم الطلب مطلوب.',
            'order_number.unique'   => 'رقم الطلب مستخدم من قبل.',

            'driver_id.required' => 'يجب اختيار السائق.',
            'driver_id.exists'   => 'السائق غير موجود.',

            'shipment_id.required' => 'يجب اختيار الشحنة.',
            'shipment_id.exists'   => 'الشحنة غير موجودة.',

            'customer_name.required' => 'اسم العميل مطلوب.',
            'customer_name.string'   => 'اسم العميل يجب أن يكون نص.',
            'customer_name.max'      => 'اسم العميل يجب ألا يتجاوز 255 حرف.',

            'customer_address.required' => 'عنوان العميل مطلوب.',

            'delivery_status.required' => 'حالة التوصيل مطلوبة.',
            'delivery_status.in'       => 'حالة التوصيل غير صحيحة.',
        ];
    }
}
