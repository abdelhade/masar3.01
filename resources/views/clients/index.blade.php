@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('العملاء'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('العملاء')]],
    ])
    <style>
        .form-check-input.toggle-active {
            width: 2em;
            height: 1em;
        }

        .form-check-input.toggle-active:checked {
            background-color: #28a745;
        }

        span.d-inline-flex {
            vertical-align: middle;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <a href="{{ route('clients.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                اضافه عميل جديد
                <i class="fas fa-plus me-2"></i>
            </a>
            <br><br>
            <x-app::excel-importer model="Client" :column-mapping="[
                'cname' => 'cname',
                'email' => 'email',
                'phone' => 'phone',
                'phone2' => 'phone2',
                'address' => 'address',
                'job' => 'job',
                'gender' => 'gender',
                'type' => 'type',
                'national_id' => 'national_id',
            ]" :validation-rules="[]" button-text="استيراد العملاء"
                button-size="small" />

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="clients-table" filename="clients-table" excel-label="تصدير Excel"
                            pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="clients-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('اسم العميل') }}</th>
                                    <th>{{ __('البريد الإلكتروني') }}</th>
                                    <th>{{ __('الهاتف') }}</th>
                                    <th>{{ __('العنوان') }}</th>
                                    <th>{{ __('الوظيفة') }}</th>
                                    <th>{{ __('تاريخ الميلاد') }}</th>
                                    <th>{{ __('الصفه') }}</th>
                                    <th>{{ __('الجنس') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    <th>{{ __('العمليات') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clients as $client)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $client->cname }}</td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->phone }}</td>
                                        <td>{{ $client->address }}</td>
                                        <td>{{ $client->job }}</td>
                                        <td>{{ $client->date_of_birth?->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $client->type->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($client->type === 'person')
                                                @if ($client->gender === 'male')
                                                    <span class="badge bg-primary">ذكر</span>
                                                @elseif ($client->gender === 'female')
                                                    <span class="badge bg-pink">أنثى</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">—</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="d-inline-flex align-items-center">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input toggle-active"
                                                        data-id="{{ $client->id }}"
                                                        {{ $client->is_active ? 'checked' : '' }}>
                                                </div>
                                            </span>
                                        </td>

                                        <td>
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('clients.edit', $client->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            <form action="{{ route('clients.destroy', $client->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟');">
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
                                        <td colspan="14" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                لا توجد بيانات عملاء
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $clients->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.querySelectorAll('.toggle-active').forEach((el) => {
            el.addEventListener('change', function() {
                let clientId = this.getAttribute('data-id');
                let newStatus = this.checked ? '1' : '0';

                fetch("{{ url('/clients/toggle-active') }}/" + clientId, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json",
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert("حصل خطأ أثناء التحديث");
                            this.checked = !this.checked; // عكس الحالة إذا فشل التحديث
                        }
                    })
                    .catch(() => {
                        alert("حصل خطأ في الاتصال");
                        this.checked = !this.checked; // عكس الحالة إذا فشل الاتصال
                    });
            });
        });
    </script>
@endpush
