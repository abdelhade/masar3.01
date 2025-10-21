@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.purchases-invoices')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('طلبات الاحتياج'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('طلبات الاحتياج')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        {{-- أزرار التصدير --}}
                        <x-table-export-actions table-id="purchase-requests-table" filename="purchase-requests"
                            excel-label="{{ __('تصدير Excel') }}" pdf-label="{{ __('تصدير PDF') }}"
                            print-label="{{ __('طباعة') }}" />

                        <table id="purchase-requests-table" class="table table-striped mb-0 text-center align-middle"
                            style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('رقم المستند') }}</th>
                                    <th>{{ __('التاريخ') }}</th>
                                    <th>{{ __('المبلغ') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    {{-- @canany(['تتبع طلب الاحتياج', 'تأكيد طلب الاحتياج']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $req)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $req->pro_id }}</td>
                                        <td>{{ $req->pro_date }}</td>
                                        <td>{{ number_format($req->pro_value, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge
                                                    @if ($req->workflow_state == 'مكتمل') bg-success
                                                    @elseif ($req->workflow_state == 'معلق') bg-warning
                                                    @elseif ($req->workflow_state == 'مرفوض') bg-danger
                                                    @else bg-secondary @endif">
                                                {{ $req->workflow_state ?? 'غير معروف' }}
                                            </span>
                                        </td>

                                        {{-- @canany(['تتبع طلب الاحتياج', 'تأكيد طلب الاحتياج']) --}}
                                        <td>
                                            {{-- @can('تتبع طلب الاحتياج') --}}
                                            <a href="{{ route('invoices.track', ['id' => $req->id]) }}"
                                                class="btn btn-secondary ">
                                                <i class="fas fa-route"></i> تتبع مراحل الطلب
                                            </a>
                                            {{-- @endcan --}}

                                            {{-- مثال على الموافقة (تقدر تفعلها لما تحتاج) --}}
                                            {{--
                                                @can('تأكيد طلب الاحتياج')
                                                    <form action="{{ route('invoices.confirm', ['id' => $req->id]) }}"
                                                          method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <input type="hidden" name="next_stage" value="1">
                                                        <button class="btn btn-success btn-icon-square-sm">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                                --}}
                                        </td>
                                        {{-- @endcanany --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('لا توجد طلبات حالياً') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $requests->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
