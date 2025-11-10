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
                        تعديل تقرير عدم المطابقة: {{ $ncr->ncr_number }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('quality.ncr.update', $ncr) }}" method="POST">
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
                                                {{ old('item_id', $ncr->item_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الحالة -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="open" {{ old('status', $ncr->status) == 'open' ? 'selected' : '' }}>مفتوح</option>
                                    <option value="in_progress" {{ old('status', $ncr->status) == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                    <option value="closed" {{ old('status', $ncr->status) == 'closed' ? 'selected' : '' }}>مغلق</option>
                                    <option value="cancelled" {{ old('status', $ncr->status) == 'cancelled' ? 'selected' : '' }}>ملغى</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- وصف المشكلة -->
                            <div class="col-md-12 mb-3">
                                <label for="problem_description" class="form-label">وصف المشكلة <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('problem_description') is-invalid @enderror" 
                                          id="problem_description" name="problem_description" rows="3" required>{{ old('problem_description', $ncr->problem_description) }}</textarea>
                                @error('problem_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- السبب الجذري -->
                            <div class="col-md-12 mb-3">
                                <label for="root_cause" class="form-label">السبب الجذري</label>
                                <textarea class="form-control @error('root_cause') is-invalid @enderror" 
                                          id="root_cause" name="root_cause" rows="3">{{ old('root_cause', $ncr->root_cause) }}</textarea>
                                @error('root_cause')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الإجراء الفوري -->
                            <div class="col-md-12 mb-3">
                                <label for="immediate_action" class="form-label">الإجراء الفوري <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('immediate_action') is-invalid @enderror" 
                                          id="immediate_action" name="immediate_action" rows="3" required>{{ old('immediate_action', $ncr->immediate_action) }}</textarea>
                                @error('immediate_action')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- التكلفة الفعلية -->
                            <div class="col-md-6 mb-3">
                                <label for="actual_cost" class="form-label">التكلفة الفعلية</label>
                                <input type="number" step="0.01" class="form-control @error('actual_cost') is-invalid @enderror" 
                                       id="actual_cost" name="actual_cost" 
                                       value="{{ old('actual_cost', $ncr->actual_cost) }}">
                                @error('actual_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ملاحظات الإغلاق -->
                            <div class="col-md-12 mb-3">
                                <label for="closure_notes" class="form-label">ملاحظات الإغلاق</label>
                                <textarea class="form-control @error('closure_notes') is-invalid @enderror" 
                                          id="closure_notes" name="closure_notes" rows="3">{{ old('closure_notes', $ncr->closure_notes) }}</textarea>
                                @error('closure_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('quality.ncr.show', $ncr) }}" class="btn btn-secondary">
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

