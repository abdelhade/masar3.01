<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('admin.partials.head')

<body class="@yield('body_class')">
    {{-- Apply saved theme immediately to avoid flash --}}
    <script>
    (function(){var k='masar_theme';var v;try{v=localStorage.getItem(k);}catch(e){v=null;}
    var t=(v&&['classic','mint-green','dark','monokai'].indexOf(v)!==-1)?v:'classic';
    document.body.classList.add('theme-'+t);
    })();
    </script>
    {{-- YouTube-style Progress Bar Loader --}}
    <div id="page-loader" class="page-loader">
        <div class="loader-bar"></div>
    </div>

    @hasSection('sidebar')

        <div class="left-sidenav">
            <div class="menu-content h-100" data-simplebar>
                <ul class="metismenu left-sidenav-menu">
         
                    <li class="nav-item border-bottom pb-2 mb-2 text-center">
                        <a href="{{ route('home') }}" class="fw-bold text-primary font-16">
                            {{ config('public_settings.campany_name') }}
                        </a>
                    </li>

                    <li class="nav-item border-bottom pb-1 mb-2">
                        <a href="{{ route('admin.dashboard') }}"
                            class="nav-link d-flex align-items-center gap-2 transition-base {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            style="{{ request()->routeIs('admin.dashboard') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3; font-weight: 600;' : 'color: var(--color-text-secondary);' }}">
                            <i data-feather="home" style="color: {{ request()->routeIs('admin.dashboard') ? '#34d3a3' : '#6b7280' }}" class="menu-icon"></i>
                            {{ __('navigation.home') }}
                        </a>
                    </li>


                    {{-- Sidebar Content: يتم تعريفه في كل صفحة --}}
                    @yield('sidebar')
                </ul>
            </div>
        </div>
    @else
        {{-- Default Sidebar: للصفحات القديمة اللي ما عندهاش dynamic sidebar --}}
        @include('admin.partials.sidebar-default')
    @endif

    <div class="page-wrapper">
        @hasSection('hide_topbar')@else
        @include('admin.partials.topbar')
        @endif
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    @include('sweetalert::alert')
                    @yield('content')
                </div>
            </div>
            @hasSection('hide_footer') @else
            @include('admin.partials.footer')
            @endif
        </div>
    </div>
    @include('admin.partials.scripts')
    @yield('script')
</body>

</html>
