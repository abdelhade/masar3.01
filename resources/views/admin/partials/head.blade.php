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
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/bootstrap2025.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/metisMenu.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('assets/js/jq.js') }}"></script>
    <link href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app-rtl.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/cake.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom-overrides.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .table{
            border: 1px solid rgb(114, 114, 255);
            border-radius: 0px;
            background-color: #fff;
            color: black;
            overflow: scroll;
          
        }
        .table-responsive {
            width: 100%;
        }

        th,
        td {

            border: 4px solid black;
        }

        .nowrap {
            white-space: nowrap;
        }

        .container {
            background-color: #FFFDF6
        }

        .form-control:focus {
            background-color: #e3f68e;
            border: 1px solid rgb(255, 255, 255);
            transition: background-color 0.5s ease-in-out, border 2s ease-in-out;
        }




        .form-control {
            border: 1px solid rgb(114, 114, 255);
            padding: 10px;
            margin: 0px;
            font-size: 19px;
        }

        #loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            /* خلفية شبه شفافة */
            backdrop-filter: blur(5px);
            /* التأثير */
            z-index: 9999;
            /* يظهر فوق كل شيء */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .spinner-border-custom {
            width: 20rem;
            height: 20rem;
            border-width: 0.1rem;
        }

        body {
            background-color: rgb(233, 241, 255)
        }

        h1 {
            font-size: 40px;
        }
        h2 {
            font-size: 30px;
        }


        .li-main {
            border: 1px solid rgb(201, 201, 255);
            border-radius: 10px;
        }

        .sub-menu {
            border: 1px solid rgb(255, 244, 244);
            border-radius: px;
        }

        .journal_tr {
            border: 2px solid rgb(114, 114, 255);

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
        
        /* LTR Support */
        [dir="ltr"] .text-start {
            text-align: left !important;
        }
        
        [dir="ltr"] .text-end {
            text-align: right !important;
        }
    </style>

    @vite(['resources/js/app.js'])
    
    <!-- User ID for Location Tracking -->
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    
    <!-- Livewire Styles -->
    @livewireStyles

    @stack('styles')
</head>
@include('components.idintity.loader')
