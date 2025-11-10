@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل التدقيق: {{ $audit->audit_title }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.audits.update', $audit) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- عنوان التدقيق -->
                            <div class="col-md-6 mb-3">
                                <label for="audit_title" class="form-label">عنوان التدقيق <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('audit_title') is-invalid @enderror" 
                                       id="audit_title" name="audit_title" value="{{ old('audit_title', $audit->audit_title) }}" required>
                                @error('audit_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الحالة -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="planned" {{ old('status', $audit->status) == 'planned' ? 'selected' : '' }}>مخطط</option>
                                    <option value="in_progress" {{ old('status', $audit->status) == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                    <option value="completed" {{ old('status', $audit->status) == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="cancelled" {{ old('status', $audit->status) == 'cancelled' ? 'selected' : '' }}>ملغى</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- التاريخ المخطط -->
                            <div class="col-md-6 mb-3">
                                <label for="planned_date" class="form-label">التاريخ المخطط <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('planned_date') is-invalid @enderror" 
                                       id="planned_date" name="planned_date" 
                                       value="{{ old('planned_date', $audit->planned_date?->format('Y-m-d')) }}" required>
                                @error('planned_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- المدقق الرئيسي -->
                            <div class="col-md-6 mb-3">
                                <label for="lead_auditor_id" class="form-label">المدقق الرئيسي <span class="text-danger">*</span></label>
                                <select class="form-select @error('lead_auditor_id') is-invalid @enderror" 
                                        id="lead_auditor_id" name="lead_auditor_id" required>
                                    <option value="">-- اختر المدقق الرئيسي --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ old('lead_auditor_id', $audit->lead_auditor_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lead_auditor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- إجمالي النتائج -->
                            <div class="col-md-3 mb-3">
                                <label for="total_findings" class="form-label">إجمالي النتائج</label>
                                <input type="number" class="form-control @error('total_findings') is-invalid @enderror" 
                                       id="total_findings" name="total_findings" 
                                       value="{{ old('total_findings', $audit->total_findings) }}">
                                @error('total_findings')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- نتائج حرجة -->
                            <div class="col-md-3 mb-3">
                                <label for="critical_findings" class="form-label">نتائج حرجة</label>
                                <input type="number" class="form-control @error('critical_findings') is-invalid @enderror" 
                                       id="critical_findings" name="critical_findings" 
                                       value="{{ old('critical_findings', $audit->critical_findings) }}">
                                @error('critical_findings')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- نتائج رئيسية -->
                            <div class="col-md-3 mb-3">
                                <label for="major_findings" class="form-label">نتائج رئيسية</label>
                                <input type="number" class="form-control @error('major_findings') is-invalid @enderror" 
                                       id="major_findings" name="major_findings" 
                                       value="{{ old('major_findings', $audit->major_findings) }}">
                                @error('major_findings')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- نتائج ثانوية -->
                            <div class="col-md-3 mb-3">
                                <label for="minor_findings" class="form-label">نتائج ثانوية</label>
                                <input type="number" class="form-control @error('minor_findings') is-invalid @enderror" 
                                       id="minor_findings" name="minor_findings" 
                                       value="{{ old('minor_findings', $audit->minor_findings) }}">
                                @error('minor_findings')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- النتيجة العامة -->
                            <div class="col-md-12 mb-3">
                                <label for="overall_result" class="form-label">النتيجة العامة</label>
                                <select class="form-select" id="overall_result" name="overall_result">
                                    <option value="">-- اختر النتيجة --</option>
                                    <option value="excellent" {{ old('overall_result', $audit->overall_result) == 'excellent' ? 'selected' : '' }}>ممتاز</option>
                                    <option value="satisfactory" {{ old('overall_result', $audit->overall_result) == 'satisfactory' ? 'selected' : '' }}>مرضي</option>
                                    <option value="needs_improvement" {{ old('overall_result', $audit->overall_result) == 'needs_improvement' ? 'selected' : '' }}>يحتاج تحسين</option>
                                    <option value="unsatisfactory" {{ old('overall_result', $audit->overall_result) == 'unsatisfactory' ? 'selected' : '' }}>غير مرضي</option>
                                </select>
                            </div>

                            <!-- الملخص -->
                            <div class="col-md-12 mb-3">
                                <label for="summary" class="form-label">الملخص</label>
                                <textarea class="form-control @error('summary') is-invalid @enderror" 
                                          id="summary" name="summary" rows="4">{{ old('summary', $audit->summary) }}</textarea>
                                @error('summary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('quality.audits.show', $audit) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>تحديث
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

