@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit me-2"></i>تعديل المعيار: {{ $standard->standard_code }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.standards.index') }}">معايير الجودة</a></li>
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('quality.standards.update', $standard) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">تفاصيل المعيار</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">الصنف</label>
                                <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ old('item_id', $standard->item_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">رمز المعيار</label>
                                <input type="text" name="standard_code" class="form-control @error('standard_code') is-invalid @enderror" 
                                       value="{{ old('standard_code', $standard->standard_code) }}" required>
                                @error('standard_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label required">اسم المعيار</label>
                                <input type="text" name="standard_name" class="form-control @error('standard_name') is-invalid @enderror" 
                                       value="{{ old('standard_name', $standard->standard_name) }}" required>
                                @error('standard_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" rows="3" class="form-control" 
                                          placeholder="وصف تفصيلي للمعيار...">{{ old('description', $standard->description) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">طريقة الاختبار</label>
                                <textarea name="test_method" rows="3" class="form-control" 
                                          placeholder="اشرح طريقة تنفيذ الاختبار...">{{ old('test_method', $standard->test_method) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">ملاحظات</label>
                                <textarea name="notes" rows="3" class="form-control" 
                                          placeholder="ملاحظات إضافية...">{{ old('notes', $standard->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">معايير الاختبار</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">حجم العينة</label>
                            <input type="number" name="sample_size" class="form-control @error('sample_size') is-invalid @enderror" 
                                   value="{{ old('sample_size', $standard->sample_size) }}" min="1" required>
                            @error('sample_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">تكرار الاختبار</label>
                            <select name="test_frequency" class="form-select @error('test_frequency') is-invalid @enderror" required>
                                <option value="">اختر التكرار</option>
                                <option value="per_batch" {{ old('test_frequency', $standard->test_frequency) == 'per_batch' ? 'selected' : '' }}>لكل دفعة</option>
                                <option value="daily" {{ old('test_frequency', $standard->test_frequency) == 'daily' ? 'selected' : '' }}>يومي</option>
                                <option value="weekly" {{ old('test_frequency', $standard->test_frequency) == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                <option value="monthly" {{ old('test_frequency', $standard->test_frequency) == 'monthly' ? 'selected' : '' }}>شهري</option>
                            </select>
                            @error('test_frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">عتبة القبول (%)</label>
                            <input type="number" step="0.01" name="acceptance_threshold" 
                                   class="form-control @error('acceptance_threshold') is-invalid @enderror" 
                                   value="{{ old('acceptance_threshold', $standard->acceptance_threshold) }}" min="0" max="100" required>
                            @error('acceptance_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">حد العيوب المسموح</label>
                            <input type="number" name="max_defects_allowed" 
                                   class="form-control @error('max_defects_allowed') is-invalid @enderror" 
                                   value="{{ old('max_defects_allowed', $standard->max_defects_allowed) }}" min="0" required>
                            @error('max_defects_allowed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الحالة</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ old('is_active', $standard->is_active) == '1' ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ old('is_active', $standard->is_active) == '0' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save me-2"></i>تحديث المعيار
                    </button>
                    <a href="{{ route('quality.standards.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection