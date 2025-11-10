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
                        تعديل الفحص: {{ $inspection->inspection_number }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.inspections.update', $inspection) }}" method="POST">
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
                                                {{ old('item_id', $inspection->item_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- نوع الفحص -->
                            <div class="col-md-6 mb-3">
                                <label for="inspection_type" class="form-label">نوع الفحص <span class="text-danger">*</span></label>
                                <select class="form-select @error('inspection_type') is-invalid @enderror" 
                                        id="inspection_type" name="inspection_type" required>
                                    <option value="">-- اختر نوع الفحص --</option>
                                    <option value="receiving" {{ old('inspection_type', $inspection->inspection_type) == 'receiving' ? 'selected' : '' }}>استلام</option>
                                    <option value="in_process" {{ old('inspection_type', $inspection->inspection_type) == 'in_process' ? 'selected' : '' }}>أثناء الإنتاج</option>
                                    <option value="final" {{ old('inspection_type', $inspection->inspection_type) == 'final' ? 'selected' : '' }}>نهائي</option>
                                    <option value="random" {{ old('inspection_type', $inspection->inspection_type) == 'random' ? 'selected' : '' }}>عشوائي</option>
                                    <option value="customer_complaint" {{ old('inspection_type', $inspection->inspection_type) == 'customer_complaint' ? 'selected' : '' }}>شكوى عميل</option>
                                </select>
                                @error('inspection_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تاريخ الفحص -->
                            <div class="col-md-6 mb-3">
                                <label for="inspection_date" class="form-label">تاريخ الفحص <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('inspection_date') is-invalid @enderror" 
                                       id="inspection_date" name="inspection_date" 
                                       value="{{ old('inspection_date', $inspection->inspection_date?->format('Y-m-d')) }}" required>
                                @error('inspection_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- رقم الدفعة -->
                            <div class="col-md-6 mb-3">
                                <label for="batch_number" class="form-label">رقم الدفعة</label>
                                <input type="text" class="form-control @error('batch_number') is-invalid @enderror" 
                                       id="batch_number" name="batch_number" 
                                       value="{{ old('batch_number', $inspection->batch_number) }}">
                                @error('batch_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الكمية المفحوصة -->
                            <div class="col-md-4 mb-3">
                                <label for="quantity_inspected" class="form-label">الكمية المفحوصة <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" class="form-control @error('quantity_inspected') is-invalid @enderror" 
                                       id="quantity_inspected" name="quantity_inspected" 
                                       value="{{ old('quantity_inspected', $inspection->quantity_inspected) }}" required>
                                @error('quantity_inspected')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- كمية النجاح -->
                            <div class="col-md-4 mb-3">
                                <label for="pass_quantity" class="form-label">كمية النجاح <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" class="form-control @error('pass_quantity') is-invalid @enderror" 
                                       id="pass_quantity" name="pass_quantity" 
                                       value="{{ old('pass_quantity', $inspection->pass_quantity) }}" required>
                                @error('pass_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- كمية الفشل -->
                            <div class="col-md-4 mb-3">
                                <label for="fail_quantity" class="form-label">كمية الفشل <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" class="form-control @error('fail_quantity') is-invalid @enderror" 
                                       id="fail_quantity" name="fail_quantity" 
                                       value="{{ old('fail_quantity', $inspection->fail_quantity) }}" required>
                                @error('fail_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- النتيجة -->
                            <div class="col-md-6 mb-3">
                                <label for="result" class="form-label">النتيجة <span class="text-danger">*</span></label>
                                <select class="form-select @error('result') is-invalid @enderror" 
                                        id="result" name="result" required>
                                    <option value="">-- اختر النتيجة --</option>
                                    <option value="pass" {{ old('result', $inspection->result) == 'pass' ? 'selected' : '' }}>ناجح</option>
                                    <option value="fail" {{ old('result', $inspection->result) == 'fail' ? 'selected' : '' }}>راسب</option>
                                    <option value="conditional" {{ old('result', $inspection->result) == 'conditional' ? 'selected' : '' }}>مشروط</option>
                                </select>
                                @error('result')
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
                                        <option value="{{ $supplier->id }}" 
                                                {{ old('supplier_id', $inspection->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- العيوب المكتشفة -->
                            <div class="col-md-12 mb-3">
                                <label for="defects_found" class="form-label">العيوب المكتشفة</label>
                                <textarea class="form-control @error('defects_found') is-invalid @enderror" 
                                          id="defects_found" name="defects_found" rows="3">{{ old('defects_found', $inspection->defects_found) }}</textarea>
                                @error('defects_found')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ملاحظات المفتش -->
                            <div class="col-md-12 mb-3">
                                <label for="inspector_notes" class="form-label">ملاحظات المفتش</label>
                                <textarea class="form-control @error('inspector_notes') is-invalid @enderror" 
                                          id="inspector_notes" name="inspector_notes" rows="3">{{ old('inspector_notes', $inspection->inspector_notes) }}</textarea>
                                @error('inspector_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الإجراء المتخذ -->
                            <div class="col-md-12 mb-3">
                                <label for="action_taken" class="form-label">الإجراء المتخذ <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('action_taken') is-invalid @enderror" 
                                          id="action_taken" name="action_taken" rows="3" required>{{ old('action_taken', $inspection->action_taken) }}</textarea>
                                @error('action_taken')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('quality.inspections.show', $inspection) }}" class="btn btn-secondary">
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

