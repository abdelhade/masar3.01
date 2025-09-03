<?php

return [

    /*
    |--------------------------------------------------------------------------
    | توثیق زبان کی سطور
    |--------------------------------------------------------------------------
    |
    | مندرجہ ذیل سطور میں ڈیفالٹ ایرر پیغامات شامل ہیں جو
    | ویلیڈیشن کلاس کے ذریعے استعمال ہوتے ہیں۔ ان میں سے کچھ قواعد کے
    | کئی ورژن ہوتے ہیں جیسے سائز کے قواعد۔ آپ انہیں حسبِ ضرورت
    | یہاں ترمیم کر سکتے ہیں۔
    |
    */

    'accepted' => ':attribute فیلڈ کو قبول کیا جانا چاہیے۔',
    'accepted_if' => ':attribute فیلڈ کو قبول کیا جانا چاہیے جب :other :value ہو۔',
    'active_url' => ':attribute فیلڈ ایک درست یو آر ایل ہونا چاہیے۔',
    'after' => ':attribute فیلڈ :date کے بعد کی تاریخ ہونی چاہیے۔',
    'after_or_equal' => ':attribute فیلڈ :date کے بعد یا اس کے برابر کی تاریخ ہونی چاہیے۔',
    'alpha' => ':attribute فیلڈ میں صرف حروف ہونے چاہئیں۔',
    'alpha_dash' => ':attribute فیلڈ میں صرف حروف، نمبر، ڈیش اور انڈرسکور ہو سکتے ہیں۔',
    'alpha_num' => ':attribute فیلڈ میں صرف حروف اور نمبر ہو سکتے ہیں۔',
    'any_of' => ':attribute فیلڈ غلط ہے۔',
    'array' => ':attribute فیلڈ ایک ارے ہونا چاہیے۔',
    'ascii' => ':attribute فیلڈ میں صرف ایک بائٹ حروف اور علامتیں ہونی چاہئیں۔',
    'before' => ':attribute فیلڈ :date سے پہلے کی تاریخ ہونی چاہیے۔',
    'before_or_equal' => ':attribute فیلڈ :date سے پہلے یا برابر کی تاریخ ہونی چاہیے۔',
    'between' => [
        'array' => ':attribute فیلڈ :min اور :max اشیاء کے درمیان ہونا چاہیے۔',
        'file' => ':attribute فیلڈ :min اور :max کلو بائٹ کے درمیان ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ :min اور :max کے درمیان ہونا چاہیے۔',
        'string' => ':attribute فیلڈ :min اور :max حروف کے درمیان ہونا چاہیے۔',
    ],
    'boolean' => ':attribute فیلڈ صحیح یا غلط ہونا چاہیے۔',
    'can' => ':attribute فیلڈ میں غلط قدر ہے۔',
    'confirmed' => ':attribute فیلڈ کی تصدیق میل نہیں کھاتی۔',
    'contains' => ':attribute فیلڈ میں مطلوبہ قدر موجود نہیں ہے۔',
    'current_password' => 'پاس ورڈ غلط ہے۔',
    'date' => ':attribute فیلڈ ایک درست تاریخ ہونی چاہیے۔',
    'date_equals' => ':attribute فیلڈ :date کے برابر تاریخ ہونی چاہیے۔',
    'date_format' => ':attribute فیلڈ کا فارمیٹ :format سے میل کھانا چاہیے۔',
    'decimal' => ':attribute فیلڈ میں :decimal اعشاری مقامات ہونے چاہئیں۔',
    'declined' => ':attribute فیلڈ کو مسترد کیا جانا چاہیے۔',
    'declined_if' => ':attribute فیلڈ کو مسترد کیا جانا چاہیے جب :other :value ہو۔',
    'different' => ':attribute اور :other مختلف ہونا چاہیے۔',
    'digits' => ':attribute فیلڈ :digits ہندسوں کا ہونا چاہیے۔',
    'digits_between' => ':attribute فیلڈ :min اور :max ہندسوں کے درمیان ہونا چاہیے۔',
    'dimensions' => ':attribute فیلڈ کی تصویر کے ابعاد غلط ہیں۔',
    'distinct' => ':attribute فیلڈ میں ڈپلیکیٹ ویلیو ہے۔',
    'doesnt_end_with' => ':attribute فیلڈ کا اختتام ان میں سے کسی پر نہیں ہونا چاہیے: :values.',
    'doesnt_start_with' => ':attribute فیلڈ کا آغاز ان میں سے کسی پر نہیں ہونا چاہیے: :values.',
    'email' => ':attribute فیلڈ ایک درست ای میل ہونا چاہیے۔',
    'ends_with' => ':attribute فیلڈ کا اختتام ان میں سے کسی پر ہونا چاہیے: :values.',
    'enum' => 'منتخب کیا گیا :attribute غلط ہے۔',
    'exists' => 'منتخب کیا گیا :attribute غلط ہے۔',
    'extensions' => ':attribute فیلڈ کا ایکسٹینشن ان میں سے ہونا چاہیے: :values.',
    'file' => ':attribute فیلڈ ایک فائل ہونی چاہیے۔',
    'filled' => ':attribute فیلڈ میں ایک ویلیو ہونی چاہیے۔',
    'gt' => [
        'array' => ':attribute فیلڈ میں :value سے زیادہ اشیاء ہونی چاہئیں۔',
        'file' => ':attribute فیلڈ :value کلو بائٹ سے بڑا ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ :value سے بڑا ہونا چاہیے۔',
        'string' => ':attribute فیلڈ :value حروف سے زیادہ ہونا چاہیے۔',
    ],
    'gte' => [
        'array' => ':attribute فیلڈ میں کم از کم :value اشیاء ہونی چاہئیں۔',
        'file' => ':attribute فیلڈ :value کلو بائٹ سے بڑا یا برابر ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ :value سے بڑا یا برابر ہونا چاہیے۔',
        'string' => ':attribute فیلڈ :value حروف سے بڑا یا برابر ہونا چاہیے۔',
    ],
    'hex_color' => ':attribute فیلڈ ایک درست ہیگز کلر ہونا چاہیے۔',
    'image' => ':attribute فیلڈ ایک تصویر ہونی چاہیے۔',
    'in' => 'منتخب کیا گیا :attribute غلط ہے۔',
    'in_array' => ':attribute فیلڈ :other میں موجود ہونا چاہیے۔',
    'integer' => ':attribute فیلڈ ایک عدد صحیح ہونا چاہیے۔',
    'ip' => ':attribute فیلڈ ایک درست IP ایڈریس ہونا چاہیے۔',
    'ipv4' => ':attribute فیلڈ ایک درست IPv4 ایڈریس ہونا چاہیے۔',
    'ipv6' => ':attribute فیلڈ ایک درست IPv6 ایڈریس ہونا چاہیے۔',
    'json' => ':attribute فیلڈ ایک درست JSON سٹرنگ ہونا چاہیے۔',
    'list' => ':attribute فیلڈ ایک فہرست ہونی چاہیے۔',
    'lowercase' => ':attribute فیلڈ چھوٹے حروف میں ہونا چاہیے۔',
    'lt' => [
        'array' => ':attribute فیلڈ میں :value سے کم اشیاء ہونی چاہئیں۔',
        'file' => ':attribute فیلڈ :value کلو بائٹ سے چھوٹا ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ :value سے چھوٹا ہونا چاہیے۔',
        'string' => ':attribute فیلڈ :value حروف سے چھوٹا ہونا چاہیے۔',
    ],
    'lte' => [
        'array' => ':attribute فیلڈ میں :value سے زیادہ اشیاء نہیں ہونی چاہئیں۔',
        'file' => ':attribute فیلڈ :value کلو بائٹ سے چھوٹا یا برابر ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ :value سے چھوٹا یا برابر ہونا چاہیے۔',
        'string' => ':attribute فیلڈ :value حروف سے چھوٹا یا برابر ہونا چاہیے۔',
    ],
    'mac_address' => ':attribute فیلڈ ایک درست MAC ایڈریس ہونا چاہیے۔',
    'max' => [
        'array' => ':attribute فیلڈ میں :max سے زیادہ اشیاء نہیں ہونی چاہئیں۔',
        'file' => ':attribute فیلڈ :max کلو بائٹ سے بڑا نہیں ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ :max سے بڑا نہیں ہونا چاہیے۔',
        'string' => ':attribute فیلڈ :max حروف سے بڑا نہیں ہونا چاہیے۔',
    ],
    'max_digits' => ':attribute فیلڈ میں :max سے زیادہ ہندسے نہیں ہونے چاہئیں۔',
    'mimes' => ':attribute فیلڈ ایک فائل ہونی چاہیے جس کی قسم ہو: :values.',
    'mimetypes' => ':attribute فیلڈ ایک فائل ہونی چاہیے جس کی قسم ہو: :values.',
    'min' => [
        'array' => ':attribute فیلڈ میں کم از کم :min اشیاء ہونی چاہئیں۔',
        'file' => ':attribute فیلڈ کم از کم :min کلو بائٹ کا ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ کم از کم :min ہونا چاہیے۔',
        'string' => ':attribute فیلڈ کم از کم :min حروف کا ہونا چاہیے۔',
    ],
    'min_digits' => ':attribute فیلڈ میں کم از کم :min ہندسے ہونے چاہئیں۔',
    'missing' => ':attribute فیلڈ غائب ہونا چاہیے۔',
    'missing_if' => ':attribute فیلڈ غائب ہونا چاہیے جب :other :value ہو۔',
    'missing_unless' => ':attribute فیلڈ غائب ہونا چاہیے جب تک :other :value نہ ہو۔',
    'missing_with' => ':attribute فیلڈ غائب ہونا چاہیے جب :values موجود ہوں۔',
    'missing_with_all' => ':attribute فیلڈ غائب ہونا چاہیے جب سب :values موجود ہوں۔',
    'multiple_of' => ':attribute فیلڈ :value کا مضرب ہونا چاہیے۔',
    'not_in' => 'منتخب کیا گیا :attribute غلط ہے۔',
    'not_regex' => ':attribute فیلڈ کا فارمیٹ غلط ہے۔',
    'numeric' => ':attribute فیلڈ ایک نمبر ہونا چاہیے۔',
    'password' => [
        'letters' => ':attribute فیلڈ میں کم از کم ایک حرف ہونا چاہیے۔',
        'mixed' => ':attribute فیلڈ میں کم از کم ایک بڑا اور ایک چھوٹا حرف ہونا چاہیے۔',
        'numbers' => ':attribute فیلڈ میں کم از کم ایک نمبر ہونا چاہیے۔',
        'symbols' => ':attribute فیلڈ میں کم از کم ایک علامت ہونا چاہیے۔',
        'uncompromised' => 'منتخب کیا گیا :attribute ڈیٹا لیک میں پایا گیا ہے۔ براہ کرم دوسرا :attribute منتخب کریں۔',
    ],
    'present' => ':attribute فیلڈ موجود ہونا چاہیے۔',
    'present_if' => ':attribute فیلڈ موجود ہونا چاہیے جب :other :value ہو۔',
    'present_unless' => ':attribute فیلڈ موجود ہونا چاہیے جب تک :other :value نہ ہو۔',
    'present_with' => ':attribute فیلڈ موجود ہونا چاہیے جب :values موجود ہوں۔',
    'present_with_all' => ':attribute فیلڈ موجود ہونا چاہیے جب سب :values موجود ہوں۔',
    'prohibited' => ':attribute فیلڈ ممنوع ہے۔',
    'prohibited_if' => ':attribute فیلڈ ممنوع ہے جب :other :value ہو۔',
    'prohibited_if_accepted' => ':attribute فیلڈ ممنوع ہے جب :other قبول کیا جائے۔',
    'prohibited_if_declined' => ':attribute فیلڈ ممنوع ہے جب :other مسترد کیا جائے۔',
    'prohibited_unless' => ':attribute فیلڈ ممنوع ہے جب تک :other :values میں نہ ہو۔',
    'prohibits' => ':attribute فیلڈ :other کو موجود ہونے سے روکتا ہے۔',
    'regex' => ':attribute فیلڈ کا فارمیٹ غلط ہے۔',
    'required' => ':attribute فیلڈ ضروری ہے۔',
    'required_array_keys' => ':attribute فیلڈ میں :values کی اندراجات ہونی چاہئیں۔',
    'required_if' => ':attribute فیلڈ ضروری ہے جب :other :value ہو۔',
    'required_if_accepted' => ':attribute فیلڈ ضروری ہے جب :other قبول کیا جائے۔',
    'required_if_declined' => ':attribute فیلڈ ضروری ہے جب :other مسترد کیا جائے۔',
    'required_unless' => ':attribute فیلڈ ضروری ہے جب تک :other :values میں نہ ہو۔',
    'required_with' => ':attribute فیلڈ ضروری ہے جب :values موجود ہوں۔',
    'required_with_all' => ':attribute فیلڈ ضروری ہے جب سب :values موجود ہوں۔',
    'required_without' => ':attribute فیلڈ ضروری ہے جب :values موجود نہ ہوں۔',
    'required_without_all' => ':attribute فیلڈ ضروری ہے جب کوئی بھی :values موجود نہ ہو۔',
    'same' => ':attribute فیلڈ :other سے میل کھانی چاہیے۔',
    'size' => [
        'array' => ':attribute فیلڈ میں :size اشیاء ہونی چاہئیں۔',
        'file' => ':attribute فیلڈ :size کلو بائٹ کا ہونا چاہیے۔',
        'numeric' => ':attribute فیلڈ :size ہونا چاہیے۔',
        'string' => ':attribute فیلڈ :size حروف کا ہونا چاہیے۔',
    ],
    'starts_with' => ':attribute فیلڈ کا آغاز ان میں سے کسی پر ہونا چاہیے: :values.',
    'string' => ':attribute فیلڈ ایک سٹرنگ ہونی چاہیے۔',
    'timezone' => ':attribute فیلڈ ایک درست ٹائم زون ہونا چاہیے۔',
    'unique' => ':attribute پہلے ہی لیا جا چکا ہے۔',
    'uploaded' => ':attribute اپ لوڈ ناکام ہوا۔',
    'uppercase' => ':attribute فیلڈ بڑے حروف میں ہونا چاہیے۔',
    'url' => ':attribute فیلڈ ایک درست یو آر ایل ہونا چاہیے۔',
    'ulid' => ':attribute فیلڈ ایک درست ULID ہونا چاہیے۔',
    'uuid' => ':attribute فیلڈ ایک درست UUID ہونا چاہیے۔',

    /*
    |--------------------------------------------------------------------------
    | کسٹم توثیق زبان کی سطور
    |--------------------------------------------------------------------------
    |
    | آپ کچھ خاص خصوصیات کے لیے اپنی مرضی کے توثیق پیغامات متعین کر سکتے ہیں
    | "attribute.rule" نام کے ساتھ۔
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'کسٹم پیغام',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | کسٹم توثیق کے خواص
    |--------------------------------------------------------------------------
    |
    | مندرجہ ذیل سطور attribute placeholders کو زیادہ قابل فہم الفاظ میں بدلتی ہیں
    | جیسے "ای میل" کی بجائے "email"۔
    |
    */

    'attributes' => [],

];
