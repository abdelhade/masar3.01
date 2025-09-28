{{-- @canany([
    'عرض العملاء',
    'عرض الموردين',
    'عرض الصناديق',
    'عرض البنوك',
    'عرض الموظفين',
    'عرض المخازن',
    'عرض
    المصروفات',
    'عرض الايرادات',
    'عرض دائنين متنوعين',
    'عرض مدينين متنوعين',
    'عرض الشركاء',
    'عرض جارى الشركاء',
    'عرض الأصول الثابتة',
    'عرض الأصول القابلة للتأجير',
]) --}}
{{-- <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="database" style="color:#4e73df" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.master_data') }}</span>
            <span class="menu-arrow">
                <i class="mdi mdi-chevron-right"></i>
            </span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
@can('عرض العملاء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'client']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.clients') }}
        </a>
    </li>
@endcan
@can('عرض الموردين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'supplier']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.suppliers') }}
        </a>
    </li>
@endcan
@can('عرض الصناديق')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'fund']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.funds') }}
        </a>
    </li>
@endcan
@can('عرض البنوك')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'bank']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.banks') }}
        </a>
    </li>
@endcan
@can('عرض الموظفين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'employee']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.employees') }}
        </a>
    </li>
@endcan
@can('عرض المخازن')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'store']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.warehouses') }}
        </a>
    </li>
@endcan
@can('عرض المصروفات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'expense']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.expenses') }}
        </a>
    </li>
@endcan
@can('عرض الايرادات')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'revenue']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.revenues') }}
        </a>
    </li>
@endcan
@can('عرض دائنين متنوعين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'creditor']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.various_creditors') }}
        </a>
    </li>
@endcan
@can('عرض مدينين متنوعين')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'depitor']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.various_debtors') }}
        </a>
    </li>
@endcan
@can('عرض الشركاء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'partner']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.partners') }}
        </a>
    </li>
@endcan
@can('عرض جارى الشركاء')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'current-partner']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.current_partners') }}
        </a>
    </li>
@endcan
@can('عرض الأصول الثابتة')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'asset']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.fixed_assets') }}
        </a>
    </li>
@endcan
@can('عرض الأصول القابلة للتأجير')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('accounts.index', ['type' => 'rentable']) }}">
            <i class="ti-control-record"></i>{{ __('navigation.rentable_assets') }}
        </a>
    </li>
@endcan
{{-- </ul>
    </li>
@endcanany --}}
