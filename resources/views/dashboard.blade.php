<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
@include('admin.partials.head')

<body>
    {{-- Apply saved theme immediately to avoid flash --}}
    <script>
    (function(){var k='masar_theme';var v;try{v=localStorage.getItem(k);}catch(e){v=null;}
    var t=(v&&['classic','mint-green','dark','monokai'].indexOf(v)!==-1)?v:'classic';
    document.body.classList.add('theme-'+t);
    })();
    </script>
    @include('admin.partials.sidebar')

    <div class="page-wrapper">
        @include('admin.partials.topbar')
        <div class="page-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
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
