@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('العملاء'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('العملاء')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('إضافة العملااء')
                <a href="{{ route('clients.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                    اضافه جديده
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('النوع') }}</th>
                                    <th>{{ __('الهاتف') }}</th>
                                    <th>{{ __('الايميل') }}</th>
                                    <th>{{ __('العنوان') }}</th>
                                    <th>{{ __('ملاحظات') }}</th>
                                    <th>{{ __('تم الاضافه بواسطة ') }}</th>
                                    @canany(['حذف العملااء', 'تعديل العملاء'])
                                        <th>{{ __('العمليات') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clients as $client)
                                    <tr class="text-center">
                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $client->name }}</td>
                                        <td>
                                            @if ($client->type == 'person')
                                                <span class="badge bg-primary">
                                                    {{ $client->type }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    {{ $client->type }}
                                                </span>
                                            @endif

                                        </td>
                                        <td>{{ $client->phone }}</td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->address }}</td>
                                        <td>{{ $client->notes }}</td>
                                        <td>{{ optional($client->creator)->name }}</td>
                                        @canany(['تعديل العملااء', 'حذف العملااء'])
                                            <td>
                                                @can('تعديل العملااء')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('clients.edit', $client->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('حذف العملااء')
                                                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('هل أنت متأكد من حذف هذا التخصص؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                لا توجد بيانات مضافة حتى الآن
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
