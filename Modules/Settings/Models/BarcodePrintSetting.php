<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarcodePrintSetting extends Model
{
    use HasFactory;

    protected $table = 'barcode_print_settings';

    protected $fillable = [
        'name',
        'company_name',
        'paper_width',
        'paper_height',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'show_company_name',
        'show_item_name',
        'show_item_code',
        'show_barcode_image',
        'show_price_before_discount',
        'show_price_after_discount',
        'font_size_company',
        'font_size_item',
        'font_size_price',
        'barcode_width',
        'barcode_height',
        'invert_colors',
        'text_align',
        'is_default',
        'is_active',
        'custom_fields',
    ];

    protected $casts = [
        'show_company_name' => 'boolean',
        'show_item_name' => 'boolean',
        'show_item_code' => 'boolean',
        'show_barcode_image' => 'boolean',
        'show_price_before_discount' => 'boolean',
        'show_price_after_discount' => 'boolean',
        'invert_colors' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'custom_fields' => 'json',
    ];
}
