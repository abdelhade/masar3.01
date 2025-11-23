@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-plus-circle me-2"></i>إضافة دفعة جديدة</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.batches.index') }}">تتبع الدفعات</a></li>
                    <li class="breadcrumb-item active">جديد</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">تفاصيل الدفعة</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.batches.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- رقم الدفعة -->
                            <div class="col-md-6 mb-3">
                                <label for="batch_number" class="form-label">رقم الدفعة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('batch_number') is-invalid @enderror"
                                       id="batch_number" name="batch_number" value="{{ old('batch_number') }}" required>
                                @error('batch_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الصنف -->
                            <div class="col-md-6 mb-3">
                                <label for="item_id" class="form-label">الصنف <span class="text-danger">*</span></label>
                                <select class="form-select @error('item_id') is-invalid @enderror"
                                        id="item_id" name="item_id" required>
                                    <option value="">-- اختر الصنف --</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تاريخ الإنتاج -->
                            <div class="col-md-6 mb-3">
                                <label for="production_date" class="form-label">تاريخ الإنتاج <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('production_date') is-invalid @enderror"
                                       id="production_date" name="production_date" value="{{ old('production_date') }}" required>
                                @error('production_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تاريخ الصلاحية -->
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">تاريخ الصلاحية</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror"
                                       id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الكمية -->
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">الكمية <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" class="form-control @error('quantity') is-invalid @enderror"
                                       id="quantity" name="quantity" value="{{ old('quantity') }}" min="0" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- حالة الجودة -->
                            <div class="col-md-6 mb-3">
                                <label for="quality_status" class="form-label">حالة الجودة <span class="text-danger">*</span></label>
                                <select class="form-select @error('quality_status') is-invalid @enderror"
                                        id="quality_status" name="quality_status" required>
                                    <option value="">-- اختر الحالة --</option>
                                    <option value="passed" {{ old('quality_status') == 'passed' ? 'selected' : '' }}>ناجح</option>
                                    <option value="failed" {{ old('quality_status') == 'failed' ? 'selected' : '' }}>راسب</option>
                                    <option value="conditional" {{ old('quality_status') == 'conditional' ? 'selected' : '' }}>مشروط</option>
                                    <option value="quarantine" {{ old('quality_status') == 'quarantine' ? 'selected' : '' }}>حجر صحي</option>
                                </select>
                                @error('quality_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- المورد -->
                            <div class="col-md-6 mb-3">
                                <label for="supplier_id" class="form-label">المورد</label>
                                <select class="form-select @error('supplier_id') is-invalid @enderror"
                                        id="supplier_id" name="supplier_id">
                                    <option value="">-- اختر المورد --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->aname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- المستودع -->
                            <div class="col-md-6 mb-3">
                                <label for="warehouse_id" class="form-label">المستودع</label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror"
                                        id="warehouse_id" name="warehouse_id">
                                    <option value="">-- اختر المستودع --</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->aname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الموقع -->
                            <div class="col-md-12 mb-3">
                                <label for="location" class="form-label">الموقع في المستودع</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location" value="{{ old('location') }}"
                                       placeholder="مثال: رف A - مستوى 2 - موقع 5">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ملاحظات -->
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">ملاحظات</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('quality.batches.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>حفظ الدفعة
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
    const productionDate = document.getElementById('production_date');
    const expiryDate = document.getElementById('expiry_date');

    // Ensure expiry date is after production date
    productionDate.addEventListener('change', function() {
        expiryDate.min = this.value;
        if (expiryDate.value && expiryDate.value < this.value) {
            expiryDate.value = '';
        }
    });
});
</script>
@endsection

