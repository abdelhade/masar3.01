<?php

declare(strict_types=1);

namespace Modules\POS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KitchenPrinterStationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stationId = $this->route('kitchen_printer')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'printer_name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('pos.printer_station_name_required'),
            'name.max' => __('pos.printer_station_name_max'),
            'printer_name.required' => __('pos.printer_name_required'),
            'printer_name.max' => __('pos.printer_name_max'),
        ];
    }
}
