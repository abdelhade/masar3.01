<?php

namespace Modules\Settings\Helpers;

use Modules\Settings\Models\BarcodePrintSetting;

class HelperSettings
{
    public static function getBarcodeSettings()
    {
        $settings = BarcodePrintSetting::where('is_default', true)->firstOrFail();

        return [
            'company_name' => $settings->company_name,
            'show_company_name' => $settings->show_company_name,
            'show_item_name' => $settings->show_item_name,
            'show_item_code' => $settings->show_item_code,
            'show_barcode_image' => $settings->show_barcode_image,
            'show_price_before_discount' => $settings->show_price_before_discount,
            'show_price_after_discount' => $settings->show_price_after_discount,
            'paper_width' => $settings->paper_width,
            'paper_height' => $settings->paper_height,
            'margin_top' => $settings->margin_top,
            'margin_bottom' => $settings->margin_bottom,
            'margin_left' => $settings->margin_left,
            'margin_right' => $settings->margin_right,
            'font_size_company' => $settings->font_size_company,
            'font_size_item' => $settings->font_size_item,
            'font_size_price' => $settings->font_size_price,
            'barcode_width' => $settings->barcode_width,
            'barcode_height' => $settings->barcode_height,
            'text_align' => $settings->text_align,
            'invert_colors' => $settings->invert_colors,
            'is_active' => $settings->is_active,
        ];
    }
}
