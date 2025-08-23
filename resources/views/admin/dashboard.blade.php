<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('admin.partials.head')

<body class="">
    @include('admin.partials.sidebar')
    <div class="page-wrapper">
        @include('admin.partials.topbar')
        <div class="page-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        @include('sweetalert::alert')
                        @yield('content')
                    </div>
                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
    @include('admin.partials.scripts')
</body>

</html>
