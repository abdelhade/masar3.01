@extends('admin.dashboard')

@section('title', 'تعديل نوع الخدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit me-2"></i>
                        تعديل نوع الخدمة: {{ $serviceType->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.service-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('services.service-types.update', $serviceType) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">
                                        كود نوع الخدمة <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $serviceType->code) }}" 
                                           min="1" 
                                           max="9999"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">يجب أن يكون الكود رقماً فريداً بين 1 و 9999</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        اسم نوع الخدمة <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $serviceType->name) }}" 
                                           maxlength="255"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="branch_id" class="form-label">الفرع</label>
                                    <select class="form-select @error('branch_id') is-invalid @enderror" 
                                            id="branch_id" 
                                            name="branch_id">
                                        <option value="">اختر الفرع (اختياري)</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" 
                                                    {{ old('branch_id', $serviceType->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">إذا لم تختر فرعاً، سيكون نوع الخدمة متاحاً لجميع الفروع</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('services.service-types.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
