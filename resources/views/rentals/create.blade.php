@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.projects')
@endsection

@section('content')
<div class="container py-4">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h3 class="mb-0">
                <i class="fas fa-calendar-plus ml-2"></i> تأجير معدة جديدة
            </h3>
            <span class="badge badge-light bg-white text-primary">
                <i class="fas fa-file-alt ml-2"></i> نموذج إدخال
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

            <form method="POST" action="{{ route('rentals.store') }}" class="needs-validation" novalidate>
                @csrf

                <div class="row">
                    <!-- المعدة -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="acc3" class="font-weight-bold">
                                <i class="fas fa-tools ml-2"></i> المعدة
                            </label>
                            <select name="acc3" id="acc3" class="form-control select2" required>
                                <option value="">اختر المعدة...</option>
                                @foreach ($equipments as $equipment)
                                <option value="{{ $equipment->id }}" {{ old('acc3') == $equipment->id ? 'selected' : '' }} {{ $equipment->rent_to > 0 ? 'disabled' :  $equipment->rent_to }}>
                                    {{ $equipment->code }} - {{ $equipment->aname }} {{ $equipment->rent_to > 0 ? ' (مستأجر)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">يرجى اختيار المعدة</div>
                        </div>
                    </div>

                    <!-- العميل -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="acc1" class="font-weight-bold">
                                <i class="fas fa-user-tie ml-2"></i> العميل
                            </label>
                            <select required name="acc1" id="acc1" class="form-control select2" required>
                                <option value="">اختر العميل...</option>
                                @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('acc1') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->code }} - {{ $customer->aname }}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">يرجى اختيار العميل</div>
                        </div>
                    </div>

                    <!-- الموظف -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="emp_id" class="font-weight-bold">
                                <i class="fas fa-user-tie ml-2"></i> الموظف المسؤول
                            </label>
                            <select name="emp_id" id="emp_id" class="form-control select2" required>
                                @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->code }} - {{ $employee->aname }}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">يرجى اختيار العميل</div>
                        </div>
                        <input type="text" name="acc2" id="" value="42" hidden>
                    </div>


                </div>


                <div class="row">
                    <!-- قيمة الإيجار -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="rental_price" class="font-weight-bold">
                                <i class="fas fa-money-bill-wave ml-2"></i> قيمة الإيجار
                            </label>
                            <div class="input-group">
                                <input type="number" name="rental_price" id="rental_price"
                                    class="form-control" step="0.01" min="0"
                                    value="{{ old('rental_price') }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">ر.س</span>
                                </div>
                                <div class="invalid-feedback">يرجى إدخال قيمة إيجار صحيحة</div>
                            </div>
                        </div>
                    </div>

                    <!-- المشروع -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="project_id" class="font-weight-bold">
                                <i class="fas fa-project-diagram ml-2"></i> المشروع
                            </label>
                            <select name="project_id" id="project_id" class="form-control select2" required>
                                <option value="">اختر المشروع...</option>
                                @foreach ($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">يرجى اختيار المشروع</div>
                        </div>
                    </div>

                    <!-- ملاحظات -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="details" class="font-weight-bold">
                                <i class="fas fa-sticky-note ml-2"></i> ملاحظات
                            </label>
                            <textarea name="details" id="details" class="form-control" rows="1">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- تاريخ البداية -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="start_date" class="font-weight-bold">
                                <i class="far fa-calendar-alt ml-2"></i> تاريخ البداية
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                class="form-control" value="{{ old('start_date') }}" required>
                            <div class="invalid-feedback">يرجى تحديد تاريخ البداية</div>
                        </div>
                    </div>

                    <!-- تاريخ النهاية -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="end_date" class="font-weight-bold">
                                <i class="far fa-calendar-check ml-2"></i> تاريخ النهاية
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                class="form-control" value="{{ old('end_date') }}" required>
                            <div class="invalid-feedback">يرجى تحديد تاريخ النهاية</div>
                        </div>
                    </div>


                    <!-- مركز التكلفة -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="cost_center_id" class="font-weight-bold">
                                <i class="fas fa-cost_center-diagram ml-2"></i> مركز التكلفة
                            </label>
                            <select name="cost_center_id" id="cost_center_id" class="form-control select2" required>
                                <option value="">اختر مركز التكلفة...</option>
                                @foreach ($cost_centers as $cost_center)
                                <option value="{{ $cost_center->id }}" {{ old('cost_center_id') == $cost_center->id ? 'selected' : '' }}>
                                    {{ $cost_center->cname }}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">يرجى اختيار مركز التكلفة</div>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <button type="submit" class="btn btn-main btn-lg px-5">
                            <i class="fas fa-save ml-2"></i> حفظ
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

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'اختر...',
            width: '100%'
        });

        // Date validation
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');

        startInput.addEventListener('change', function() {
            endInput.min = this.value;
            if (endInput.value && endInput.value < this.value) {
                endInput.value = this.value;
            }
        });

        endInput.addEventListener('change', function() {
            if (startInput.value && this.value < startInput.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ في التاريخ',
                    text: 'تاريخ النهاية لا يمكن أن يكون قبل تاريخ البداية',
                });
                this.value = startInput.value;
            }
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    });
</script>
@endsection

@section('styles')
<style>
    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: none;
    }

    .form-control,
    .select2-container--default .select2-selection--single {
        border-radius: 8px;
        padding: 10px 15px;
        height: calc(2.5em + .75rem + 2px);
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.5em + .75rem);
    }

    .invalid-feedback {
        font-size: 0.85rem;
    }

    .was-validated .form-control:invalid,
    .was-validated .custom-select:invalid {
        border-color: #dc3545;
    }
</style>

@endsection
