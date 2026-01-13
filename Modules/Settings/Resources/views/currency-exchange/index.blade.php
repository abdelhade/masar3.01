@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.settings')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => 'تبادل العملات',
        'items' => [
            ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
            ['label' => 'تبادل العملات'],
        ],
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>عمليات تبادل العملات</h2>
            {{-- @can('create currency-exchange') --}}
                <a href="{{ route('settings.currency-exchange.create') }}" class="btn btn-main">
                    <i class="las la-plus me-2"></i>
                    إضافة عملية جديدة
                </a>
            {{-- @endcan --}}
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>رقم السند</th>
                            <th>نوع العملية</th>
                            <th>من صندوق</th>
                            <th>إلى صندوق</th>
                            <th>العملة</th>
                            <th>القيمة الأصلية</th>
                            <th>القيمة المحولة</th>
                            <th>البيان</th>
                            <th>المستخدم</th>
                            <th class="text-center">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($exchanges as $exchange)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $exchange->pro_date }}</td>
                                <td>{{ $exchange->pro_id }}</td>
                                <td>
                                    @if ($exchange->pro_type == 80)
                                        <span class="badge bg-success">شراء عملة</span>
                                    @else
                                        <span class="badge bg-info">بيع عملة</span>
                                    @endif
                                </td>
                                <td>{{ $exchange->acc2Head->aname ?? '—' }}</td>
                                <td>{{ $exchange->acc1Head->aname ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $exchange->currency->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="fw-bold">
                                    @if ($exchange->currency_rate > 0)
                                        {{ number_format($exchange->pro_value / $exchange->currency_rate, 2) }}
                                    @else
                                        {{ number_format($exchange->pro_value, 2) }}
                                    @endif
                                </td>
                                <td class="fw-bold text-success">
                                    {{ number_format($exchange->pro_value, 2) }}
                                </td>
                                <td>{{ $exchange->details ?? '—' }}</td>
                                <td>{{ $exchange->user->name ?? '—' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        @can('edit currency-exchange')
                                            <a href="{{ route('settings.currency-exchange.edit', $exchange->id) }}"
                                                class="btn btn-sm btn-warning" title="تعديل">
                                                <i class="las la-edit"></i>
                                            </a>
                                        @endcan

                                        @can('delete currency-exchange')
                                            <form action="{{ route('settings.currency-exchange.destroy', $exchange->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('هل أنت متأكد من حذف هذه العملية؟')"
                                                    title="حذف">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">
                                    <div class="alert alert-info py-4 mb-0">
                                        <i class="las la-info-circle me-2"></i>
                                        <strong>لا توجد عمليات تبادل عملات حالياً</strong>
                                        <br>
                                        <small class="text-muted mt-2 d-block">
                                            يمكنك إضافة عملية جديدة باستخدام الزر أعلاه
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
