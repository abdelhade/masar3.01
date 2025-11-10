@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-barcode me-2"></i>
                        تفاصيل الدفعة: {{ $batch->batch_number }}
                    </h4>
                    <div>
                        <a href="{{ route('quality.batches.edit', $batch) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('quality.batches.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- معلومات أساسية -->
                        <div class="col-12 mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>معلومات أساسية
                            </h5>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>رقم الدفعة:</strong>
                            <p class="fs-5 text-primary">{{ $batch->batch_number }}</p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>الصنف:</strong>
                            <p>{{ $batch->item?->name ?? 'غير محدد' }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>الحالة:</strong>
                            <p>
                                <span class="badge bg-{{ $batch->status == 'active' ? 'success' : ($batch->status == 'consumed' ? 'secondary' : 'danger') }} fs-6">
                                    @switch($batch->status)
                                        @case('active') نشط @break
                                        @case('consumed') مستهلك @break
                                        @case('expired') منتهي الصلاحية @break
                                        @case('rejected') مرفوض @break
                                        @default {{ $batch->status }}
                                    @endswitch
                                </span>
                            </p>
                        </div>

                        <!-- التواريخ -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar me-2"></i>التواريخ
                            </h5>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>تاريخ الإنتاج:</strong>
                            <p>{{ $batch->production_date?->format('Y-m-d') }}</p>
                        </div>

                        @if($batch->expiry_date)
                        <div class="col-md-4 mb-3">
                            <strong>تاريخ الصلاحية:</strong>
                            <p class="{{ $batch->expiry_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                {{ $batch->expiry_date->format('Y-m-d') }}
                                @if($batch->expiry_date->isPast())
                                    <span class="badge bg-danger">منتهي</span>
                                @elseif($batch->expiry_date->diffInDays(now()) < 30)
                                    <span class="badge bg-warning">ينتهي قريباً</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>الأيام المتبقية:</strong>
                            <p>
                                @if($batch->expiry_date->isPast())
                                    <span class="text-danger fw-bold">منتهي منذ {{ $batch->expiry_date->diffInDays(now()) }} يوم</span>
                                @else
                                    <span class="{{ $batch->expiry_date->diffInDays(now()) < 30 ? 'text-warning fw-bold' : '' }}">
                                        {{ $batch->expiry_date->diffInDays(now()) }} يوم
                                    </span>
                                @endif
                            </p>
                        </div>
                        @endif

                        <!-- الكمية والجودة -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-boxes me-2"></i>الكمية والجودة
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($batch->quantity, 3) }}</h3>
                                    <p class="mb-0">الكمية</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card bg-{{ $batch->quality_status == 'passed' ? 'success' : ($batch->quality_status == 'failed' ? 'danger' : 'warning') }} text-white">
                                <div class="card-body text-center">
                                    <h4>
                                        @switch($batch->quality_status)
                                            @case('passed') ناجح ✓ @break
                                            @case('failed') راسب ✗ @break
                                            @case('conditional') مشروط @break
                                            @case('quarantine') حجر صحي @break
                                            @default {{ $batch->quality_status }}
                                        @endswitch
                                    </h4>
                                    <p class="mb-0">حالة الجودة</p>
                                </div>
                            </div>
                        </div>

                        <!-- الموقع والمورد -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>الموقع والمورد
                            </h5>
                        </div>

                        @if($batch->supplier)
                        <div class="col-md-4 mb-3">
                            <strong>المورد:</strong>
                            <p>{{ $batch->supplier->name }}</p>
                        </div>
                        @endif

                        @if($batch->warehouse)
                        <div class="col-md-4 mb-3">
                            <strong>المستودع:</strong>
                            <p>{{ $batch->warehouse->name }}</p>
                        </div>
                        @endif

                        @if($batch->location)
                        <div class="col-md-4 mb-3">
                            <strong>الموقع:</strong>
                            <p>{{ $batch->location }}</p>
                        </div>
                        @endif

                        @if($batch->notes)
                        <div class="col-12 mb-3">
                            <strong>ملاحظات:</strong>
                            <p class="alert alert-info">{{ $batch->notes }}</p>
                        </div>
                        @endif

                        <!-- معلومات إضافية -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info me-2"></i>معلومات إضافية
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>تاريخ الإنشاء:</strong>
                            <p>{{ $batch->created_at?->format('Y-m-d H:i') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>آخر تحديث:</strong>
                            <p>{{ $batch->updated_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

