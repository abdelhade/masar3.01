@can('view Resources Dashboard')
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('resources.dashboard') }}">
        <i class="ti-control-record"></i>لوحة معلومات الموارد
    </a>
</li>
@endcan

@can('view Resources')
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('resources.index') }}">
        <i class="ti-control-record"></i>إدارة الموارد
    </a>
</li>
@endcan

@can('view Resource Assignments')
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('resources.assignments.index') }}">
        <i class="ti-control-record"></i>تعيينات الموارد
    </a>
</li>
@endcan

@can('view Resource Categories')
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('resources.categories.index') }}">
        <i class="ti-control-record"></i>التصنيفات
    </a>
</li>
@endcan

@can('view Resource Types')
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('resources.types.index') }}">
        <i class="ti-control-record"></i>الأنواع
    </a>
</li>
@endcan

@can('view Resource Statuses')
<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('resources.statuses.index') }}">
        <i class="ti-control-record"></i>الحالات
    </a>
</li>
@endcan

