<?php

return [

    /*
    |--------------------------------------------------------------------------
    | पुनः सत्यापन भाषा पंक्तियाँ
    |--------------------------------------------------------------------------
    |
    | निम्नलिखित पंक्तियों में डिफ़ॉल्ट त्रुटि संदेश शामिल हैं जिन्हें
    | सत्यापन ऑब्जेक्ट द्वारा उपयोग किया जाता है। इनमें से कुछ नियमों के
    | कई संस्करण होते हैं जैसे आकार नियम। आप आवश्यकता अनुसार इन्हें यहाँ
    | संशोधित कर सकते हैं।
    |
    */

    'accepted' => ':attribute फ़ील्ड स्वीकार किया जाना चाहिए।',
    'accepted_if' => ':attribute फ़ील्ड स्वीकार किया जाना चाहिए जब :other :value हो।',
    'active_url' => ':attribute फ़ील्ड एक मान्य URL होना चाहिए।',
    'after' => ':attribute फ़ील्ड :date के बाद की तारीख होना चाहिए।',
    'after_or_equal' => ':attribute फ़ील्ड :date के बाद या उसके बराबर की तारीख होना चाहिए।',
    'alpha' => ':attribute फ़ील्ड में केवल अक्षर होने चाहिए।',
    'alpha_dash' => ':attribute फ़ील्ड में केवल अक्षर, अंक, डैश और अंडरस्कोर हो सकते हैं।',
    'alpha_num' => ':attribute फ़ील्ड में केवल अक्षर और अंक हो सकते हैं।',
    'any_of' => ':attribute फ़ील्ड अमान्य है।',
    'array' => ':attribute फ़ील्ड एक ऐरे होना चाहिए।',
    'ascii' => ':attribute फ़ील्ड में केवल एक-बाइट के अल्फ़ान्यूमेरिक वर्ण और प्रतीक हो सकते हैं।',
    'before' => ':attribute फ़ील्ड :date से पहले की तारीख होना चाहिए।',
    'before_or_equal' => ':attribute फ़ील्ड :date से पहले या बराबर की तारीख होना चाहिए।',
    'between' => [
        'array' => ':attribute फ़ील्ड में :min और :max आइटम के बीच होना चाहिए।',
        'file' => ':attribute फ़ील्ड :min और :max किलोबाइट के बीच होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड :min और :max के बीच होना चाहिए।',
        'string' => ':attribute फ़ील्ड :min और :max अक्षरों के बीच होना चाहिए।',
    ],
    'boolean' => ':attribute फ़ील्ड true या false होना चाहिए।',
    'can' => ':attribute फ़ील्ड में अमान्य मान है।',
    'confirmed' => ':attribute फ़ील्ड की पुष्टि मेल नहीं खाती।',
    'contains' => ':attribute फ़ील्ड में आवश्यक मान गायब है।',
    'current_password' => 'पासवर्ड गलत है।',
    'date' => ':attribute फ़ील्ड एक मान्य तारीख होना चाहिए।',
    'date_equals' => ':attribute फ़ील्ड :date के बराबर तारीख होना चाहिए।',
    'date_format' => ':attribute फ़ील्ड का फ़ॉर्मेट :format से मेल खाना चाहिए।',
    'decimal' => ':attribute फ़ील्ड में :decimal दशमलव स्थान होने चाहिए।',
    'declined' => ':attribute फ़ील्ड अस्वीकार किया जाना चाहिए।',
    'declined_if' => ':attribute फ़ील्ड अस्वीकार किया जाना चाहिए जब :other :value हो।',
    'different' => ':attribute और :other अलग होना चाहिए।',
    'digits' => ':attribute फ़ील्ड :digits अंक का होना चाहिए।',
    'digits_between' => ':attribute फ़ील्ड :min और :max अंकों के बीच होना चाहिए।',
    'dimensions' => ':attribute फ़ील्ड में अमान्य चित्र आयाम हैं।',
    'distinct' => ':attribute फ़ील्ड में डुप्लिकेट मान है।',
    'doesnt_end_with' => ':attribute फ़ील्ड का अंत निम्न में से किसी से नहीं होना चाहिए: :values.',
    'doesnt_start_with' => ':attribute फ़ील्ड की शुरुआत निम्न में से किसी से नहीं होनी चाहिए: :values.',
    'email' => ':attribute फ़ील्ड एक मान्य ईमेल होना चाहिए।',
    'ends_with' => ':attribute फ़ील्ड का अंत निम्न में से किसी से होना चाहिए: :values.',
    'enum' => 'चुना हुआ :attribute मान अमान्य है।',
    'exists' => 'चुना हुआ :attribute मान अमान्य है।',
    'extensions' => ':attribute फ़ील्ड का एक्सटेंशन इनमें से एक होना चाहिए: :values.',
    'file' => ':attribute फ़ील्ड एक फ़ाइल होना चाहिए।',
    'filled' => ':attribute फ़ील्ड में मान होना चाहिए।',
    'gt' => [
        'array' => ':attribute फ़ील्ड में :value से अधिक आइटम होने चाहिए।',
        'file' => ':attribute फ़ील्ड :value किलोबाइट से बड़ा होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड :value से बड़ा होना चाहिए।',
        'string' => ':attribute फ़ील्ड :value अक्षरों से अधिक होना चाहिए।',
    ],
    'gte' => [
        'array' => ':attribute फ़ील्ड में कम से कम :value आइटम होने चाहिए।',
        'file' => ':attribute फ़ील्ड :value किलोबाइट से बड़ा या बराबर होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड :value से बड़ा या बराबर होना चाहिए।',
        'string' => ':attribute फ़ील्ड :value अक्षरों से बड़ा या बराबर होना चाहिए।',
    ],
    'hex_color' => ':attribute फ़ील्ड एक मान्य हेक्स रंग होना चाहिए।',
    'image' => ':attribute फ़ील्ड एक चित्र होना चाहिए।',
    'in' => 'चुना हुआ :attribute मान अमान्य है।',
    'in_array' => ':attribute फ़ील्ड :other में मौजूद होना चाहिए।',
    'integer' => ':attribute फ़ील्ड एक पूर्णांक होना चाहिए।',
    'ip' => ':attribute फ़ील्ड एक मान्य IP पता होना चाहिए।',
    'ipv4' => ':attribute फ़ील्ड एक मान्य IPv4 पता होना चाहिए।',
    'ipv6' => ':attribute फ़ील्ड एक मान्य IPv6 पता होना चाहिए।',
    'json' => ':attribute फ़ील्ड एक मान्य JSON स्ट्रिंग होना चाहिए।',
    'list' => ':attribute फ़ील्ड एक सूची होना चाहिए।',
    'lowercase' => ':attribute फ़ील्ड छोटे अक्षरों में होना चाहिए।',
    'lt' => [
        'array' => ':attribute फ़ील्ड में :value से कम आइटम होने चाहिए।',
        'file' => ':attribute फ़ील्ड :value किलोबाइट से छोटा होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड :value से छोटा होना चाहिए।',
        'string' => ':attribute फ़ील्ड :value अक्षरों से छोटा होना चाहिए।',
    ],
    'lte' => [
        'array' => ':attribute फ़ील्ड में :value से अधिक आइटम नहीं होने चाहिए।',
        'file' => ':attribute फ़ील्ड :value किलोबाइट से छोटा या बराबर होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड :value से छोटा या बराबर होना चाहिए।',
        'string' => ':attribute फ़ील्ड :value अक्षरों से छोटा या बराबर होना चाहिए।',
    ],
    'mac_address' => ':attribute फ़ील्ड एक मान्य MAC पता होना चाहिए।',
    'max' => [
        'array' => ':attribute फ़ील्ड में :max से अधिक आइटम नहीं होने चाहिए।',
        'file' => ':attribute फ़ील्ड :max किलोबाइट से बड़ा नहीं होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड :max से बड़ा नहीं होना चाहिए।',
        'string' => ':attribute फ़ील्ड :max अक्षरों से बड़ा नहीं होना चाहिए।',
    ],
    'max_digits' => ':attribute फ़ील्ड में :max से अधिक अंक नहीं होने चाहिए।',
    'mimes' => ':attribute फ़ील्ड एक फ़ाइल होनी चाहिए जिसका प्रकार हो: :values.',
    'mimetypes' => ':attribute फ़ील्ड एक फ़ाइल होनी चाहिए जिसका प्रकार हो: :values.',
    'min' => [
        'array' => ':attribute फ़ील्ड में कम से कम :min आइटम होने चाहिए।',
        'file' => ':attribute फ़ील्ड कम से कम :min किलोबाइट का होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड कम से कम :min होना चाहिए।',
        'string' => ':attribute फ़ील्ड कम से कम :min अक्षरों का होना चाहिए।',
    ],
    'min_digits' => ':attribute फ़ील्ड में कम से कम :min अंक होने चाहिए।',
    'missing' => ':attribute फ़ील्ड गायब होना चाहिए।',
    'missing_if' => ':attribute फ़ील्ड गायब होना चाहिए जब :other :value हो।',
    'missing_unless' => ':attribute फ़ील्ड गायब होना चाहिए जब तक :other :value न हो।',
    'missing_with' => ':attribute फ़ील्ड गायब होना चाहिए जब :values मौजूद हों।',
    'missing_with_all' => ':attribute फ़ील्ड गायब होना चाहिए जब सभी :values मौजूद हों।',
    'multiple_of' => ':attribute फ़ील्ड :value का गुणज होना चाहिए।',
    'not_in' => 'चुना हुआ :attribute मान अमान्य है।',
    'not_regex' => ':attribute फ़ील्ड का फ़ॉर्मेट अमान्य है।',
    'numeric' => ':attribute फ़ील्ड एक संख्या होना चाहिए।',
    'password' => [
        'letters' => ':attribute फ़ील्ड में कम से कम एक अक्षर होना चाहिए।',
        'mixed' => ':attribute फ़ील्ड में कम से कम एक बड़ा और एक छोटा अक्षर होना चाहिए।',
        'numbers' => ':attribute फ़ील्ड में कम से कम एक संख्या होनी चाहिए।',
        'symbols' => ':attribute फ़ील्ड में कम से कम एक प्रतीक होना चाहिए।',
        'uncompromised' => 'चुना हुआ :attribute डेटा लीक में पाया गया है। कृपया कोई अन्य :attribute चुनें।',
    ],
    'present' => ':attribute फ़ील्ड मौजूद होना चाहिए।',
    'present_if' => ':attribute फ़ील्ड मौजूद होना चाहिए जब :other :value हो।',
    'present_unless' => ':attribute फ़ील्ड मौजूद होना चाहिए जब तक :other :value न हो।',
    'present_with' => ':attribute फ़ील्ड मौजूद होना चाहिए जब :values मौजूद हों।',
    'present_with_all' => ':attribute फ़ील्ड मौजूद होना चाहिए जब सभी :values मौजूद हों।',
    'prohibited' => ':attribute फ़ील्ड निषिद्ध है।',
    'prohibited_if' => ':attribute फ़ील्ड निषिद्ध है जब :other :value हो।',
    'prohibited_if_accepted' => ':attribute फ़ील्ड निषिद्ध है जब :other स्वीकार किया जाए।',
    'prohibited_if_declined' => ':attribute फ़ील्ड निषिद्ध है जब :other अस्वीकार किया जाए।',
    'prohibited_unless' => ':attribute फ़ील्ड निषिद्ध है जब तक :other :values में न हो।',
    'prohibits' => ':attribute फ़ील्ड :other को मौजूद होने से रोकता है।',
    'regex' => ':attribute फ़ील्ड का फ़ॉर्मेट अमान्य है।',
    'required' => ':attribute फ़ील्ड आवश्यक है।',
    'required_array_keys' => ':attribute फ़ील्ड में :values के लिए प्रविष्टियाँ होनी चाहिए।',
    'required_if' => ':attribute फ़ील्ड आवश्यक है जब :other :value हो।',
    'required_if_accepted' => ':attribute फ़ील्ड आवश्यक है जब :other स्वीकार किया जाए।',
    'required_if_declined' => ':attribute फ़ील्ड आवश्यक है जब :other अस्वीकार किया जाए।',
    'required_unless' => ':attribute फ़ील्ड आवश्यक है जब तक :other :values में न हो।',
    'required_with' => ':attribute फ़ील्ड आवश्यक है जब :values मौजूद हों।',
    'required_with_all' => ':attribute फ़ील्ड आवश्यक है जब सभी :values मौजूद हों।',
    'required_without' => ':attribute फ़ील्ड आवश्यक है जब :values मौजूद न हों।',
    'required_without_all' => ':attribute फ़ील्ड आवश्यक है जब कोई भी :values मौजूद न हों।',
    'same' => ':attribute फ़ील्ड :other से मेल खाना चाहिए।',
    'size' => [
        'array' => ':attribute फ़ील्ड में :size आइटम होने चाहिए।',
        'file' => ':attribute फ़ील्ड :size किलोबाइट का होना चाहिए।',
        'numeric' => ':attribute फ़ील्ड :size होना चाहिए।',
        'string' => ':attribute फ़ील्ड :size अक्षरों का होना चाहिए।',
    ],
    'starts_with' => ':attribute फ़ील्ड की शुरुआत इनमें से किसी से होनी चाहिए: :values.',
    'string' => ':attribute फ़ील्ड एक स्ट्रिंग होना चाहिए।',
    'timezone' => ':attribute फ़ील्ड एक मान्य टाइमज़ोन होना चाहिए।',
    'unique' => ':attribute मान पहले से लिया जा चुका है।',
    'uploaded' => ':attribute अपलोड करने में विफल।',
    'uppercase' => ':attribute फ़ील्ड बड़े अक्षरों में होना चाहिए।',
    'url' => ':attribute फ़ील्ड एक मान्य URL होना चाहिए।',
    'ulid' => ':attribute फ़ील्ड एक मान्य ULID होना चाहिए।',
    'uuid' => ':attribute फ़ील्ड एक मान्य UUID होना चाहिए।',

    /*
    |--------------------------------------------------------------------------
    | कस्टम सत्यापन भाषा पंक्तियाँ
    |--------------------------------------------------------------------------
    |
    | आप विशेष विशेषताओं के लिए कस्टम सत्यापन संदेश निर्दिष्ट कर सकते हैं
    | "attribute.rule" नामकरण सम्मेलन का उपयोग करके।
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'कस्टम-संदेश',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | कस्टम सत्यापन विशेषताएँ
    |--------------------------------------------------------------------------
    |
    | निम्न पंक्तियाँ विशेषता प्लेसहोल्डर को किसी और अधिक पठनीय चीज़ से
    | बदलने के लिए उपयोग की जाती हैं जैसे "ईमेल" की बजाय "email"।
    |
    */

    'attributes' => [],

];
