<?php

namespace Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_id'               => 'required|exists:clients,id',
            'return_date'             => 'required|date',
            'return_type'             => 'required|in:refund,exchange,credit_note',
            'original_invoice_number' => 'nullable|string|max:255',
            'original_invoice_date'   => 'nullable|date',
            'branch_id'               => 'required|exists:branches,id',
            'reason'                  => 'nullable|string',
            'notes'                   => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.item_id'         => 'required|exists:items,id',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.item_condition'  => 'nullable|string',
            'items.*.notes'           => 'nullable|string',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('items')) {
            $items = $this->items;

            foreach ($items as $key => $item) {
                if (isset($item['quantity'])) {
                    // نحول "1.00" إلى 1 ليقبلها الـ validation integer
                    $items[$key]['quantity'] = (int) floatval($item['quantity']);
                }
            }

            $this->merge(['items' => $items]);
        }
    }

    public function attributes()
    {
        return [
            'client_id' => __('Client'),
            'branch_id' => __('Branch'),
            'items' => __('Items'),
            'items.*.item_id' => __('Item'),
            'items.*.quantity' => __('Quantity'),
        ];
    }
}
