<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">معالجة الراتب المرن (ثابت + ساعات عمل مرن)</h4>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>{{ session('warning') }}</strong>
            @if (session()->has('error_details'))
                <div class="mt-3">
                    <ul class="mb-0 ps-4" style="list-style-type: disc;">
                        @foreach (session('error_details') as $error)
                            <li class="mb-2">{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>{{ session('error') }}</strong>
            @if (session()->has('error_details'))
                <div class="mt-3">
                    <ul class="mb-0 ps-4" style="list-style-type: disc;">
                        @foreach (session('error_details') as $error)
                            <li class="mb-2">{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Create New Processing Button --}}
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('flexible-salary.processing.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إنشاء معالجة جديدة
            </a>
        </div>
    </div>

    {{-- Previous Processings --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">المعالجات السابقة</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم الموظف</th>
                                    <th>الفترة</th>
                                    <th>الراتب الثابت</th>
                                    <th>عدد الساعات</th>
                                    <th>أجر الساعة</th>
                                    <th>إجمالي الراتب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processings as $processing)
                                <tr>
                                    <td>{{ $processing->employee->name }}</td>
                                    <td>{{ $processing->period_start->format('Y-m-d') }} - {{ $processing->period_end->format('Y-m-d') }}</td>
                                    <td>{{ number_format($processing->fixed_salary, 2) }}</td>
                                    <td>{{ number_format($processing->hours_worked, 2) }}</td>
                                    <td>{{ number_format($processing->hourly_wage, 2) }}</td>
                                    <td>{{ number_format($processing->total_salary, 2) }}</td>
                                    <td>{!! $processing->status_badge !!}</td>
                                    <td>
                                        @if($processing->status === 'pending')
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('flexible-salary.processing.edit', $processing->id) }}" 
                                                class="btn btn-sm btn-primary"
                                                title="تعديل">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <button wire:click="approveProcessing({{ $processing->id }})" 
                                                class="btn btn-sm btn-success"
                                                wire:confirm="هل أنت متأكد من الموافقة على هذه المعالجة؟"
                                                title="موافقة">
                                                <i class="fas fa-check"></i> موافقة
                                            </button>
                                            <button wire:click="rejectProcessing({{ $processing->id }})" 
                                                class="btn btn-sm btn-danger"
                                                wire:confirm="هل أنت متأكد من رفض هذه المعالجة؟"
                                                title="رفض">
                                                <i class="fas fa-times"></i> رفض
                                            </button>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد معالجات</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $processings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

