@extends('admin.dashboard')

@section('title', 'أنواع الخدمات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-tags me-2"></i>
                        أنواع الخدمات
                    </h3>
                    <a href="{{ route('services.service-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة نوع خدمة جديد
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($serviceTypes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>الكود</th>
                                        <th>الاسم</th>
                                        <th>الفرع</th>
                                        <th>عدد الخدمات</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceTypes as $serviceType)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $serviceType->code }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $serviceType->name }}</strong>
                                            </td>
                                            <td>
                                                @if($serviceType->branch)
                                                    <span class="badge bg-info">{{ $serviceType->branch->name }}</span>
                                                @else
                                                    <span class="text-muted">جميع الفروع</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $serviceType->services_count ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $serviceType->created_at->format('Y-m-d H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('services.service-types.show', $serviceType) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('services.service-types.edit', $serviceType) }}" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('services.service-types.destroy', $serviceType) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف نوع الخدمة؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $serviceTypes->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد أنواع خدمات</h5>
                            <p class="text-muted">ابدأ بإضافة نوع خدمة جديد</p>
                            <a href="{{ route('services.service-types.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                إضافة نوع خدمة جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
