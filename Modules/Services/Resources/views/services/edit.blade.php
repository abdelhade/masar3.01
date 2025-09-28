@extends('admin.dashboard')

@section('title', 'تعديل الخدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit me-2"></i>
                        تعديل الخدمة: {{ $service->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.services.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <form action="{{ route('services.services.update', $service) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">المعلومات الأساسية</h5>
                                
                                <div class="form-group mb-3">
                                    <label for="name">اسم الخدمة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $service->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="code">كود الخدمة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $service->code) }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="service_type_id">تصنيف الخدمة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('service_type_id') is-invalid @enderror" 
                                            id="service_type_id" name="service_type_id" required>
                                        <option value="">اختر تصنيف الخدمة</option>
                                        @foreach($serviceTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('service_type_id', $service->service_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="service_unit_id">وحدة الخدمة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('service_unit_id') is-invalid @enderror" 
                                            id="service_unit_id" name="service_unit_id" required>
                                        <option value="">اختر وحدة الخدمة</option>
                                        @foreach($serviceUnits as $unit)
                                            <option value="{{ $unit->id }}" {{ old('service_unit_id', $service->service_unit_id) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description">وصف الخدمة</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $service->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="col-md-6">
                                <h5 class="mb-3">التسعير</h5>
                                
                                <div class="form-group mb-3">
                                    <label for="price">سعر الخدمة <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" value="{{ old('price', $service->price) }}" 
                                               step="0.01" min="0" required>
                                        <span class="input-group-text">ر.س</span>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="cost">تكلفة الخدمة</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('cost') is-invalid @enderror" 
                                               id="cost" name="cost" value="{{ old('cost', $service->cost) }}" 
                                               step="0.01" min="0">
                                        <span class="input-group-text">ر.س</span>
                                    </div>
                                    @error('cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" 
                                                   name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                نشط
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_taxable" 
                                                   name="is_taxable" value="1" {{ old('is_taxable', $service->is_taxable) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_taxable">
                                                خاضع للضريبة
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('services.services.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ التعديلات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-generate code from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const codeField = document.getElementById('code');
    
    if (!codeField.value || codeField.value.startsWith('SRV-')) {
        // Generate code from Arabic name (simple approach)
        const code = name.replace(/\s+/g, '').substring(0, 10).toUpperCase();
        codeField.value = 'SRV-' + code;
    }
});
</script>
@endpush