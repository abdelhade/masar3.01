<!DOCTYPE html>
<html lang="en">
@include('admin.partials.head')

<body class="page-wrapper">
    <div class="">
        @include('admin.partials.topbar2')
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
