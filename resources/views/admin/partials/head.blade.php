<head>
    <meta charset="utf-8" />
    <title>MASAR مـسار</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <script src="{{ asset('assets/js/lucide.js') }}"></script>

    <!-- Google Fonts - IBM Plex Sans Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}">
    
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- <link href="{{ asset('assets/css/bootstrap2025.css') }}" rel="stylesheet" type="text/css" /> -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/metisMenu.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- <script src="{{ asset('assets/js/jq.js') }}"></script> -->
    <link href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app-rtl.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/cake.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/custom-buttons.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    {{-- Masar theme switcher (classic, mint-green, dark, monokai) --}}
    <link href="{{ asset('css/themes/masar-themes.css') }}" rel="stylesheet" type="text/css" />
    
    {{-- Early theme initialization to prevent flash --}}
    <script>
        (function() {
            var STORAGE_KEY = 'masar_theme';
            var VALID_THEMES = ['classic', 'mint-green', 'dark', 'monokai'];
            var DEFAULT_THEME = 'classic';
            
            try {
                var stored = localStorage.getItem(STORAGE_KEY);
                var theme = (stored && VALID_THEMES.indexOf(stored) !== -1) ? stored : DEFAULT_THEME;
                document.documentElement.className = 'theme-' + theme;
                if (document.body) {
                    document.body.className = 'theme-' + theme;
                }
            } catch (e) {
                document.documentElement.className = 'theme-' + DEFAULT_THEME;
            }
        })();
    </script>

    <!-- Alpine.js is included with Livewire 3, no need to load separately -->
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.0/dist/cdn.min.js"></script> --}}

    <!-- Vite CSS only - JS is loaded in scripts.blade.php after Livewire/Alpine -->
    @vite(['resources/css/app.css'])
    {{-- <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" /> --}}

    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    

    @livewireStyles

    {{-- YouTube-style Progress Bar Loader Styles --}}
    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            z-index: 99999;
            background: transparent;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .page-loader.active {
            opacity: 1;
        }

        .loader-bar {
            height: 100%;
            background: linear-gradient(90deg, #34d3a3 0%, #28a745 50%, #34d3a3 100%);
            background-size: 200% 100%;
            width: 0%;
            transition: width 0.3s ease;
            box-shadow: 0 0 10px rgba(52, 211, 163, 0.5);
            animation: shimmer 1.5s infinite;
        }

        .page-loader.active .loader-bar {
            width: 30%;
            animation: loading 2s ease-in-out infinite;
        }

        .page-loader.completing .loader-bar {
            width: 100%;
            transition: width 0.4s ease;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        @keyframes loading {
            0% {
                width: 0%;
            }
            50% {
                width: 70%;
            }
            100% {
                width: 90%;
            }
        }
    </style>

    @stack('styles')
    
    {{-- Custom Form Control Focus Styles --}}
    <style>
        .form-control:focus,
        select.form-control:focus,
        textarea.form-control:focus,
        .custom-select:focus,
        input.form-control:focus {
            border-color: #00ff22ff !important;
            background-color: #bbff00ff !important;
            font-weight: bold !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            outline: 0;
        }
        .form-control{
            border-color: #000000ff !important;
            background-color:rgb(253, 253, 251) !important;
            font-weight: bold !important;
            box-shadow: 0 0 0 0.2rem rgba(79, 250, 36, 0.25) !important;
            outline: 0; 
        }
        td{
          padding: 0px !important;
          margin: 0px !important;  
        }
    </style>
</head>
@include('components.idintity.loader')
