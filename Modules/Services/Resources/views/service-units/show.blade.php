@extends('admin.dashboard')

@section('title', 'تفاصيل وحدة الخدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cube me-2"></i>
                        تفاصيل وحدة الخدمة: {{ $serviceUnit->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.service-units.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                        <a href="{{ route('services.service-units.edit', $serviceUnit) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>
                            تعديل
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">الكود:</label>
                                        <div>
                                            <span class="badge bg-secondary fs-6">{{ $serviceUnit->code }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">الاسم:</label>
                                        <div class="fs-5">{{ $serviceUnit->name }}</div>
                                    </div>
                                </div>
                            </div>

                           

                            <div class="row">

                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">الحالة:</label>
                                        <div>
                                            @if($serviceUnit->is_active)
                                                <span class="badge bg-success fs-6">نشط</span>
                                            @else
                                                <span class="badge bg-secondary fs-6">غير نشط</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">عدد الخدمات:</label>
                                        <div>
                                            <span class="badge bg-primary fs-6">{{ $serviceUnit->services->count() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">تاريخ الإنشاء:</label>
                                        <div class="text-muted">{{ $serviceUnit->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">آخر تحديث:</label>
                                        <div class="text-muted">{{ $serviceUnit->updated_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-cogs me-2"></i>
                                        الإجراءات
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('services.service-units.edit', $serviceUnit) }}" 
                                           class="btn btn-warning">
                                            <i class="fas fa-edit me-1"></i>
                                            تعديل وحدة الخدمة
                                        </a>
                                        
                                        <form action="{{ route('services.service-units.destroy', $serviceUnit) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('هل أنت متأكد من حذف وحدة الخدمة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="fas fa-trash me-1"></i>
                                                حذف وحدة الخدمة
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            
                        </div>
                    </div>

                    @if($serviceUnit->services->count() > 0)
                        <hr>
                        <h5 class="mb-3">
                            <i class="fas fa-list me-2"></i>
                            الخدمات المرتبطة ({{ $serviceUnit->services->count() }})
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>الكود</th>
                                        <th>اسم الخدمة</th>
                                        <th>نوع الخدمة</th>
                                        <th>السعر</th>
                                        <th>التكلفة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceUnit->services as $service)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $service->code }}</span>
                                            </td>
                                            <td>{{ $service->name }}</td>
                                            <td>
                                                @if($service->serviceType)
                                                    <span class="badge bg-info">{{ $service->serviceType->name }}</span>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold">{{ number_format($service->price, 2) }} ر.س</span>
                                            </td>
                                            <td>
                                                <span class="text-info">{{ number_format($service->cost, 2) }} ر.س</span>
                                            </td>
                                            <td>
                                                @if($service->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-secondary">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('services.services.show', $service) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="عرض الخدمة">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <hr>
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">لا توجد خدمات مرتبطة بهذه الوحدة</h6>
                            <p class="text-muted">يمكنك إضافة خدمات جديدة وربطها بهذه الوحدة</p>
                            <a href="{{ route('services.services.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                إضافة خدمة جديدة
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
