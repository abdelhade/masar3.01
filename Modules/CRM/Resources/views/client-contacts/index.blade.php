@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('جهات اتصال الشركات'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('مصدر الفرص')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('إضافة جهات اتصال الشركات')
                <a href="{{ route('client-contacts.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
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
                                    <th>{{ __('الشركه') }}</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('البريد الالكتروني') }}</th>
                                    <th>{{ __('الهاتف') }}</th>
                                    <th>{{ __('المنصب') }}</th>
                                    @canany(['تعديل جهات اتصال الشركات', 'حذف جهات اتصال الشركات'])
                                        <th>{{ __('العمليات') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ClientContacts as $contact)
                                    <tr class="text-center">
                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $contact->client->name }}</td>
                                        <td>{{ $contact->name }}</td>
                                        <td>{{ $contact->email }}</td>
                                        <td>{{ $contact->phone }}</td>
                                        <td>{{ $contact->position }}</td>
                                        @canany(['تعديل جهات اتصال الشركات', 'حذف جهات اتصال الشركات'])
                                            <td>
                                                @can('تعديل جهات اتصال الشركات')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('client-contacts.edit', $contact->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('حذف جهات اتصال الشركات')
                                                    <form action="{{ route('client-contacts.destroy', $contact->id) }}"
                                                        method="POST" style="display:inline-block;"
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
