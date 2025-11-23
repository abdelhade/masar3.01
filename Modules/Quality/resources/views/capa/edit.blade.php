@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit me-2"></i>تعديل الإجراء التصحيحي (CAPA)</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.capa.index') }}">الإجراءات التصحيحية</a></li>
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('quality.capa.update', $capa) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">تفاصيل الإجراء التصحيحي</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">تقرير عدم المطابقة</label>
                                <input type="text" class="form-control" value="{{ $capa->nonConformanceReport->ncr_number ?? '---' }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">نوع الإجراء</label>
                                <input type="text" class="form-control" value="{{ $capa->action_type == 'corrective' ? 'تصحيحي' : 'وقائي' }}" readonly>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label required">وصف الإجراء</label>
                                <textarea name="action_description" rows="4" 
                                          class="form-control @error('action_description') is-invalid @enderror" 
                                          placeholder="اشرح الإجراء المطلوب تنفيذه..." required>{{ old('action_description', $capa->action_description) }}</textarea>
                                @error('action_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">تحليل السبب الجذري</label>
                                <textarea name="root_cause_analysis" rows="3" class="form-control" 
                                          placeholder="ما هو السبب الجذري للمشكلة؟">{{ old('root_cause_analysis', $capa->root_cause_analysis) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">الإجراءات الوقائية</label>
                                <textarea name="preventive_measures" rows="3" class="form-control" 
                                          placeholder="ما هي الإجراءات الوقائية لمنع تكرار المشكلة؟">{{ old('preventive_measures', $capa->preventive_measures) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">تاريخ البدء المخطط</label>
                                <input type="text" class="form-control" value="{{ $capa->planned_start_date->format('Y-m-d') }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">تاريخ الإكمال المخطط</label>
                                <input type="date" name="planned_completion_date" 
                                       class="form-control @error('planned_completion_date') is-invalid @enderror" 
                                       value="{{ old('planned_completion_date', $capa->planned_completion_date->format('Y-m-d')) }}" required>
                                @error('planned_completion_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">التعيين والمتابعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">المسؤول عن التنفيذ</label>
                            <select name="responsible_person" class="form-select @error('responsible_person') is-invalid @enderror" required>
                                <option value="">اختر المسؤول</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('responsible_person', $capa->responsible_person) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('responsible_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الأولوية</label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ old('priority', $capa->priority) == 'low' ? 'selected' : '' }}>منخفضة</option>
                                <option value="medium" {{ old('priority', $capa->priority) == 'medium' ? 'selected' : '' }}>متوسطة</option>
                                <option value="high" {{ old('priority', $capa->priority) == 'high' ? 'selected' : '' }}>عالية</option>
                                <option value="critical" {{ old('priority', $capa->priority) == 'critical' ? 'selected' : '' }}>حرجة</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">نسبة الإنجاز</label>
                            <input type="number" name="completion_percentage" 
                                   class="form-control" value="{{ old('completion_percentage', $capa->completion_percentage) }}" 
                                   min="0" max="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">الحالة</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="planned" {{ old('status', $capa->status) == 'planned' ? 'selected' : '' }}>مخطط</option>
                                <option value="in_progress" {{ old('status', $capa->status) == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                <option value="completed" {{ old('status', $capa->status) == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="verified" {{ old('status', $capa->status) == 'verified' ? 'selected' : '' }}>تم التحقق</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات التنفيذ</label>
                            <textarea name="implementation_notes" rows="3" class="form-control" 
                                      placeholder="ملاحظات حول التنفيذ...">{{ old('implementation_notes', $capa->implementation_notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>حفظ التعديلات
                    </button>
                    <a href="{{ route('quality.capa.show', $capa) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection