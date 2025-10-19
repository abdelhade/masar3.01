@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.service')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('الصيانة'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('الصيانة')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            <a href="{{ route('maintenances.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                {{ __('إضافة جديد') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="maintenances-table" filename="maintenances"
                            excel-label="{{ __('تصدير Excel') }}" pdf-label="{{ __('تصدير PDF') }}"
                            print-label="{{ __('طباعة') }}" />

                        <table id="maintenances-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('اسم العميل') }}</th>
                                    <th>{{ __('رقم التليفون') }}</th>
                                    <th>{{ __('البند') }}</th>
                                    <th>{{ __('رقم البند') }}</th>
                                    <th>{{ __('نوع الصيانة') }}</th>
                                    <th>{{ __('التاريخ') }}</th>
                                    <th>{{ __('تاريخ الاستحقاق') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    <th>{{ __('العمليات') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($maintenances as $maintenance)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $maintenance->client_name }}</td>
                                        <td>{{ $maintenance->client_phone }}</td>
                                        <td>{{ $maintenance->item_name }}</td>
                                        <td>{{ $maintenance->item_number }}</td>
                                        <td>{{ $maintenance->type->name }}</td>
                                        <td>
                                            {{ $maintenance->date ? $maintenance->date->format('Y-m-d') : '-' }}
                                        </td>

                                        {{-- تاريخ الاستحقاق --}}
                                        <td>
                                            {{ $maintenance->accural_date ? $maintenance->accural_date->format('Y-m-d') : '-' }}
                                        </td>
                                        <td>
                                            @if ($maintenance->status)
                                                <span class="{{ $maintenance->status->color() }}">
                                                    {{ $maintenance->status->label() }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('maintenances.edit', $maintenance->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>

                                            <form action="{{ route('maintenances.destroy', $maintenance->id) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف عملية الصيانة هذه؟');">
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
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('لا توجد بيانات') }}
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
    </div>
@endsection
