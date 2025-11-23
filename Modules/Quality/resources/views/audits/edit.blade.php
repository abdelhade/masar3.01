@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit me-2"></i>تعديل التدقيق</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.audits.index') }}">التدقيق</a></li>
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">تفاصيل التدقيق</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.audits.update', $audit) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="audit_title" class="form-label">عنوان التدقيق <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('audit_title') is-invalid @enderror" 
                                       id="audit_title" name="audit_title" value="{{ old('audit_title', $audit->audit_title) }}" required>
                                @error('audit_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="planned" {{ old('status', $audit->status) == 'planned' ? 'selected' : '' }}>مخطط</option>
                                    <option value="in_progress" {{ old('status', $audit->status) == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                    <option value="completed" {{ old('status', $audit->status) == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="cancelled" {{ old('status', $audit->status) == 'cancelled' ? 'selected' : '' }}>ملغى</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="planned_date" class="form-label">التاريخ المخطط <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('planned_date') is-invalid @enderror" 
                                       id="planned_date" name="planned_date" 
                                       value="{{ old('planned_date', $audit->planned_date?->format('Y-m-d')) }}" required>
                                @error('planned_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

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
                                @error('lead_auditor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="audit_objectives" class="form-label">أهداف التدقيق</label>
                                <textarea class="form-control @error('audit_objectives') is-invalid @enderror" 
                                          id="audit_objectives" name="audit_objectives" rows="3">{{ old('audit_objectives', $audit->audit_objectives) }}</textarea>
                                @error('audit_objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('quality.audits.show', $audit) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection