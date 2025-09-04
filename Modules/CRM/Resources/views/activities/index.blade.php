@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('الأنشطة'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('الأنشطة')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة الأنشطة') --}}
            <a href="{{ route('activities.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                اضافه نشاط جديد
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="activites-table" filename="activites-table"
                            excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="activites-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('العنوان') }}</th>
                                    <th>{{ __('النوع') }}</th>
                                    <th>{{ __('التاريخ') }}</th>
                                    <th>{{ __('الوقت') }}</th>
                                    <th>{{ __('العميل') }}</th>
                                    <th>{{ __('المسؤول') }}</th>
                                    <th>{{ __('الوصف') }}</th>
                                    {{-- @canany(['تعديل الأنشطة', 'حذف الأنشطة']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $activity->title }}</td>
                                        <td>
                                            @if ($activity->type->value === \Modules\CRM\Enums\ActivityTypeEnum::CALL->value)
                                                <span class="badge bg-primary">{{ $activity->type->label() }}</span>
                                            @elseif ($activity->type->value === \Modules\CRM\Enums\ActivityTypeEnum::MESSAGE->value)
                                                <span class="badge bg-success">{{ $activity->type->label() }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $activity->type->label() }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $activity->activity_date?->format('Y-m-d') }}</td>
                                        <td>{{ $activity->scheduled_at?->format('H:i') }}</td>
                                        <td>{{ optional($activity->client)->cname }}</td>
                                        <td>{{ optional($activity->assignedUser)->name }}</td>
                                        <td>{{ Str::limit($activity->description, 30) }}</td>

                                        {{-- @canany(['تعديل الأنشطة', 'حذف الأنشطة']) --}}
                                        <td>
                                            {{-- @can('تعديل الأنشطة') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('activities.edit', $activity->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف الأنشطة') --}}
                                            <form action="{{ route('activities.destroy', $activity->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا النشاط؟');">
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
                                                لا توجد أنشطة مضافة حتى الآن
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
