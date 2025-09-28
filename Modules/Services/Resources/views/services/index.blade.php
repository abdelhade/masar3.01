@extends('admin.dashboard')

@section('title', 'إدارة الخدمات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-cogs me-2"></i>
                        إدارة الخدمات
                    </h3>
                    <a href="{{ route('services.services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة خدمة جديدة
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">البحث</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="البحث في اسم أو كود الخدمة">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="service_type_id">نوع الخدمة</label>
                                <select class="form-control" id="service_type_id" name="service_type_id">
                                    <option value="">جميع الأنواع</option>
                                    @foreach(\Modules\Services\Models\ServiceType::orderBy('name')->get() as $type)
                                        <option value="{{ $type->id }}" {{ request('service_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-secondary d-block w-100" onclick="applyFilters()">
                                    <i class="fas fa-search me-1"></i>
                                    بحث
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-white">#</th>
                                    <th class="text-white">اسم الخدمة</th>
                                    <th class="text-white">كود الخدمة</th>
                                    <th class="text-white">التصنيف</th>
                                    <th class="text-white">الوحدة</th>
                                    <th class="text-white">التكلفة</th>
                                    <th class="text-white">السعر</th>
                                    {{-- <th class="text-white">الحالة</th> --}}
                                    <th class="text-white">التفعيل</th>
                                    <th class="text-white">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>{{ $service->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($service->image)
                                                    <img src="{{ asset('storage/' . $service->image) }}" 
                                                         alt="{{ $service->name }}" 
                                                         class="rounded me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-cogs text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $service->name }}</strong>
                                                    @if($service->description)
                                                        <br><small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $service->code }}</span>
                                        </td>
                                        <td>
                                            @if($service->serviceType)
                                                <span class="badge bg-primary">{{ $service->serviceType->name }}</span>
                                            @else
                                                <span class="text-muted">لا يوجد تصنيف</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->serviceUnit)
                                                <span class="badge bg-info">{{ $service->serviceUnit->name }}</span>
                                            @else
                                                <span class="text-muted">لا يوجد وحدة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($service->cost, 2) }} ر.س</strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($service->price, 2) }} ر.س</strong>
                                        </td>
                                        {{-- <td>
                                            @if($service->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td> --}}
                                        <td class="text-center align-middle">
                                            <form action="{{ route('services.services.toggle-status', $service) }}" 
                                                  method="POST" class="d-inline-block w-100">
                                                @csrf
                                                @method('PATCH')
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="form-check form-switch mb-1">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="toggle-{{ $service->id }}" 
                                                               {{ $service->is_active ? 'checked' : '' }}
                                                               onchange="this.form.submit()">
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $service->is_active ? 'مفعل' : 'غير مفعل' }}
                                                    </small>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('services.services.show', $service) }}" 
                                                   class="btn btn-sm btn-outline-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('services.services.edit', $service) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('services.services.destroy', $service) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>لا توجد خدمات متاحة</p>
                                                <a href="{{ route('services.services.create') }}" class="btn btn-primary">
                                                    إضافة خدمة جديدة
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($services->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $services->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = document.getElementById('search').value;
    const serviceTypeId = document.getElementById('service_type_id').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (serviceTypeId) params.append('service_type_id', serviceTypeId);
    
    window.location.href = '{{ route("services.services.index") }}?' + params.toString();
}

// Auto-submit on Enter key
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>
@endpush
