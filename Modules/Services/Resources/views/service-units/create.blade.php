@extends('admin.dashboard')

@section('title', 'إضافة وحدة خدمة جديدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus me-2"></i>
                        إضافة وحدة خدمة جديدة
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.service-units.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('services.service-units.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">
                                        كود وحدة الخدمة <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code') }}" 
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
                                        اسم وحدة الخدمة <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           maxlength="255"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">مثال: ساعة، جلسة، خدمة، مشروع</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            وحدة الخدمة نشطة
                                        </label>
                                    </div>
                                    <div class="form-text">الوحدات النشطة فقط ستظهر في قوائم الاختيار</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('services.service-units.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                حفظ وحدة الخدمة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const costInput = document.getElementById('cost');
    const sellPriceInput = document.getElementById('sell_price');
    
    // Auto-calculate sell price if cost is entered
    costInput.addEventListener('input', function() {
        const cost = parseFloat(this.value);
        if (cost && !sellPriceInput.value) {
            // Set sell price to cost + 50% margin
            sellPriceInput.value = (cost * 1.5).toFixed(3);
        }
    });
});
</script>
@endsection
