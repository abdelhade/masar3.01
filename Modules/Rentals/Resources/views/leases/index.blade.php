@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('عقود الإيجار'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('عقود الإيجار')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة العقود') --}}
            <a href="{{ route('rentals.leases.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                {{ __('إضافة عقد جديد') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br><br>
            <div class="card">
                <div class="card-body">
                    <x-table-export-actions table-id="leases-table" filename="leases" excel-label="تصدير Excel"
                        pdf-label="تصدير PDF" print-label="طباعة" />

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="leases-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('الوحدة') }}</th>
                                    <th>{{ __('العميل') }}</th>
                                    <th>{{ __('تاريخ البداية') }}</th>
                                    <th>{{ __('تاريخ النهاية') }}</th>
                                    <th>{{ __('قيمة الإيجار') }}</th>
                                    <th>{{ __('الصندوق') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    <th>{{ __('ملاحظات') }}</th>
                                    {{-- @canany(['تعديل العقود', 'حذف العقود']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leases as $lease)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ optional($lease->unit)->name }}</td>
                                        <td>{{ optional($lease->client)->cname }}</td>
                                        <td>{{ $lease->start_date }}</td>
                                        <td>{{ $lease->end_date }}</td>
                                        <td>{{ number_format($lease->rent_amount, 2) }}</td>
                                        <td>{{ $lease->account->aname ?? '-' }}</td>
                                        <td>
                                            @switch($lease->status)
                                                @case(\Modules\Rentals\Enums\LeaseStatus::PENDING->value)
                                                    <span
                                                        class="badge bg-warning">{{ \Modules\Rentals\Enums\LeaseStatus::PENDING->label() }}</span>
                                                @break

                                                @case(\Modules\Rentals\Enums\LeaseStatus::ACTIVE->value)
                                                    <span
                                                        class="badge bg-success">{{ \Modules\Rentals\Enums\LeaseStatus::ACTIVE->label() }}</span>
                                                @break

                                                @case(\Modules\Rentals\Enums\LeaseStatus::EXPIRED->value)
                                                    <span
                                                        class="badge bg-secondary">{{ \Modules\Rentals\Enums\LeaseStatus::EXPIRED->label() }}</span>
                                                @break

                                                @case(\Modules\Rentals\Enums\LeaseStatus::TERMINATED->value)
                                                    <span
                                                        class="badge bg-danger">{{ \Modules\Rentals\Enums\LeaseStatus::TERMINATED->label() }}</span>
                                                @break
                                            @endswitch
                                        </td>

                                        <td>{{ $lease->notes ?? '-' }}</td>

                                        {{-- @canany(['تعديل العقود', 'حذف العقود']) --}}
                                        <td>
                                            {{-- @can('تعديل العقود') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('rentals.leases.edit', $lease->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف العقود') --}}
                                            <form action="{{ route('rentals.leases.destroy', $lease->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا العقد؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                            {{-- @endcan --}}
                                        </td>
                                        {{-- @endcanany --}}
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                <div class="alert alert-info py-3 mb-0"
                                                    style="font-size: 1.2rem; font-weight: 500;">
                                                    <i class="las la-info-circle me-2"></i>
                                                    لا توجد عقود مضافة حتى الآن
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $leases->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
