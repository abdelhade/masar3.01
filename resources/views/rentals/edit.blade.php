@extends('admin.dashboard')

@section('content')
<div class="container py-4">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-3">
            <h3 class="mb-0">
                <i class="fas fa-edit ml-2"></i> تعديل بيانات التأجير
            </h3>
            <span class="badge badge-light bg-white text-warning">
                <i class="fas fa-pencil-alt ml-2"></i> نموذج تعديل
            </span>
        </div>

        <div class="card-body p-4">
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-circle"></i> خطأ في الإدخال
                </h5>
                <ul class="mb-0 pr-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('rentals.update', $rental->id) }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="acc3">المعدة</label>
                        <select name="acc3" class="form-control select2" required>
                            @foreach ($equipments as $equipment)
                                <option value="{{ $equipment->id }}" {{ $rental->acc3 == $equipment->id ? 'selected' : '' }}>
                                    {{ $equipment->code }} - {{ $equipment->aname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="acc1">العميل</label>
                        <select name="acc1" class="form-control select2" required>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $rental->acc1 == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->code }} - {{ $customer->aname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="emp_id">الموظف المسؤول</label>
                        <select name="emp_id" class="form-control select2" required>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $rental->emp_id == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->code }} - {{ $employee->aname }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="acc2" value="99">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="rental_price">قيمة الإيجار</label>
                        <input type="number" name="rental_price" class="form-control" step="0.01" min="0" value="{{ $rental->pro_value }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="project_id">المشروع</label>
                        <select name="project_id" class="form-control select2" required>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" {{ $rental->project_id == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="details">ملاحظات</label>
                        <textarea name="details" class="form-control" rows="1">{{ $rental->details }}</textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="start_date">تاريخ البداية</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $rental->start_date }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="end_date">تاريخ النهاية</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $rental->end_date }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="cost_center_id">مركز التكلفة</label>
                        <select name="cost_center_id" class="form-control select2" required>
                            @foreach ($cost_centers as $cost_center)
                                <option value="{{ $cost_center->id }}" {{ $rental->cost_center_id == $cost_center->id ? 'selected' : '' }}>
                                    {{ $cost_center->cname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-warning btn-lg px-5">
                        <i class="fas fa-save ml-2"></i> تحديث
                    </button>
                    <a href="{{ route('rentals.index') }}" class="btn btn-secondary btn-lg px-5 mr-2">
                        <i class="fas fa-times ml-2"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('rentalForm').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });
</script>
@endpush
