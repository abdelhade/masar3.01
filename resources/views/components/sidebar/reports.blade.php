{{-- التقارير العامة --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#reports-general" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="reports-general">
        <i class="fas fa-chart-pie"></i>
        <span>التقارير العامة</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="reports-general">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.overall') }}">
                    <i class="ti-control-record"></i>محلل العمل اليومي
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('journal-summery') }}">
                    <i class="ti-control-record"></i>اليومية العامة
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-journal-details') }}">
                    <i class="ti-control-record"></i>كشف حساب عام
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- تقارير الحسابات --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#reports-accounts" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="reports-accounts">
        <i class="fas fa-book"></i>
        <span>تقارير الحسابات</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="reports-accounts">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('accounts.tree') }}">
                    <i class="ti-control-record"></i>شجرة الحسابات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-balance-sheet') }}">
                    <i class="ti-control-record"></i>الميزانية العمومية
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-profit-loss-report') }}">
                    <i class="ti-control-record"></i>أرباح وخسائر
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-account-balances') }}">
                    <i class="ti-control-record"></i>ميزان الحسابات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('account-movement') }}">
                    <i class="ti-control-record"></i>حركة حساب
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- تقارير المخزون والأصناف --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#reports-inventory" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="reports-inventory">
        <i class="fas fa-boxes"></i>
        <span>تقارير المخزون</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="reports-inventory">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('items.index') }}">
                    <i class="ti-control-record"></i>قائمة الأصناف مع الأرصدة
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('item-movement') }}">
                    <i class="ti-control-record"></i>حركة الصنف
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.get-items-max-min-quantity') }}">
                    <i class="ti-control-record"></i>حد الطلب الأدنى والأقصى
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.items.inactive') }}">
                    <i class="ti-control-record"></i>الأصناف الموقوفة
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.items.with-stores') }}">
                    <i class="ti-control-record"></i>الأصناف لكل مستودع
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.inventory-discrepancy-report') }}">
                    <i class="ti-control-record"></i>مراقبة جرد الأصناف
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- تقارير المبيعات --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#reports-sales" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="reports-sales">
        <i class="fas fa-shopping-cart"></i>
        <span>تقارير المبيعات</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="reports-sales">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.sales.representative') }}">
                    <i class="ti-control-record"></i>المبيعات حسب المندوب
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.sales.daily') }}">
                    <i class="ti-control-record"></i>المبيعات اليومية
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-sales-total-report') }}">
                    <i class="ti-control-record"></i>المبيعات إجماليات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.sales.items') }}">
                    <i class="ti-control-record"></i>المبيعات أصناف
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('sales.invoice-report') }}">
                    <i class="ti-control-record"></i>فواتير المبيعات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('sales-orders-report') }}">
                    <i class="ti-control-record"></i>أوامر البيع
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase-quotations-reports') }}">
                    <i class="ti-control-record"></i>عروض الأسعار للعملاء
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-sales-report-by-address') }}">
                    <i class="ti-control-record"></i>المبيعات بالعنوان
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- تقارير المشتريات --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#reports-purchases" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="reports-purchases">
        <i class="fas fa-shopping-basket"></i>
        <span>تقارير المشتريات</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="reports-purchases">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-purchases-daily-report') }}">
                    <i class="ti-control-record"></i>المشتريات اليومية
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-purchases-total') }}">
                    <i class="ti-control-record"></i>المشتريات إجماليات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-purchases-items-report') }}">
                    <i class="ti-control-record"></i>المشتريات أصناف
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('billing.invoice-report') }}">
                    <i class="ti-control-record"></i>فواتير المشتريات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('supplier-rfqs-report') }}">
                    <i class="ti-control-record"></i>عروض الأسعار من الموردين
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- تقارير المصروفات --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#reports-expenses" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="reports-expenses">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>تقارير المصروفات</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="reports-expenses">
        <ul class="nav flex-column sub-menu">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.expenses-balance-report') }}">
                    <i class="ti-control-record"></i>ميزان المصروفات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-expenses-report') }}">
                    <i class="ti-control-record"></i>تقرير المصروفات العام
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-expenses-daily-report') }}">
                    <i class="ti-control-record"></i>كشف حساب مصروف
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cost-centers-report') }}">
                    <i class="ti-control-record"></i>تقرير مراكز التكلفة
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- تقارير النقدية والبنوك --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#reports-cash" data-bs-toggle="collapse" role="button" aria-expanded="false"
        aria-controls="reports-cash">
        <i class="fas fa-money-bill-wave"></i>
        <span>النقدية والبنوك</span>
        <i class="ti-angle-down"></i>
    </a>
    <div class="collapse" id="reports-cash">
        <ul class="nav flex-column sub-menu">
            {{-- <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cash-bank-report') }}">
                    <i class="ti-control-record"></i>تقرير النقدية والبنوك
                </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports.general-cashbox-movement-report') }}">
                    <i class="ti-control-record"></i>حركة الصندوق
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- تقرير التصنيع --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('manufacturing.invoice.report') }}">
        <i class="fas fa-industry"></i>
        <span>تقارير التصنيع</span>
    </a>
</li>

