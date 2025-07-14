@extends('admin.dashboard') {{-- أو layout حسب ما تستخدمه --}}

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
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

                            @if(request('type') && isset($typeLabels[request('type')]))
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
                        <a href="{{ route('accounts.create', ['parent' => $parentCode]) }}" class="btn btn-primary cake cake-fadeIn">
                            {{ __('إضافة حساب جديد') }}
                        </a>
                        @endcan
                        @endif
                    </div>



                    </div>

                    <div class="row mt-2">
                        <div class="col"></div>
                        <div class="col">
                            <input class="form-control form-control-lg frst" type="text" id="itmsearch" placeholder="بحث بالكود | اسم الحساب | ID">
                        </div>
                    </div>
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
                    <div class="table-responsive">
                        <table id="myTable" class="display table table-hover table-strippedtable-sortable" data-page-length='50'>
                            <thead>
                                <tr>
                                    <th class="font-family-cairo fw-bold font-14">#</th>
                                    <th class="font-family-cairo fw-bold font-14">الاسم</th>
                                    <th class="font-family-cairo fw-bold font-14">الرصيد</th>
                                    <th class="font-family-cairo fw-bold font-14">العنوان</th>
                                    <th class="font-family-cairo fw-bold font-14">التليفون</th>
                                    <th class="font-family-cairo fw-bold font-14">ID</th>
                                  @canany([
                                    'إضافة العملاء',
                                    'حذف العملاء'
                                  ])

                                    <th class="font-family-cairo fw-bold font-14">عمليات</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $index => $acc)
                                <tr>
                                    <td class="font-family-cairo fw-bold font-14">{{ $index + 1 }}</td>
                                    <td class="font-family-cairo fw-bold font-14">
                                        <form action="" method="post">
                                            @csrf
                                            <input type="hidden" name="acc_id" value="{{ $acc->id }}">
                                            <button class="btn btn-light btn-block font-family-cairo fw-bold font-14" type="submit">
                                                {{ $acc->code }} - {{ $acc->aname }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="font-family-cairo fw-bold font-14">{{ $acc->balance }}</td>
                                    <td class="font-family-cairo fw-bold font-14">{{ $acc->address }}</td>
                                    <td class="font-family-cairo fw-bold font-14">{{ $acc->phone }}</td>
                                    <td class="font-family-cairo fw-bold font-14">{{ $acc->id }}</td>
                                   @canany([
                                    'إضافة العملاء',
                                    'حذف العملاء'
                                  ])
                                    <td x-show="">
                                        @can('تعديل العملاء')
                                        <button>
                                            <a href="{{ route('accounts.edit', $acc->id) }}" class="text-primary font-16"><i class="las la-pen"></i></a>
                                      </button>
                                      @endcan
                                      @can('حذف العملاء')
                                          
                                    
                                            <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-danger font-16" onclick="return confirm('هل أنت متأكد؟')">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                                  
                                            </form>
                                            @endcan
                                    </td>
                                    @endcanany
                                </tr>
                                @endforeach
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <th class="font-family-cairo fw-bold font-14">#</th>
                                    <th class="font-family-cairo fw-bold font-14">الاسم</th>
                                    <th class="font-family-cairo fw-bold font-14">الرصيد</th>
                                    <th class="font-family-cairo fw-bold font-14">العنوان</th>
                                    <th class="font-family-cairo fw-bold font-14">التليفون</th>
                                    <th class="font-family-cairo fw-bold font-14">ID</th>
                                    <th class="font-family-cairo fw-bold font-14">عمليات</th>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
  $(document).ready(function () {
    $('#myTable').DataTable();
  });
</script>
@endsection
