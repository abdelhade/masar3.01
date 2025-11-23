@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit me-2"></i>تعديل الفحص: {{ $inspection->inspection_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.inspections.index') }}">الفحوصات</a></li>
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ url('/quality/inspections/' . $inspection->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="inspection_id" value="{{ $inspection->id }}">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">تفاصيل الفحص</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">الصنف</label>
                                <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ old('item_id', $inspection->item_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">نوع الفحص</label>
                                <select name="inspection_type" class="form-select @error('inspection_type') is-invalid @enderror" required>
                                    <option value="">اختر النوع</option>
                                    <option value="receiving" {{ old('inspection_type', $inspection->inspection_type) == 'receiving' ? 'selected' : '' }}>فحص استلام مواد خام</option>
                                    <option value="in_process" {{ old('inspection_type', $inspection->inspection_type) == 'in_process' ? 'selected' : '' }}>فحص أثناء الإنتاج</option>
                                    <option value="final" {{ old('inspection_type', $inspection->inspection_type) == 'final' ? 'selected' : '' }}>فحص نهائي</option>
                                    <option value="random" {{ old('inspection_type', $inspection->inspection_type) == 'random' ? 'selected' : '' }}>فحص عشوائي</option>
                                    <option value="customer_complaint" {{ old('inspection_type', $inspection->inspection_type) == 'customer_complaint' ? 'selected' : '' }}>فحص شكوى عميل</option>
                                </select>
                                @error('inspection_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">المورد</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">اختر المورد (اختياري)</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $inspection->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->aname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">تاريخ الفحص</label>
                                <input type="date" name="inspection_date" 
                                       class="form-control @error('inspection_date') is-invalid @enderror" 
                                       value="{{ old('inspection_date', $inspection->inspection_date?->format('Y-m-d')) }}" required>
                                @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label required">الكمية المفحوصة</label>
                                <input type="number" step="0.001" name="quantity_inspected" 
                                       class="form-control @error('quantity_inspected') is-invalid @enderror" 
                                       value="{{ old('quantity_inspected', $inspection->quantity_inspected) }}" required>
                                @error('quantity_inspected')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label required">الكمية الناجحة</label>
                                <input type="number" step="0.001" name="pass_quantity" 
                                       class="form-control @error('pass_quantity') is-invalid @enderror" 
                                       value="{{ old('pass_quantity', $inspection->pass_quantity) }}" required>
                                @error('pass_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label required">الكمية الفاشلة</label>
                                <input type="number" step="0.001" name="fail_quantity" 
                                       class="form-control @error('fail_quantity') is-invalid @enderror" 
                                       value="{{ old('fail_quantity', $inspection->fail_quantity) }}" required>
                                @error('fail_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">العيوب المكتشفة</label>
                                <textarea name="defects_found" rows="3" class="form-control" 
                                          placeholder="اذكر العيوب التي تم اكتشافها...">{{ old('defects_found', $inspection->defects_found) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">ملاحظات المفتش</label>
                                <textarea name="inspector_notes" rows="3" class="form-control" 
                                          placeholder="ملاحظات إضافية...">{{ old('inspector_notes', $inspection->inspector_notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">النتيجة والإجراء</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">نتيجة الفحص</label>
                            <select name="result" class="form-select @error('result') is-invalid @enderror" required>
                                <option value="pass" {{ old('result', $inspection->result) == 'pass' ? 'selected' : '' }}>نجح</option>
                                <option value="fail" {{ old('result', $inspection->result) == 'fail' ? 'selected' : '' }}>فشل</option>
                                <option value="conditional" {{ old('result', $inspection->result) == 'conditional' ? 'selected' : '' }}>مشروط</option>
                            </select>
                            @error('result')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">الإجراء المتخذ</label>
                            <select name="action_taken" class="form-select @error('action_taken') is-invalid @enderror" required>
                                <option value="accepted" {{ old('action_taken', $inspection->action_taken) == 'accepted' ? 'selected' : '' }}>مقبول</option>
                                <option value="rejected" {{ old('action_taken', $inspection->action_taken) == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="rework" {{ old('action_taken', $inspection->action_taken) == 'rework' ? 'selected' : '' }}>إعادة عمل</option>
                                <option value="conditional_accept" {{ old('action_taken', $inspection->action_taken) == 'conditional_accept' ? 'selected' : '' }}>قبول مشروط</option>
                                <option value="pending_review" {{ old('action_taken', $inspection->action_taken) == 'pending_review' ? 'selected' : '' }}>انتظار مراجعة</option>
                            </select>
                            @error('action_taken')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">رقم الدفعة</label>
                            <input type="text" name="batch_number" class="form-control" 
                                   value="{{ old('batch_number', $inspection->batch_number) }}" placeholder="اختياري">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">المرفقات</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">صور، تقارير، شهادات</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save me-2"></i>تحديث الفحص
                    </button>
                    <a href="{{ route('quality.inspections.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);
    console.log('Method input value:', document.querySelector('input[name="_method"]').value);
    console.log('Inspection ID:', '{{ $inspection->id }}');
});
</script>
@endsection