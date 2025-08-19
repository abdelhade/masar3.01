@extends('admin.dashboard')

@section('content')
@php
    $permissionTypes = [
        'client' => 'العملاء',
        'supplier' => 'الموردين',
        'fund' => 'الصناديق',
        'bank' => 'البنوك',
        'employee' => 'الموظفين',
        'store' => 'المخازن',
        'expense' => 'المصروفات',
        'revenue' => 'الإيرادات',
        'creditor' => 'دائنين متنوعين',
        'depitor' => 'مدينين متنوعين',
        'partner' => 'الشركاء',
        'current-partner' => 'جارى الشركاء',
        'asset' => 'الأصول الثابتة',
        'rentable' => 'الأصول القابلة للتأجير',
    ];

    $type = request('type');
    $permName = $permissionTypes[$type] ?? null;
@endphp

<div class="container">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <h3 class="cake cake-bounce">قائمة الحسابات
                        @isset($parentAccountName)
                            : {{ $parentAccountName }}
                        @else
                            @php
                                $typeLabels = [
                                    'client' => 'العملاء',
                                    'supplier' => 'الموردين',
                                    'fund' => 'الصناديق',
                                    'bank' => 'البنوك',
                                    'expense' => 'المصروفات',
                                    'revenue' => 'الإيرادات',
                                    'creditor' => 'الدائنين',
                                    'depitor' => 'المدينين',
                                    'partner' => 'الشركاء',
                                    'current-partner' => 'جاري الشركاء',
                                    'asset' => 'الأصول',
                                    'employee' => 'الموظفين',
                                    'rentable' => 'المستأجرات',
                                    'store' => 'المخازن',
                                ];
                            @endphp

                            @if (request('type') && isset($typeLabels[request('type')]))
                                _ {{ $typeLabels[request('type')] }}
                            @endif
                        @endisset
                    </h3>
                </div>



            </div>
            @php
                $parentCodes = [
                    'client' => '1103',   // العملاء
                    'supplier' => '2101',   // الموردين
                    'bank' => '1102',   // البنوك
                    'fund' => '1101',   // الصناديق
                    'store' => '1104',   // المخازن
                    'expense' => '57',      // المصروفات
                    'revenue' => '42',      // الإيرادات
                    'creditor' => '2104',   // دائنين اخرين
                    'depitor' => '1106',   // مدينين آخرين
                    'partner' => '31',   // الشريك الرئيسي
                    'current-partner' => '3201',   // جاري الشريك
                    'asset' => '12',      // الأصول
                    'employee' => '2102',   // الموظفين
                    'rentable' => '1202',   // مباني (أصل قابل للإيجار)
                ];

                $type = request()->get('type');
                $parentCode = $parentCodes[$type] ?? null;
            @endphp

            <div class="col-md-3">
                @if ($parentCode)
                    @can("إضافة $permName")
                        <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}"
                            class="btn btn-primary cake cake-fadeIn">
                            {{ __('إضافة حساب جديد') }}
                        </a>
                    @endcan
                @endif
            </div>
        </div>

        <div class="row mt-3 justify-content-between align-items-center">
            <div class="col-lg-3">

            </div>

            <div class="col-md-4 text-end">
                <input class="form-control form-control-lg frst " type="text" id="itmsearch"
                    placeholder="بحث بالكود | اسم الحساب | ID">
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3 cake cake-zoomIn">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3 cake cake-zoomIn">
                {{ session('error') }}
            </div>
        @endif

        <div class="card-body px-0 mt-4">
            <div class="card border-0 rounded-0">
                <div class="card-header border-0">
                <button class="printbtn" data-target="printed">طباعة</button>

                    <div class="table-responsive" id="printed" style="overflow-x: auto;">
                    <table id="myTable" class="table table-striped" style="min-width: 100%;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-family-cairo fw-bold font-14 text-center">#</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">الاسم</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">الرصيد</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">العنوان</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">التليفون</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">ID</th>
                                    @canany(["تعديل $permName", "حذف $permName"])
                                        <th class="font-family-cairo fw-bold font-14 text-center">عمليات</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($accounts as $index => $acc)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        @csrf
                                        {{ $acc->code }}
                                            - {{ $acc->aname }}
                                    </td>
                                    <td class="text-center"><a class="btn btn-sm btn-primary" href="{{ route('account-movement', ['accountId' => $acc['id']]) }}">{{ $acc->balance ?? 0.00}}</a></td>
                                    <td class="text-center">{{ $acc->address ?? '__'}}</td>
                                    <td class="text-center">{{ $acc->phone ?? '__'}}</td>
                                    <td class="text-center">{{ $acc->id }}</td>
                                    @canany(["تعديل $permName", "حذف $permName"])
                                    <td class="text-center">
                                        @can("تعديل $permName")
                                            <a href="{{ route('accounts.edit', $acc->id) }}"
                                                class="btn btn-success btn-icon-square-sm">
                                                <i class="las la-pen"></i>
                                            </a>
                                        @endcan

                                        @can("حذف $permName")
                                            <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-icon-square-sm"
                                                    onclick="return confirm('هل أنت متأكد؟')">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                    @endcan

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center">
                                        <div class="alert alert-info py-3 mb-0"
                                            style="font-size: 1.2rem; font-weight: 500;">
                                            <i class="las la-info-circle me-2"></i>
                                            لا توجد بيانات
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('itmsearch');
        const table = document.getElementById('myTable');
        if (!searchInput || !table) return;

        searchInput.addEventListener('keyup', function () {
            const filter = this.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(function (row) {
                let text = row.textContent.replace(/\s+/g, ' ').toLowerCase();
                if (filter === '' || text.indexOf(filter) !== -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>


@endsection