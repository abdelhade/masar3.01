@extends('admin.dashboard')

{{-- Dynamic Sidebar: نعرض فقط الحسابات --}}
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
        $permName = $permissionTypes[$type] ?? 'accounts';
        $parentCode = $parentCodes[$type] ?? null;
    @endphp

    <div class="container">

        {{-- رسائل الأخطاء --}}
        @if (session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif

        {{-- البريدكرامب --}}
        <section class="content-header">
            <div class="container-fluid">
                @include('components.breadcrumb', [
                    'title' => $permName ? __('قائمة الحسابات - ' . $permName) : __('قائمة الحسابات'),
                    'items' => [
                        ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
                        $permName ? ['label' => $permName] : ['label' => __('قائمة الحسابات')],
                    ],
                ])
            </div>
        </section>

        {{-- الأكشنات (إضافة + بحث) --}}
        <div class="row mt-3 justify-content-between align-items-center">
            <div class="col-md-3">
                <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}" class="btn btn-primary">
                    <i class="las la-plus"></i> {{ __('إضافة حساب جديد') }}
                </a>
            </div>

            <div class="col-md-4">
                <form method="GET" action="{{ route('accounts.index') }}" class="d-flex gap-2">
                    @if($type)
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif
                    
                    <input 
                        class="form-control" 
                        type="text" 
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="بحث بالكود | اسم الحساب | ID"
                        autocomplete="off">
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-search"></i>
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('accounts.index', ['type' => $type]) }}" class="btn btn-secondary">
                            <i class="las la-times"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- الجدول --}}
        <div class="card-body px-0 mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">{{ $permName ? 'قائمة ' . $permName : 'قائمة الحسابات' }}</h5>
                    <x-table-export-actions table-id="myTable" filename="accounts" excel-label="تصدير Excel"
                        pdf-label="تصدير PDF" print-label="طباعة" />
                </div>

                <div class="table-responsive" id="printed" style="overflow-x: auto;">
                    <table id="myTable" class="table table-striped table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الرصيد</th>
                                <th>العنوان</th>
                                <th>التليفون</th>
                                <th>ID</th>
                                <th>عمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accounts as $index => $acc)
                                <tr>
                                    <td>{{ $accounts->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ Str::limit($acc->aname, 40) }}</span>
                                            <span class="text-muted small">{{ $acc->code }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if(!$acc->secret)
                                            <a class="btn btn-sm btn-outline-dark"
                                                href="{{ route('account-movement', ['accountId' => $acc->id]) }}">
                                                {{ number_format($acc->balance ?? 0, 2) }}
                                            </a>
                                        @else
                                            <span class="text-muted">----</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                              title="{{ $acc->address }}">
                                            {{ $acc->address ?? '__' }}
                                        </span>
                                    </td>
                                    <td>{{ $acc->phone ?? '__' }}</td>
                                    <td><span class="badge bg-secondary">{{ $acc->id }}</span></td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('accounts.edit', $acc->id) }}" 
                                               class="btn btn-success btn-sm"
                                               title="تعديل">
                                                <i class="las la-pen"></i>
                                            </a>

                                            <form action="{{ route('accounts.destroy', $acc->id) }}" 
                                                  method="POST"
                                                  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm"
                                                        title="حذف"
                                                        onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="alert alert-info py-3 mb-0">
                                            <i class="las la-info-circle me-2"></i>
                                            @if(request('search'))
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
                @if($accounts->hasPages())
                    <div class="card-footer border-0 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
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
    </div>

@endsection
