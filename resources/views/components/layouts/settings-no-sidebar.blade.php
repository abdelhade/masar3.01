{{-- resources/views/components/layouts/settings-no-sidebar.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? __('Settings') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Cairo&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        
        /* RTL Support */
        [dir="rtl"] .rtl-flip {
            transform: scaleX(-1);
        }
        
        [dir="rtl"] .text-start {
            text-align: right !important;
        }
        
        [dir="rtl"] .text-end {
            text-align: left !important;
        }
        
        [dir="rtl"] .ms-auto {
            margin-left: 0 !important;
            margin-right: auto !important;
        }
        
        [dir="rtl"] .me-auto {
            margin-right: 0 !important;
            margin-left: auto !important;
        }
        
        [dir="rtl"] .ps-3 {
            padding-left: 0 !important;
            padding-right: 1rem !important;
        }
        
        [dir="rtl"] .pe-3 {
            padding-right: 0 !important;
            padding-left: 1rem !important;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        {{ $slot }}
    </div>
    
    @livewireScripts
</body>
</html>