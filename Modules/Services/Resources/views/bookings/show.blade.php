@extends('admin.dashboard')

@section('title', 'تفاصيل حجز الخدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-eye me-2"></i>
                        تفاصيل حجز الخدمة
                    </h3>
                    <div class="btn-group">
                        <a href="{{ route('services.bookings.edit', $booking) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            تعديل
                        </a>
                        <a href="{{ route('services.bookings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة إلى القائمة
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Service Information -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-cogs me-2"></i>
                                        معلومات الخدمة
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>اسم الخدمة:</strong></td>
                                            <td>{{ $booking->service->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>كود الخدمة:</strong></td>
                                            <td><span class="badge bg-info">{{ $booking->service->code }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>نوع الخدمة:</strong></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ match($booking->service->service_type) {
                                                        'general' => 'عام',
                                                        'consultation' => 'استشارة',
                                                        'maintenance' => 'صيانة',
                                                        'repair' => 'إصلاح',
                                                        'installation' => 'تركيب',
                                                        'training' => 'تدريب',
                                                        'other' => 'أخرى',
                                                        default => $booking->service->service_type
                                                    } }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>مدة الخدمة:</strong></td>
                                            <td>60 دقيقة</td>
                                        </tr>
                                        <tr>
                                            <td><strong>السعر:</strong></td>
                                            <td><strong class="text-success">{{ number_format($booking->price, 2) }} ر.س</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Information -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        معلومات الحجز
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>تاريخ الحجز:</strong></td>
                                            <td>{{ $booking->booking_date->format('Y-m-d') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>وقت البداية:</strong></td>
                                            <td>{{ $booking->start_time->format('H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>وقت النهاية:</strong></td>
                                            <td>{{ $booking->end_time ? $booking->end_time->format('H:i') : 'غير محدد' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>الحالة:</strong></td>
                                            <td>
                                                @if($booking->is_completed)
                                                    <span class="badge bg-success">مكتمل</span>
                                                @elseif($booking->is_cancelled)
                                                    <span class="badge bg-danger">ملغي</span>
                                                @else
                                                    <span class="badge bg-warning">في الانتظار</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>تاريخ الإنشاء:</strong></td>
                                            <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user me-2"></i>
                                        معلومات العميل
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>اسم العميل:</strong></td>
                                            <td>{{ $booking->customer->aname }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>نوع الحساب:</strong></td>
                                            <td>{{ $booking->customer->atype ?? 'غير محدد' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-tie me-2"></i>
                                        معلومات الموظف
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($booking->employee)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>اسم الموظف:</strong></td>
                                                <td>{{ $booking->employee->aname }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>نوع الحساب:</strong></td>
                                                <td>{{ $booking->employee->atype ?? 'غير محدد' }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">لم يتم تعيين موظف لهذا الحجز</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    @if($booking->notes || $booking->customer_notes)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-sticky-note me-2"></i>
                                            الملاحظات
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($booking->notes)
                                            <div class="mb-3">
                                                <h6><strong>ملاحظات عامة:</strong></h6>
                                                <p class="text-muted">{{ $booking->notes }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($booking->customer_notes)
                                            <div class="mb-3">
                                                <h6><strong>ملاحظات العميل:</strong></h6>
                                                <p class="text-muted">{{ $booking->customer_notes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Cancellation Information -->
                    @if($booking->is_cancelled)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-times-circle me-2"></i>
                                            معلومات الإلغاء
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>تاريخ الإلغاء:</strong></td>
                                                <td>{{ $booking->cancelled_at ? $booking->cancelled_at->format('Y-m-d H:i') : 'غير محدد' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>سبب الإلغاء:</strong></td>
                                                <td>{{ $booking->cancellation_reason ?? 'لم يتم تحديد السبب' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-center gap-2">
                                @if(!$booking->is_completed && !$booking->is_cancelled)
                                    <form action="{{ route('services.bookings.complete', $booking) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success" 
                                                onclick="return confirm('هل أنت متأكد من إكمال هذا الحجز؟')">
                                            <i class="fas fa-check me-1"></i>
                                            إكمال الحجز
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('services.bookings.cancel', $booking) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('هل أنت متأكد من إلغاء هذا الحجز؟')">
                                            <i class="fas fa-times me-1"></i>
                                            إلغاء الحجز
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('services.bookings.edit', $booking) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i>
                                    تعديل
                                </a>
                                
                                <form action="{{ route('services.bookings.destroy', $booking) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الحجز؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-1"></i>
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
