<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('admin.partials.head')

<body>
    {{-- YouTube-style Progress Bar Loader --}}
    <div id="page-loader" class="page-loader">
        <div class="loader-bar"></div>
    </div>
    {{-- Dynamic Sidebar: كل صفحة تحدد الـ sidebar الخاص بها --}}
    @hasSection('sidebar')
        {{-- Sidebar Wrapper: يحتوي الـ structure الثابت --}}
        <div class="left-sidenav">
            <div class="menu-content h-100" data-simplebar>
                <ul class="metismenu left-sidenav-menu">
         
                    <li class="nav-item border-bottom pb-1 mb-2">
                        <a href="{{ route('admin.dashboard') }}"
                            class="nav-link d-flex align-items-center gap-2 transition-base {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            style="{{ request()->routeIs('admin.dashboard') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3; font-weight: 600;' : 'color: var(--color-text-secondary);' }}">
                            <i data-feather="home" style="color: {{ request()->routeIs('admin.dashboard') ? '#34d3a3' : '#6b7280' }}" class="menu-icon"></i>
                            {{ __('navigation.home') }}
                        </a>
                    </li>

                    <li class="nav-item border-bottom pb-1 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}"
                                class="btn btn-link p-0 d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; color: #6b7280; text-decoration: none;"
                                title="العودة للرئيسية">
                                <i data-feather="arrow-right" style="width: 18px; height: 18px;"></i>
                            </a>
                            <a href="{{ route('pos.index') }}"
                                class="nav-link flex-grow-1 d-flex align-items-center gap-2 transition-base {{ request()->routeIs('pos.index') ? 'active' : '' }}"
                                style="{{ request()->routeIs('pos.index') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3; font-weight: 600;' : 'color: var(--color-text-secondary);' }}">
                                <i data-feather="shopping-cart" style="color: {{ request()->routeIs('pos.index') ? '#34d3a3' : '#6b7280' }}" class="menu-icon"></i>
                                {{ __('إدارة نقاط البيع') }}
                            </a>
                        </div>
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
        @include('admin.partials.topbar')
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    @include('sweetalert::alert')
                    @yield('content')
                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
    @include('admin.partials.scripts')
    @yield('script')
</body>

</html>
