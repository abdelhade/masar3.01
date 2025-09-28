@extends('admin.dashboard')

@section('title', 'عرض الخدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-eye me-2"></i>
                        عرض الخدمة: {{ $service->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.services.edit', $service) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-1"></i>
                            تعديل
                        </a>
                        <a href="{{ route('services.services.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        المعلومات الأساسية
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold" style="width: 30%;">اسم الخدمة:</td>
                                            <td>{{ $service->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">كود الخدمة:</td>
                                            <td><span class="badge bg-primary">{{ $service->code }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">تصنيف الخدمة:</td>
                                            <td>
                                                @if($service->serviceType)
                                                    <span class="badge bg-primary">{{ $service->serviceType->name }}</span>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">وحدة الخدمة:</td>
                                            <td>
                                                @if($service->serviceUnit)
                                                    <span class="badge bg-info">{{ $service->serviceUnit->name }}</span>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">السعر:</td>
                                            <td class="text-success fw-bold">{{ number_format($service->price, 2) }} ريال</td>
                                        </tr>
                                        @if($service->cost)
                                        <tr>
                                            <td class="fw-bold">التكلفة:</td>
                                            <td class="text-danger">{{ number_format($service->cost, 2) }} ريال</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold">الحالة:</td>
                                            <td>
                                                @if($service->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">خاضع للضريبة:</td>
                                            <td>
                                                @if($service->is_taxable)
                                                    <span class="badge bg-warning">نعم</span>
                                                @else
                                                    <span class="badge bg-secondary">لا</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Service Image and Description -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-image me-2"></i>
                                     الوصف
                                    </h5>
                                </div>
                                <div class="card-body">

                                    @if($service->description)
                                        <div class="mt-3">
                                            <h6 class="fw-bold">الوصف:</h6>
                                            <p class="text-muted">{{ $service->description }}</p>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>






                    <!-- Bookings Summary -->
                    @if($service->bookings && $service->bookings->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calendar me-2"></i>
                                        ملخص الحجوزات
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-primary">{{ $service->bookings->count() }}</h4>
                                                <p class="text-muted mb-0">إجمالي الحجوزات</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-success">{{ $service->bookings->where('status', 'confirmed')->count() }}</h4>
                                                <p class="text-muted mb-0">مؤكدة</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-warning">{{ $service->bookings->where('status', 'pending')->count() }}</h4>
                                                <p class="text-muted mb-0">في الانتظار</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-danger">{{ $service->bookings->where('status', 'cancelled')->count() }}</h4>
                                                <p class="text-muted mb-0">ملغية</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">
                            تم الإنشاء: {{ $service->created_at->format('Y-m-d H:i') }}
                        </small>
                        <small class="text-muted">
                            آخر تحديث: {{ $service->updated_at->format('Y-m-d H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
