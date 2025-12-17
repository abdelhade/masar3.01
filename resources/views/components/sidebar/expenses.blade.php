{{-- لوحة المصروفات --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('expenses.dashboard') ? 'active' : '' }}" href="{{ route('expenses.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>لوحة المصروفات</span>
    </a>
</li>

{{-- تسجيل مصروف جديد --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('expenses.create') ? 'active' : '' }}" href="{{ route('expenses.create') }}">
        <i class="fas fa-plus-circle"></i>
        <span>تسجيل مصروف جديد</span>
    </a>
</li>

<li class="nav-divider"></li>

{{-- تقارير المصروفات --}}
<li class="nav-item has-submenu">
    <a class="nav-link" href="javascript: void(0);">
        <i class="fas fa-file-invoice-dollar"></i>
        تقارير المصروفات
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.general-expenses-report') ? 'active' : '' }}" 
               href="{{ route('reports.general-expenses-report') }}">
                <i class="ti-control-record"></i>تقرير المصروفات العام
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.general-expenses-daily-report') ? 'active' : '' }}" 
               href="{{ route('reports.general-expenses-daily-report') }}">
                <i class="ti-control-record"></i>كشف حساب مصروف
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.expenses-balance-report') ? 'active' : '' }}" 
               href="{{ route('reports.expenses-balance-report') }}">
                <i class="ti-control-record"></i>ميزان المصروفات
            </a>
        </li>
    </ul>
</li>

{{-- مراكز التكلفة --}}
<li class="nav-item has-submenu">
    <a class="nav-link" href="javascript: void(0);">
        <i class="fas fa-sitemap"></i>
        مراكز التكلفة
        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
    </a>
    <ul class="sub-menu mm-collapse">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.general-cost-centers-report') ? 'active' : '' }}" 
               href="{{ route('reports.general-cost-centers-report') }}">
                <i class="ti-control-record"></i>تقرير مراكز التكلفة
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.general-cost-center-account-statement') ? 'active' : '' }}" 
               href="{{ route('reports.general-cost-center-account-statement') }}">
                <i class="ti-control-record"></i>كشف حساب مركز التكلفة
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.general-cost-centers-list') ? 'active' : '' }}" 
               href="{{ route('reports.general-cost-centers-list') }}">
                <i class="ti-control-record"></i>قائمة مراكز التكلفة
            </a>
        </li>
    </ul>
</li>

<li class="nav-divider"></li>

{{-- العودة للتقارير --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('reports.overall') }}">
        <i class="fas fa-arrow-right"></i>
        <span>العودة للتقارير</span>
    </a>
</li>

{{-- العودة للوحة الرئيسية --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="fas fa-home"></i>
        <span>الصفحة الرئيسية</span>
    </a>
</li>

