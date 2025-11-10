@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-plus-circle me-2"></i>تقرير عدم مطابقة جديد (NCR)</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.ncr.index') }}">تقارير NCR</a></li>
                    <li class="breadcrumb-item active">جديد</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('quality.ncr.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">معلومات عدم المطابقة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">الصنف</label>
                                <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                    <option value="">اختر الصنف</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">الفحص المرتبط</label>
                                <select name="inspection_id" class="form-select">
                                    <option value="">اختر الفحص (اختياري)</option>
                                    @foreach($inspections as $inspection)
                                        <option value="{{ $inspection->id }}">
                                            {{ $inspection->inspection_number }} - {{ $inspection->item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الدفعة</label>
                                <input type="text" name="batch_number" class="form-control" 
                                       placeholder="اختياري">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">الكمية المتأثرة</label>
                                <input type="number" step="0.001" name="affected_quantity" 
                                       class="form-control @error('affected_quantity') is-invalid @enderror" required>
                                @error('affected_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">المصدر</label>
                                <select name="source" class="form-select @error('source') is-invalid @enderror" required>
                                    <option value="">اختر المصدر</option>
                                    <option value="receiving_inspection">فحص استلام</option>
                                    <option value="in_process">أثناء الإنتاج</option>
                                    <option value="final_inspection">فحص نهائي</option>
                                    <option value="customer_complaint">شكوى عميل</option>
                                    <option value="internal_audit">تدقيق داخلي</option>
                                    <option value="supplier_notification">إشعار مورد</option>
                                </select>
                                @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">تاريخ الاكتشاف</label>
                                <input type="date" name="detected_date" 
                                       class="form-control @error('detected_date') is-invalid @enderror" 
                                       value="{{ date('Y-m-d') }}" required>
                                @error('detected_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label required">وصف المشكلة</label>
                                <textarea name="problem_description" rows="4" 
                                          class="form-control @error('problem_description') is-invalid @enderror" 
                                          placeholder="اشرح المشكلة بالتفصيل..." required></textarea>
                                @error('problem_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">الإجراء الفوري المتخذ</label>
                                <textarea name="immediate_action" rows="3" class="form-control" 
                                          placeholder="ما هو الإجراء الفوري الذي تم اتخاذه؟"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">التصنيف والأولوية</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">مستوى الخطورة</label>
                            <select name="severity" class="form-select @error('severity') is-invalid @enderror" required>
                                <option value="minor">ثانوي</option>
                                <option value="major">رئيسي</option>
                                <option value="critical">حرج</option>
                            </select>
                            @error('severity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التكلفة المقدرة</label>
                            <input type="number" step="0.01" name="estimated_cost" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التصرف</label>
                            <select name="disposition" class="form-select">
                                <option value="">سيتم تحديده</option>
                                <option value="rework">إعادة عمل</option>
                                <option value="scrap">إتلاف</option>
                                <option value="return_to_supplier">إرجاع للمورد</option>
                                <option value="use_as_is">استخدام كما هو</option>
                                <option value="repair">إصلاح</option>
                                <option value="downgrade">تخفيض الدرجة</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">تعيين لـ</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">اختر المسؤول</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">تاريخ الإغلاق المستهدف</label>
                            <input type="date" name="target_closure_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">المرفقات</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">يمكنك إرفاق صور أو مستندات</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-save me-2"></i>إنشاء تقرير NCR
                    </button>
                    <a href="{{ route('quality.ncr.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

