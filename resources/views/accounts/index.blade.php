@extends('admin.dashboard') {{-- أو layout حسب ما تستخدمه --}}

@section('content')
    <div class="content-wrapper">
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
                                        'debtor' => 'المدينين',
                                        'partner' => 'الشركاء',
                                        'current-partner' => 'الشركاء',
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
                    @php
                        $parentCodes = [
                            'client' => '122',
                            'supplier' => '211',
                            'bank' => '124',
                            'fund' => '121',
                            'store' => '123',
                            'expense' => '44',
                            'revenue' => '32',
                            'creditor' => '212',
                            'depitor' => '125',
                            'partner' => '221',
                            'current-partner' => '224',
                            'asset' => '11',
                            'employee' => '213',
                            'rentable' => '112',
                        ];

                        $type = request()->get('type');
                        $parentCode = $parentCodes[$type] ?? null;
                    @endphp

                    <div class="col-md-3">
                        @if ($parentCode)
                            @can('إضافةالعملاء')
                                <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}"
                                    class="btn btn-primary cake cake-fadeIn">
                                    {{ __('إضافة حساب جديد') }}
                                </a>
                            @endcan
                        @endif
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4">
                        @if ($parentCode)
                            <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}"
                                class="btn btn-primary cake cake-fadeIn">
                                {{ __('إضافة حساب جديد') }}
                                <i class="fas fa-plus me-2"></i>
                            </a>
                        @endif
                    </div>
                    <div class="col">
                        <input class="form-control form-control-lg frst" type="text" id="itmsearch"
                            placeholder="بحث بالكود | اسم الحساب | ID">
                       
                            @if (session('success'))
                                <div class="alert alert-success cake cake-zoomIn ">
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger cake cake-zoomIn">
                                    {{ session('error') }}
                                </div>
                            @endif

                       
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success cake cake-zoomIn ">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger cake cake-zoomIn ">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="card">
                            <div class="card-header">
                                <div class="table-responsive" style="overflow-x: auto;">
                                    <table class="table table-striped mb-0" style="min-width: 1200px;">
                                        <thead class="table-light text-center align-middle">

                                            <tr>
                                                <th class="font-family-cairo fw-bold font-14 text-center">#</th>
                                                <th class="font-family-cairo fw-bold font-14 text-center">الاسم</th>
                                                <th class="font-family-cairo fw-bold font-14 text-center">الرصيد</th>
                                                <th class="font-family-cairo fw-bold font-14 text-center">العنوان</th>
                                                <th class="font-family-cairo fw-bold font-14 text-center">التليفون</th>
                                                <th class="font-family-cairo fw-bold font-14 text-center">ID</th>
                                                <th class="font-family-cairo fw-bold font-14 text-center">عمليات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($accounts as $index => $acc)
                                                <tr>
                                                    <td class="font-family-cairo fw-bold font-14 text-center">
                                                        {{ $index + 1 }}
                                                    </td>
                                                    <td class="font-family-cairo fw-bold font-14 text-center">
                                                        <form action="" method="post">
                                                            @csrf
                                                            <input type="hidden" name="acc_id"
                                                                value="{{ $acc->id }}">
                                                            <button
                                                                class="btn btn-light btn-block font-family-cairo fw-bold font-14"
                                                                type="submit">
                                                                {{ $acc->code }} - {{ $acc->aname }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td class="font-family-cairo fw-bold font-14 text-center">
                                                        {{ $acc->balance }}
                                                    </td>
                                                    <td class="font-family-cairo fw-bold font-14 text-center">
                                                        {{ $acc->address }}
                                                    </td>
                                                    <td class="font-family-cairo fw-bold font-14 text-center">
                                                        {{ $acc->phone }}
                                                    </td>
                                                    <td class="font-family-cairo fw-bold font-14 text-center">
                                                        {{ $acc->id }}
                                                    </td>
                                                    <td x-show="" class="text-center">

                                                        <a href="{{ route('accounts.edit', $acc->id) }}"
                                                            class="btn btn-success btn-icon-square-sm"><i
                                                                class="las la-pen"></i></a>

                                                        <form action="{{ route('accounts.destroy', $acc->id) }}"
                                                            method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger btn-icon-square-sm"
                                                                onclick="return confirm('هل أنت متأكد؟')">
                                                                <i class="las la-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </td>
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
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
    </script>
@endsection
