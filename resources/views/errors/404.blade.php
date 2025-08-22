<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">
    <title>{{ __('errors.page_not_found_title') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding: 50px;
            color: #343a40;
        }

        h1 {
            font-size: 4rem;
            color: #ff6b6b;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .animation {
            width: 200px;
            height: 200px;
            margin: 0 auto 40px;
            background-image: url("{{ asset('assets/images/404.gif') }}");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4ecdc4;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .btn:hover {
            background-color: #3aa79e;
            transform: scale(1.05);
        }

        .dialect {
            font-size: 1.8rem;
            color: #6c757d;
            font-style: italic;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="animation"></div>
    <h1>404</h1>
    <p>{{ __('errors.page_not_found_message') }}</p>

    <div class="dialect">
        "{{ __('errors.page_not_found_dialect') }}"
    </div>

    <a href="/" class="btn">{{ __('errors.go_home') }}</a>
</body>

</html>
