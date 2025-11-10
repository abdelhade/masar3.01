{{-- لوحة تحكم الجودة --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>لوحة تحكم الجودة</span>
    </a>
</li>

{{-- فحوصات الجودة --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#quality-inspections" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="quality-inspections">
        <i class="fas fa-clipboard-check"></i>
        <span>فحوصات الجودة</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="quality-inspections">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('quality.inspections.index') }}">
                    <i class="ti-control-record"></i>جميع الفحوصات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('quality.inspections.create') }}">
                    <i class="ti-control-record"></i>فحص جديد
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- معايير الجودة --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.standards.index') }}">
        <i class="fas fa-ruler-combined"></i>
        <span>معايير الجودة</span>
    </a>
</li>

{{-- تقارير عدم المطابقة (NCR) --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#quality-ncr" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="quality-ncr">
        <i class="fas fa-exclamation-triangle"></i>
        <span>عدم المطابقة (NCR)</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="quality-ncr">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('quality.ncr.index') }}">
                    <i class="ti-control-record"></i>جميع التقارير
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('quality.ncr.create') }}">
                    <i class="ti-control-record"></i>تقرير جديد
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- إجراءات تصحيحية (CAPA) --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.capa.index') }}">
        <i class="fas fa-tools"></i>
        <span>إجراءات تصحيحية (CAPA)</span>
    </a>
</li>

{{-- تتبع الدفعات --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.batches.index') }}">
        <i class="fas fa-barcode"></i>
        <span>تتبع الدفعات</span>
    </a>
</li>

{{-- تقييم الموردين --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.suppliers.index') }}">
        <i class="fas fa-star"></i>
        <span>تقييم الموردين</span>
    </a>
</li>

{{-- الشهادات --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.certificates.index') }}">
        <i class="fas fa-certificate"></i>
        <span>الشهادات والامتثال</span>
    </a>
</li>

{{-- التدقيق الداخلي --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.audits.index') }}">
        <i class="fas fa-search"></i>
        <span>التدقيق الداخلي</span>
    </a>
</li>

{{-- التقارير --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('quality.reports') }}">
        <i class="fas fa-chart-line"></i>
        <span>تقارير الجودة</span>
    </a>
</li>

