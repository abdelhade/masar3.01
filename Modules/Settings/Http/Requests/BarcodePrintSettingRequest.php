<?php

namespace Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BarcodePrintSettingRequest extends FormRequest
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
        return [
            'company_name' => 'required|string|max:255',
            'paper_width' => 'required|numeric|min:10|max:100',
            'paper_height' => 'required|numeric|min:10|max:100',
            'margin_top' => 'required|numeric|min:0|max:10',
            'margin_bottom' => 'required|numeric|min:0|max:10',
            'margin_left' => 'required|numeric|min:0|max:10',
            'margin_right' => 'required|numeric|min:0|max:10',
            'font_size_company' => 'required|numeric|min:6|max:20',
            'font_size_item' => 'required|numeric|min:6|max:16',
            'font_size_price' => 'required|numeric|min:6|max:16',
            'barcode_width' => 'required|numeric|min:20|max:80',
            'barcode_height' => 'required|numeric|min:5|max:30',
            'show_company_name' => 'boolean',
            'show_item_name' => 'boolean',
            'show_item_code' => 'boolean',
            'show_barcode_image' => 'boolean',
            'show_price_before_discount' => 'boolean',
            'show_price_after_discount' => 'boolean',
            'invert_colors' => 'boolean',
            'text_align' => 'required|in:center,right,left',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'اسم الشركة مطلوب.',
            'company_name.max' => 'اسم الشركة يجب ألا يزيد عن 255 حرفًا.',
            'paper_width.required' => 'عرض الورقة مطلوب.',
            'paper_width.numeric' => 'عرض الورقة يجب أن يكون رقمًا.',
            'paper_width.min' => 'عرض الورقة يجب أن لا يقل عن 10 مم.',
            'paper_width.max' => 'عرض الورقة يجب أن لا يزيد عن 100 مم.',
            'paper_height.required' => 'ارتفاع الورقة مطلوب.',
            'paper_height.numeric' => 'ارتفاع الورقة يجب أن يكون رقمًا.',
            'paper_height.min' => 'ارتفاع الورقة يجب أن لا يقل عن 10 مم.',
            'paper_height.max' => 'ارتفاع الورقة يجب أن لا يزيد عن 100 مم.',
            'margin_top.required' => 'الهامش العلوي مطلوب.',
            'margin_top.numeric' => 'الهامش العلوي يجب أن يكون رقمًا.',
            'margin_top.min' => 'الهامش العلوي يجب أن لا يقل عن 0 مم.',
            'margin_top.max' => 'الهامش العلوي يجب أن لا يزيد عن 10 مم.',
            'margin_bottom.required' => 'الهامش السفلي مطلوب.',
            'margin_bottom.numeric' => 'الهامش السفلي يجب أن يكون رقمًا.',
            'margin_bottom.min' => 'الهامش السفلي يجب أن لا يقل عن 0 مم.',
            'margin_bottom.max' => 'الهامش السفلي يجب أن لا يزيد عن 10 مم.',
            'margin_left.required' => 'الهامش الأيسر مطلوب.',
            'margin_left.numeric' => 'الهامش الأيسر يجب أن يكون رقمًا.',
            'margin_left.min' => 'الهامش الأيسر يجب أن لا يقل عن 0 مم.',
            'margin_left.max' => 'الهامش الأيسر يجب أن لا يزيد عن 10 مم.',
            'margin_right.required' => 'الهامش الأيمن مطلوب.',
            'margin_right.numeric' => 'الهامش الأيمن يجب أن يكون رقمًا.',
            'margin_right.min' => 'الهامش الأيمن يجب أن لا يقل عن 0 مم.',
            'margin_right.max' => 'الهامش الأيمن يجب أن لا يزيد عن 10 مم.',
            'font_size_company.required' => 'حجم خط اسم الشركة مطلوب.',
            'font_size_company.numeric' => 'حجم خط اسم الشركة يجب أن يكون رقمًا.',
            'font_size_company.min' => 'حجم خط اسم الشركة يجب أن لا يقل عن 6 نقاط.',
            'font_size_company.max' => 'حجم خط اسم الشركة يجب أن لا يزيد عن 20 نقطة.',
            'font_size_item.required' => 'حجم خط اسم الصنف مطلوب.',
            'font_size_item.numeric' => 'حجم خط اسم الصنف يجب أن يكون رقمًا.',
            'font_size_item.min' => 'حجم خط اسم الصنف يجب أن لا يقل عن 6 نقاط.',
            'font_size_item.max' => 'حجم خط اسم الصنف يجب أن لا يزيد عن 16 نقطة.',
            'font_size_price.required' => 'حجم خط السعر مطلوب.',
            'font_size_price.numeric' => 'حجم خط السعر يجب أن يكون رقمًا.',
            'font_size_price.min' => 'حجم خط السعر يجب أن لا يقل عن 6 نقاط.',
            'font_size_price.max' => 'حجم خط السعر يجب أن لا يزيد عن 16 نقطة.',
            'barcode_width.required' => 'عرض الباركود مطلوب.',
            'barcode_width.numeric' => 'عرض الباركود يجب أن يكون رقمًا.',
            'barcode_width.min' => 'عرض الباركود يجب أن لا يقل عن 20 مم.',
            'barcode_width.max' => 'عرض الباركود يجب أن لا يزيد عن 80 مم.',
            'barcode_height.required' => 'ارتفاع الباركود مطلوب.',
            'barcode_height.numeric' => 'ارتفاع الباركود يجب أن يكون رقمًا.',
            'barcode_height.min' => 'ارتفاع الباركود يجب أن لا يقل عن 5 مم.',
            'barcode_height.max' => 'ارتفاع الباركود يجب أن لا يزيد عن 30 مم.',
            'text_align.required' => 'محاذاة النص مطلوبة.',
            'text_align.in' => 'محاذاة النص يجب أن تكون إما وسط، يمين، أو يسار.',
        ];
    }
}
