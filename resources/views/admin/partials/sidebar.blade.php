<!-- Left Sidenav -->
<div class="left-sidenav">
    <style>
        /* .menu-content a {
            color: black !important;
            background-color: rgb(250, 250, 250) !important;
            border: rgb(143, 143, 143) solid 1px;
        }

        .menu-content li {
            margin: 1px !important;
        }

        .menu-content a:hover {
            color: white !important;
            background-color:rgb(97, 139, 255) !important;
            padding: 3px !important;

        } */

        /* .menu-content i{
            font-size:40px !important;
        } */
    </style>
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
            @php
                $sidebarType = request()->get('sidebar', 'all');
            @endphp

            @if ($sidebarType == 'all' || $sidebarType == 'accounts')
                @include('components.sidebar.accounts')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'items')
                @include('components.sidebar.items')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'discounts')
                @include('components.sidebar.discounts')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'manufacturing')
                @include('components.sidebar.manufacturing')
            @endif
            @if ($sidebarType == 'all' || $sidebarType == 'permissions')
                @include('components.sidebar.permissions')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'crm')
                @include('components.sidebar.crm')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'sales-invoices')
                @include('components.sidebar.sales-invoices')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'purshase-invoices')
                @include('components.sidebar.invoices')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'invoices')
                @include('components.sidebar.invoices')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'vouchers')
                @include('components.sidebar.vouchers')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'transfers')
                @include('components.sidebar.transfers')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'multi-vouchers')
                @include('components.sidebar.merit-vouchers')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'contract-journals')
                @include('components.sidebar.contract-journals')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'depreciation-journals')
                @include('components.sidebar.multi-vouchers')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'basic_journal-journals')
                @include('components.sidebar.journals')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'projects')
                @include('components.sidebar.projects')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'departments')
                @include('components.sidebar.departments')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'settings')
                @include('components.sidebar.settings')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'rentals')
                @include('components.sidebar.rentals')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'service')
                @include('components.sidebar.service')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'shipping')
                @include('components.sidebar.shipping')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'POS')
                @include('components.sidebar.POS')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'daily_progress')
                @include('components.sidebar.daily_progress')
            @endif

            @if ($sidebarType == 'all' || $sidebarType == 'inquiries')
                @include('components.sidebar.inquiries')
            @endif

        </ul>
    </div>
</div>
