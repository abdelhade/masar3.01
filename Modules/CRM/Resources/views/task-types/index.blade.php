@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('انواع المهمات'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('انواع المهمات')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة انواع المهمات') --}}
            <a href="{{ route('tasks.types.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                اضافه جديده <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br><br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">

                        <x-table-export-actions table-id="work-item-types-table" filename="work-item-types" />

                        <table id="work-item-types-table" class="table table-striped text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('العنوان') }}</th>
                                    {{-- @canany(['تعديل انواع المهمات', 'حذف انواع المهمات']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($taskType as $type)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->title }}</td>
                                        {{-- @canany(['تعديل انواع المهمات', 'حذف انواع المهمات']) --}}
                                        <td>
                                            {{-- @can('تعديل انواع المهمات') --}}
                                            <a href="{{ route('tasks.types.edit', $type->id) }}"
                                                class="btn btn-success btn-icon-square-sm">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan

                                                @can('حذف انواع المهمات') --}}
                                            <form action="{{ route('tasks.types.destroy', $type->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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
                                        <td colspan="3" class="text-center">
                                            <div class="alert alert-info mb-0">لا توجد بيانات</div>
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
