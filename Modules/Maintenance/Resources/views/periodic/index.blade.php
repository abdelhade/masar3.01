{{-- resources/views/maintenance/periodic/index.blade.php --}}
@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.service')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('الصيانة الدورية'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('الصيانة الدورية')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <a href="{{ route('periodic.maintenances.create') }}" type="button"
                class="btn btn-primary font-hold fw-bold">
                {{ __('إضافة جدول صيانة دوري') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="periodic-table" class="table table-striped mb-0" style="min-width: 1400px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('العميل') }}</th>
                                    <th>{{ __('البند') }}</th>
                                    <th>{{ __('نوع الصيانة') }}</th>
                                    <th>{{ __('التكرار') }}</th>
                                    <th>{{ __('الصيانة القادمة') }}</th>
                                    <th>{{ __('آخر صيانة') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    <th>{{ __('العمليات') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schedules as $schedule)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $schedule->client_name }}</strong><br>
                                            <small>{{ $schedule->client_phone }}</small>
                                        </td>
                                        <td>
                                            {{ $schedule->item_name }}<br>
                                            <small class="text-muted">رقم: {{ $schedule->item_number }}</small>
                                        </td>
                                        <td>{{ $schedule->serviceType->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $schedule->getFrequencyLabel() }}</span>
                                        </td>
                                        <td>
                                            {{ $schedule->next_maintenance_date->format('Y-m-d') }}<br>
                                            @if ($schedule->isOverdue())
                                                <span class="badge bg-danger">متأخرة</span>
                                            @elseif($schedule->isMaintenanceDueSoon())
                                                <span class="badge bg-warning">قريباً</span>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->last_maintenance_date?->format('Y-m-d') ?? 'لم تتم بعد' }}</td>
                                        <td>
                                            @if ($schedule->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-secondary">معطل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('periodic.maintenances.edit', $schedule->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>

                                            <form action="{{ route('periodic.maintenances.toggle', $schedule->id) }}"
                                                method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-warning btn-icon-square-sm"
                                                    title="{{ $schedule->is_active ? 'تعطيل' : 'تفعيل' }}">
                                                    <i class="las la-power-off"></i>
                                                </button>
                                            </form>

                                            <a class="btn btn-primary btn-icon-square-sm"
                                                href="{{ route('periodic.maintenances.create-maintenance', $schedule->id) }}"
                                                title="إنشاء صيانة">
                                                <i class="las la-wrench"></i>
                                            </a>

                                            <form action="{{ route('periodic.maintenances.destroy', $schedule->id) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الجدول؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="alert alert-info py-3 mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('لا توجد جداول صيانة دورية') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
