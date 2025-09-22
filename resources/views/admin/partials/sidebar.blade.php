<!-- Left Sidenav -->
<div class="left-sidenav">
    <div class="menu-content h-100" data-simplebar>
        <ul class="metismenu left-sidenav-menu">

            <li class="menu-label my-2"><a href="{{ route('home') }}">{{ config('public_settings.campany_name') }}</a>
            </li>

            <li class="nav-item border-bottom pb-1 mb-2">
                <a href="{{ route('home.index') }}"
                    class="nav-link d-flex align-items-center gap-2 font-family-cairo fw-bold">
                    <i data-feather="home" style="color:#4e73df" class="menu-icon"></i>
                    {{ __('navigation.home') }}
                </a>

            </li>

            @include('components.sidebar.accounts')

            @include('components.sidebar.items')

            @include('components.sidebar.discounts')

            @include('components.sidebar.manufacturing')

            @include('components.sidebar.permissions')

            @include('components.sidebar.crm')

            @include('components.sidebar.invoices')

            @include('components.sidebar.vouchers')

            @include('components.sidebar.transfers')

            @include('components.sidebar.multi-vouchers')

            @include('components.sidebar.journals')

            @include('components.sidebar.projects')

            @include('components.sidebar.departments')

            @include('components.sidebar.settings')

            @include('components.sidebar.rentals')

            @include('components.sidebar.service')

            @include('components.sidebar.shipping')

            @include('components.sidebar.POS')

            @include('components.sidebar.daily_progress')

            @include('components.sidebar.inquiries')

        </ul>
    </div>
</div>
