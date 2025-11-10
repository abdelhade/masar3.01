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
                        تعديل معيار الجودة: {{ $standard->standard_name }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.standards.update', $standard) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- الصنف -->
                            <div class="col-md-6 mb-3">
                                <label for="item_id" class="form-label">الصنف <span class="text-danger">*</span></label>
                                <select class="form-select @error('item_id') is-invalid @enderror" 
                                        id="item_id" name="item_id" required>
                                    <option value="">-- اختر الصنف --</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" 
                                                {{ old('item_id', $standard->item_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- رمز المعيار -->
                            <div class="col-md-6 mb-3">
                                <label for="standard_code" class="form-label">رمز المعيار <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('standard_code') is-invalid @enderror" 
                                       id="standard_code" name="standard_code" 
                                       value="{{ old('standard_code', $standard->standard_code) }}" required>
                                @error('standard_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- اسم المعيار -->
                            <div class="col-md-12 mb-3">
                                <label for="standard_name" class="form-label">اسم المعيار <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('standard_name') is-invalid @enderror" 
                                       id="standard_name" name="standard_name" 
                                       value="{{ old('standard_name', $standard->standard_name) }}" required>
                                @error('standard_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الوصف -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">الوصف</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description', $standard->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- طريقة الاختبار -->
                            <div class="col-md-12 mb-3">
                                <label for="test_method" class="form-label">طريقة الاختبار</label>
                                <textarea class="form-control @error('test_method') is-invalid @enderror" 
                                          id="test_method" name="test_method" rows="3">{{ old('test_method', $standard->test_method) }}</textarea>
                                @error('test_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- حجم العينة -->
                            <div class="col-md-4 mb-3">
                                <label for="sample_size" class="form-label">حجم العينة <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('sample_size') is-invalid @enderror" 
                                       id="sample_size" name="sample_size" 
                                       value="{{ old('sample_size', $standard->sample_size) }}" min="1" required>
                                @error('sample_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تكرار الاختبار -->
                            <div class="col-md-4 mb-3">
                                <label for="test_frequency" class="form-label">تكرار الاختبار <span class="text-danger">*</span></label>
                                <select class="form-select @error('test_frequency') is-invalid @enderror" 
                                        id="test_frequency" name="test_frequency" required>
                                    <option value="">-- اختر التكرار --</option>
                                    <option value="per_batch" {{ old('test_frequency', $standard->test_frequency) == 'per_batch' ? 'selected' : '' }}>لكل دفعة</option>
                                    <option value="daily" {{ old('test_frequency', $standard->test_frequency) == 'daily' ? 'selected' : '' }}>يومي</option>
                                    <option value="weekly" {{ old('test_frequency', $standard->test_frequency) == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                    <option value="monthly" {{ old('test_frequency', $standard->test_frequency) == 'monthly' ? 'selected' : '' }}>شهري</option>
                                </select>
                                @error('test_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- عتبة القبول (%) -->
                            <div class="col-md-4 mb-3">
                                <label for="acceptance_threshold" class="form-label">عتبة القبول (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('acceptance_threshold') is-invalid @enderror" 
                                       id="acceptance_threshold" name="acceptance_threshold" 
                                       value="{{ old('acceptance_threshold', $standard->acceptance_threshold) }}" 
                                       min="0" max="100" required>
                                @error('acceptance_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الحد الأقصى للعيوب المسموح بها -->
                            <div class="col-md-6 mb-3">
                                <label for="max_defects_allowed" class="form-label">الحد الأقصى للعيوب المسموح بها <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('max_defects_allowed') is-invalid @enderror" 
                                       id="max_defects_allowed" name="max_defects_allowed" 
                                       value="{{ old('max_defects_allowed', $standard->max_defects_allowed) }}" 
                                       min="0" required>
                                @error('max_defects_allowed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الحالة -->
                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">الحالة</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', $standard->is_active) == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ old('is_active', $standard->is_active) == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ملاحظات -->
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">ملاحظات</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes', $standard->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('quality.standards.show', $standard) }}" class="btn btn-secondary">
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

