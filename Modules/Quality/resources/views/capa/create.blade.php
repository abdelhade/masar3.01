@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-plus-circle me-2"></i>إجراء تصحيحي جديد (CAPA)</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.capa.index') }}">الإجراءات التصحيحية</a></li>
                    <li class="breadcrumb-item active">جديد</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('quality.capa.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">تفاصيل الإجراء التصحيحي</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">تقرير عدم المطابقة</label>
                                <select name="ncr_id" class="form-select @error('ncr_id') is-invalid @enderror" required>
                                    <option value="">اختر التقرير</option>
                                    @foreach($ncrs as $ncr)
                                        <option value="{{ $ncr->id }}" {{ old('ncr_id') == $ncr->id ? 'selected' : '' }}>
                                            {{ $ncr->ncr_number }} - {{ $ncr->item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ncr_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">نوع الإجراء</label>
                                <select name="action_type" class="form-select @error('action_type') is-invalid @enderror" required>
                                    <option value="">اختر النوع</option>
                                    <option value="corrective" {{ old('action_type') == 'corrective' ? 'selected' : '' }}>تصحيحي</option>
                                    <option value="preventive" {{ old('action_type') == 'preventive' ? 'selected' : '' }}>وقائي</option>
                                </select>
                                @error('action_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label required">وصف الإجراء</label>
                                <textarea name="action_description" rows="4" 
                                          class="form-control @error('action_description') is-invalid @enderror" 
                                          placeholder="اشرح الإجراء المطلوب تنفيذه..." required>{{ old('action_description') }}</textarea>
                                @error('action_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">تحليل السبب الجذري</label>
                                <textarea name="root_cause_analysis" rows="3" class="form-control" 
                                          placeholder="ما هو السبب الجذري للمشكلة؟">{{ old('root_cause_analysis') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">الإجراءات الوقائية</label>
                                <textarea name="preventive_measures" rows="3" class="form-control" 
                                          placeholder="ما هي الإجراءات الوقائية لمنع تكرار المشكلة؟">{{ old('preventive_measures') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">تاريخ البدء المخطط</label>
                                <input type="date" name="planned_start_date" 
                                       class="form-control @error('planned_start_date') is-invalid @enderror" 
                                       value="{{ old('planned_start_date', date('Y-m-d')) }}" required>
                                @error('planned_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">تاريخ الإكمال المخطط</label>
                                <input type="date" name="planned_completion_date" 
                                       class="form-control @error('planned_completion_date') is-invalid @enderror" 
                                       value="{{ old('planned_completion_date') }}" required>
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
                                    <option value="{{ $user->id }}" {{ old('responsible_person') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('responsible_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الأولوية</label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ old('priority', 'medium') == 'low' ? 'selected' : '' }}>منخفضة</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>متوسطة</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>عالية</option>
                                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>حرجة</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التكلفة المقدرة</label>
                            <input type="number" step="0.01" name="estimated_cost" 
                                   class="form-control" value="{{ old('estimated_cost') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">نسبة الإنجاز</label>
                            <input type="number" name="completion_percentage" 
                                   class="form-control" value="{{ old('completion_percentage', 0) }}" 
                                   min="0" max="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">المرفقات</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">مستندات، صور، تقارير</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>حفظ الإجراء
                    </button>
                    <a href="{{ route('quality.capa.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection