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
            'clients' => __('Clients'),
            'suppliers' => __('Suppliers'),
            'funds' => __('Funds'),
            'banks' => __('Banks'),
            'employees' => __('Employees'),
            'warhouses' => __('Warehouses'),
            'expenses' => __('Expenses'),
            'revenues' => __('Revenues'),
            'creditors' => __('Other Creditors'),
            'debtors' => __('Other Debtors'),
            'partners' => __('Partners'),
            'current-partners' => __('Partner Current Account'),
            'assets' => __('Assets'),
            'rentables' => __('Rentable Properties'),
            'check-portfolios-incoming' => __('Incoming Check Portfolios'),
            'check-portfolios-outgoing' => __('Outgoing Check Portfolios'),
        ];

        // Prefer the Arabic label when available, otherwise fall back to the original mapping
        $permName = $permissionLabels[$type] ?? ($permissionTypes[$type] ?? 'accounts');
        $parentCode = $parentCodes[$type] ?? null;
    @endphp

    <div class="container-dashboard">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-page-title mb-2">{{ $permName ? __('List') . ' ' . $permName : __('Accounts List') }}</h1>
            <p class="text-body-sm text-text-secondary">{{ __('Manage and view all financial accounts') }}</p>
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
                    'title' => $permName ? __('Accounts List') . ' - ' . $permName : __('Accounts List'),
                    'items' => [
                        ['label' => __('Home'), 'url' => route('admin.dashboard')],
                        $permName ? ['label' => $permName] : ['label' => __('Accounts List')],
                    ],
                ])
            </div>
        </section>

        {{-- الأكشنات (إضافة + بحث) --}}
        <div class="row">
            <div class="flex-shrink-0 col-md-8">
                @if ($type == 'current-partners')
                    <p class="p-3 rounded-lg" style="background: linear-gradient(135deg, #34d3a3 0%, #239d77 100%); color: white;">
                        {{ __('Account is added when adding a new partner') }}
                    </p>
                @elseif(
                    $type &&
                        isset($permissionTypes[$type]) &&
                        auth()->user()->can('create ' . $permissionTypes[$type]))
                    <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}" class="btn btn-main">
                        <i class="las la-plus"></i> {{ __('Add New Account') }}
                    </a>
                @elseif(!$type)
                    <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}" class="btn btn-main">
                        <i class="las la-plus"></i> {{ __('Add New Account') }}
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
                        placeholder="{{ __('Search by Code | Account Name | ID') }}" autocomplete="off">
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
                <h3 class="text-section-title mb-0">{{ $permName ? __('List') . ' ' . $permName : __('Accounts List') }}</h3>
                @php
                    $printPermission = $type && isset($permissionTypes[$type]) 
                        ? 'print ' . $permissionTypes[$type] 
                        : null;
                @endphp
                <x-table-export-actions 
                    table-id="myTable" 
                    filename="accounts" 
                    :excel-label="__('Export Excel')"
                    :pdf-label="__('Export PDF')" 
                    :print-label="__('Print')"
                    :print-permission="$printPermission" />
            </div>

            <div class="container-table" id="printed">
                <table id="myTable" class="table table-sticky">
                    <thead>
                        <tr>
                            <th class="text-table-header">#</th>
                            <th class="text-table-header">{{ __('Name') }}</th>
                            <th class="text-table-header">{{ __('Balance') }}</th>
                            <th class="text-table-header">{{ __('Address') }}</th>
                            <th class="text-table-header">{{ __('Phone') }}</th>
                            <th class="text-table-header">ID</th>
                            <th class="text-table-header">{{ __('Actions') }}</th>
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
                                                :title="__('Edit')">
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
                                                    :title="__('Delete')"
                                                    onclick="return confirm('{{ __('Are you sure you want to delete?') }}')">
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
                                            {{ __('Please select account type from sidebar') }}
                                        @elseif (request('search'))
                                            {{ __('No results found for') }} "{{ request('search') }}"
                                        @else
                                            {{ __('No data available') }}
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
                            {{ __('Showing') }} {{ $accounts->firstItem() }} {{ __('to') }} {{ $accounts->lastItem() }}
                            {{ __('of') }} {{ $accounts->total() }} {{ __('account') }}
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


