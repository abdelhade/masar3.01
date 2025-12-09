@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    @php
        $permissionTypes = [
            'clients' => 'Clients',
            'suppliers' => 'Suppliers',
            'funds' => 'Funds',
            'banks' => 'Banks',
            'employees' => 'Employees',
            'warhouses' => 'warhouses',
            'expenses' => 'Expenses',
            'revenues' => 'Revenues',
            'creditors' => 'various_creditors',
            'debtors' => 'various_debtors',
            'partners' => 'partners',
            'current-partners' => 'current_partners',
            'assets' => 'assets',
            'rentables' => 'rentables',
            'check-portfolios-incoming' => 'check-portfolios-incoming',
            'check-portfolios-outgoing' => 'check-portfolios-outgoing',
        ];

        $parentCodes = [
            'clients' => '1103', // العملاء
            'suppliers' => '2101', // الموردين
            'banks' => '1102', // البنوك
            'funds' => '1101', // الصناديق
            'warhouses' => '1104', // المخازن
            'expenses' => '5', // المصروفات
            'revenues' => '42', // الايرادات
            'creditors' => '2104', // دائنين اخرين
            'debtors' => '1106', // مدينين آخرين
            'partners' => '31', // الشريك الرئيسي
            'current-partners' => '3201', // جاري الشريك
            'assets' => '12', // الأصول
            'employees' => '2102', // الموظفين
            'rentables' => '1202', // مباني (أصل قابل للإيجار)
            'check-portfolios-incoming' => '1105', // حافظات أوراق القبض
            'check-portfolios-outgoing' => '2103', // حافظات أوراق الدفع
        ];

        $type = request('type');

        // Arabic labels for display without modifying the original $permissionTypes
        $permissionLabels = [
            'clients' => 'العملاء',
            'suppliers' => 'الموردين',
            'funds' => 'الصناديق',
            'banks' => 'البنوك',
            'employees' => 'الموظفين',
            'warhouses' => 'المخازن',
            'expenses' => 'المصروفات',
            'revenues' => 'الايرادات',
            'creditors' => 'دائنين آخرين',
            'debtors' => 'مدينين آخرين',
            'partners' => 'الشركاء',
            'current-partners' => 'جاري الشريك',
            'assets' => 'الأصول',
            'rentables' => 'الممتلكات القابلة للإيجار',
            'check-portfolios-incoming' => 'حافظات أوراق القبض',
            'check-portfolios-outgoing' => 'حافظات أوراق الدفع',
        ];

        // Prefer the Arabic label when available, otherwise fall back to the original mapping
        $permName = $permissionLabels[$type] ?? ($permissionTypes[$type] ?? 'accounts');
        $parentCode = $parentCodes[$type] ?? null;
    @endphp

    <div class="container-dashboard">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-page-title mb-2">{{ $permName ? 'قائمة ' . $permName : 'قائمة الحسابات' }}</h1>
            <p class="text-body-sm text-text-secondary">إدارة وعرض جميع الحسابات المالية</p>
        </div>

        {{-- رسائل الأخطاء --}}
        @if (session('error'))
            <div class="alert alert-danger mt-3 border-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- البريدكرامب --}}
        <section class="content-header mb-4">
            <div class="container-fluid">
                @include('components.breadcrumb', [
                    'title' => $permName ? __('قائمة الحسابات - ' . $permName) : __('قائمة الحسابات'),
                    'items' => [
                        ['label' => ' الصفحه الرئيسية', 'url' => route('admin.dashboard')],
                        $permName ? ['label' => $permName] : ['label' => __('قائمة الحسابات')],
                    ],
                ])
            </div>
        </section>

        {{-- الأكشنات (إضافة + بحث) --}}
        <div class="row">
            <div class="flex-shrink-0 col-md-8">
                @if ($type == 'current-partners')
                    <p class="p-3 rounded-lg" style="background: linear-gradient(135deg, #34d3a3 0%, #239d77 100%); color: white;">
                        يتم اضافة حساب مع اضافة شريك جديد
                    </p>
                @elseif(
                    $type &&
                        isset($permissionTypes[$type]) &&
                        auth()->user()->can('create ' . $permissionTypes[$type]))
                    <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}" class="btn btn-main">
                        <i class="las la-plus"></i> {{ __('إضافة حساب جديد') }}
                    </a>
                @elseif(!$type)
                    <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}" class="btn btn-main">
                        <i class="las la-plus"></i> {{ __('إضافة حساب جديد') }}
                    </a>
                @endif
            </div>

            <div class="col-md-4">
                <form method="GET" action="{{ route('accounts.index') }}" class="flex gap-2">
                    <div class="row">
                        <div class="col-8">
                    @if ($type)
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif
                    
                    <input class="input form-control" type="text" name="search" value="{{ request('search') }}"
                        placeholder="بحث بالكود | اسم الحساب | ID" autocomplete="off">
                        </div>
                        <div class="col-3">
                    <button type="submit" class="btn btn-lg btn-main col-2">
                        <i class="las la-search text-lg"></i>
                    </button>
                    </div>
                    <div class="col-2"> 
                       
                    @if (request('search'))
                        <a href="{{ route('accounts.index', ['type' => $type]) }}" class="btn btn-outline col-2">
                            <i class="las la-times"></i>
                        </a>
                    @endif
                    </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- الجدول --}}
        <div class="card hover-lift transition-base" style="border-left: 4px solid #34d3a3;">
            <div class="card-header border-b border-border-light p-4 d-flex justify-content-between align-items-center">
                <h3 class="text-section-title mb-0">{{ $permName ? 'قائمة ' . $permName : 'قائمة الحسابات' }}</h3>
                <x-table-export-actions table-id="myTable" filename="accounts" excel-label="تصدير Excel"
                    pdf-label="تصدير PDF" print-label="طباعة" />
            </div>

            <div class="container-table" id="printed">
                <table id="myTable" class="table table-sticky">
                    <thead>
                        <tr>
                            <th class="text-table-header">#</th>
                            <th class="text-table-header">الاسم</th>
                            <th class="text-table-header">الرصيد</th>
                            <th class="text-table-header">العنوان</th>
                            <th class="text-table-header">التليفون</th>
                            <th class="text-table-header">ID</th>
                            <th class="text-table-header">عمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $index => $acc)
                            <tr>
                                <td class="text-table">{{ $accounts->firstItem() + $index }}</td>
                                <td class="text-table">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-text-primary">
                                            <i class="text-text-tertiary small">{{ $acc->code }}</i> - {{ Str::limit($acc->aname, 40) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-table">
                                    @if (!$acc->secret)
                                        <a class="btn btn-outline btn-sm"
                                            href="{{ route('account-movement', ['accountId' => $acc->id]) }}">
                                            {{ number_format($acc->balance ?? 0, 2) }}
                                        </a>
                                    @else
                                        <span class="text-text-tertiary">----</span>
                                    @endif
                                </td>
                                <td class="text-table">
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                        title="{{ $acc->address }}">
                                        {{ $acc->address ?? '__' }}
                                    </span>
                                </td>
                                <td class="text-table">{{ $acc->phone ?? '__' }}</td>
                                <td class="text-table">
                                    <span class="badge badge-neutral">{{ $acc->id }}</span>
                                </td>
                                <td class="text-table">
                                    @php
                                        // تحديد نوع الحساب من الكود - ترتيب من الأطول للأقصر
                                        $accountType = null;
                                        $sortedCodes = $parentCodes;
                                        uksort($sortedCodes, function ($a, $b) use ($parentCodes) {
                                            return strlen($parentCodes[$b]) - strlen($parentCodes[$a]);
                                        });

                                        foreach ($sortedCodes as $typeKey => $code) {
                                            if (str_starts_with($acc->code, $code)) {
                                                $accountType = $typeKey;
                                                break;
                                            }
                                        }
                                        $permissionName =
                                            $accountType && isset($permissionTypes[$accountType])
                                                ? $permissionTypes[$accountType]
                                                : 'accounts';
                                    @endphp
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if (auth()->user()->can('edit ' . $permissionName))
                                            <a href="{{ route('accounts.edit-direct', $acc->id) }}" 
                                                class="btn btn-success btn-sm" 
                                                title="تعديل">
                                                <i class="las la-pen"></i>
                                            </a>
                                        @endif

                                        @if (
                                            $acc->deletable &&
                                                auth()->user()->can('delete ' . $permissionName))
                                            <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm" 
                                                    title="حذف"
                                                    onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-4">
                                    <div class="alert alert-info py-3 mb-0" style="background: #e6f2ff; border-left: 4px solid #1a8eff; color: #0075e6;">
                                        <i class="las la-info-circle me-2"></i>
                                        @if (!$type)
                                            يرجى اختيار نوع الحسابات من القائمة الجانبية
                                        @elseif (request('search'))
                                            لا توجد نتائج للبحث عن "{{ request('search') }}"
                                        @else
                                            لا توجد بيانات
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($accounts->hasPages())
                <div class="card-footer border-t border-border-light p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-body-sm text-text-secondary">
                            عرض {{ $accounts->firstItem() }} إلى {{ $accounts->lastItem() }}
                            من أصل {{ $accounts->total() }} حساب
                        </div>

                        <div>
                            {{ $accounts->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection


