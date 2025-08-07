<?php

return [
    // البيئة: sandbox للتجريب، production للإنتاج
    'mode' => env('ZATCA_MODE', 'sandbox'),

    // روابط API
    'sandbox_url' => 'https://sandbox.zatca.gov.sa/IntegrationSandbox',
    'production_url' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core',

    // بيانات الشركة (يجب تعديلها)
    'company' => [
        'name' => env('ZATCA_COMPANY_NAME', 'اسم شركتك'),
        'vat_number' => env('ZATCA_VAT_NUMBER', '300000000000003'),
        'address' => env('ZATCA_COMPANY_ADDRESS', 'عنوان الشركة'),
        'city' => env('ZATCA_COMPANY_CITY', 'الرياض'),
        'country' => 'SA',
        'building_number' => env('ZATCA_BUILDING_NUMBER', '1234'),
        'postal_code' => env('ZATCA_POSTAL_CODE', '12345'),
        'additional_number' => env('ZATCA_ADDITIONAL_NUMBER', '1234'),
    ],

    // مسارات الشهادات
    'certificate_path' => storage_path('certificates/zatca.crt'),
    'private_key_path' => storage_path('certificates/zatca.key'),
    'certificate_password' => env('ZATCA_CERTIFICATE_PASSWORD', ''),
];
