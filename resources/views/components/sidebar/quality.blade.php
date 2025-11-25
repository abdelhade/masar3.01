{{-- لوحة تحكم الجودة --}}
@can('view quality')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>لوحة تحكم الجودة</span>
        </a>
    </li>
@endcan

{{-- فحوصات الجودة --}}
@canany(['view inspections', 'create inspections'])
    <li class="nav-item">
        <a class="nav-link collapsed" href="#quality-inspections" data-bs-toggle="collapse" role="button" aria-expanded="false"
            aria-controls="quality-inspections">
            <i class="fas fa-clipboard-check"></i>
            <span>فحوصات الجودة</span>
            <i class="ti-angle-down"></i>
        </a>
        <div class="collapse" id="quality-inspections">
            <ul class="nav flex-column sub-menu">
                @can('view inspections')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.inspections.index') }}">
                            <i class="ti-control-record"></i>جميع الفحوصات
                        </a>
                    </li>
                @endcan
                @can('create inspections')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.inspections.create') }}">
                            <i class="ti-control-record"></i>فحص جديد
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany

{{-- معايير الجودة --}}
@can('view standards')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.standards.index') }}">
            <i class="fas fa-ruler-combined"></i>
            <span>معايير الجودة</span>
        </a>
    </li>
@endcan


{{-- تقارير عدم المطابقة (NCR) --}}
@canany(['view ncr', 'create ncr'])
    <li class="nav-item">
        <a class="nav-link collapsed" href="#quality-ncr" data-bs-toggle="collapse" role="button" aria-expanded="false"
            aria-controls="quality-ncr">
            <i class="fas fa-exclamation-triangle"></i>
            <span>عدم المطابقة (NCR)</span>
            <i class="ti-angle-down"></i>
        </a>
        <div class="collapse" id="quality-ncr">
            <ul class="nav flex-column sub-menu">
                @can('view ncr')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.ncr.index') }}">
                            <i class="ti-control-record"></i>جميع التقارير
                        </a>
                    </li>
                @endcan
                @can('create ncr')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.ncr.create') }}">
                            <i class="ti-control-record"></i>تقرير جديد
                        </a>
                    </li>
                @endcan
                </ul>
            </div>
        </li>
    @endcanany
    {{-- إجراءات تصحيحية (CAPA) --}}
    @can('view capa')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('quality.capa.index') }}">
                <i class="fas fa-tools"></i>
                <span>إجراءات تصحيحية (CAPA)</span>
            </a>
        </li>
    @endcan

    {{-- تتبع الدفعات --}}
    @can('view batches')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('quality.batches.index') }}">
                <i class="fas fa-barcode"></i>
                <span>تتبع الدفعات</span>
            </a>
        </li>
    @endcan


    {{-- تقييم الموردين --}}
    @can('view rateSuppliers')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('quality.suppliers.index') }}">
                <i class="fas fa-star"></i>
                <span>تقييم الموردين</span>
            </a>
        </li>
    @endcan


    {{-- الشهادات --}}
    @can('view certificates')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('quality.certificates.index') }}">
                <i class="fas fa-certificate"></i>
                <span>الشهادات والامتثال</span>
            </a>
        </li>
    @endcan


    {{-- التدقيق الداخلي --}}
    @can('view audits')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('quality.audits.index') }}">
                <i class="fas fa-search"></i>
                <span>التدقيق الداخلي</span>
            </a>
        </li>
    @endcan


    {{-- التقارير
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.reports') }}">
        <i class="fas fa-chart-line"></i>
        <span>تقارير الجودة</span>
    </a>
</li> --}}
