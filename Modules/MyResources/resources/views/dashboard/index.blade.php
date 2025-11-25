@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary">
                <div class="card-body">
                    <h2 class="mb-0">
                        <i class="fas fa-cubes me-2"></i>
                        لوحة تحكم الموارد
                    </h2>
                    <p class="mb-0 mt-2">لوحة تحكم شاملة لمتابعة جميع الموارد والتكاليف</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Resources -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">إجمالي الموارد</h6>
                            <h3 class="mb-0">{{ $totalResources }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> نشطة: {{ $activeResources }}
                            </small>
                        </div>
                        <div class="text-primary" style="font-size: 3rem;">
                            <i class="fas fa-cubes"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Assignments -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">التعيينات النشطة</h6>
                            <h3 class="mb-0">{{ $activeAssignments }}</h3>
                            <small class="text-info">
                                <i class="fas fa-clock"></i> مجدولة: {{ $scheduledAssignments }}
                            </small>
                        </div>
                        <div class="text-info" style="font-size: 3rem;">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Maintenance -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">صيانة قادمة</h6>
                            <h3 class="mb-0">{{ $upcomingMaintenance->count() }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-calendar-alt"></i> خلال 7 أيام
                            </small>
                        </div>
                        <div class="text-warning" style="font-size: 3rem;">
                            <i class="fas fa-wrench"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resources by Status -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">حسب الحالة</h6>
                            <h3 class="mb-0">{{ $resourcesByStatus->count() }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-chart-pie"></i> حالات مختلفة
                            </small>
                        </div>
                        <div class="text-secondary" style="font-size: 3rem;">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="row">
        <!-- Resources by Category -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        الموارد حسب التصنيف
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>التصنيف</th>
                                    <th class="text-center">العدد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resourcesByCategory as $category)
                                <tr>
                                    <td>{{ $category->category->name ?? $category->category->name_ar ?? 'غير محدد' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $category->count }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">لا توجد موارد</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resources by Status -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        الموارد حسب الحالة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الحالة</th>
                                    <th class="text-center">العدد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resourcesByStatus as $status)
                                <tr>
                                    <td>
                                        <span >
                                            {{ $status->status->name ?? $status->status->name_ar ?? 'غير محدد' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $status->count }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">لا توجد حالات</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Maintenance & Recent Assignments -->
    <div class="row">
        <!-- Upcoming Maintenance -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-wrench me-2"></i>
                        صيانة قادمة (خلال 7 أيام)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>تاريخ الصيانة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingMaintenance as $resource)
                                <tr>
                                    <td>
                                        <strong>{{ $resource->code }}</strong><br>
                                        <small class="text-muted">{{ $resource->name }}</small>
                                    </td>
                                    <td>{{ $resource->next_maintenance_date?->format('Y-m-d') ?? '---' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $resource->status->color ?? 'secondary' }}">
                                            {{ $resource->status->name ?? $resource->status->name_ar ?? 'غير محدد' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">لا توجد صيانة قادمة</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Assignments -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر التعيينات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>المورد</th>
                                    <th>المشروع</th>
                                    <th>تاريخ البدء</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAssignments as $assignment)
                                <tr>
                                    <td>
                                        @if($assignment->resource)
                                            <strong>{{ $assignment->resource->code }}</strong><br>
                                            <small class="text-muted">{{ $assignment->resource->name }}</small>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->project->name ?? '---' }}</td>
                                    <td>{{ $assignment->start_date?->format('Y-m-d') ?? '---' }}</td>
                                    <td>
                                        @if($assignment->status instanceof \Modules\MyResources\Enums\ResourceAssignmentStatus)
                                            <span class="badge bg-{{ $assignment->status->color() }}">
                                                {{ $assignment->status->label() }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ $assignment->status ?? '---' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">لا توجد تعيينات</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        إجراءات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('myresources.index') }}" class="btn btn-primary w-100">
                                <i class="fas fa-list me-2"></i>
                                قائمة الموارد
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('myresources.create') }}" class="btn btn-success w-100">
                                <i class="fas fa-plus-circle me-2"></i>
                                مورد جديد
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('myresources.assignments.create') }}" class="btn btn-info w-100">
                                <i class="fas fa-tasks me-2"></i>
                                تعيين جديد
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('myresources.categories.index') }}" class="btn btn-warning w-100">
                                <i class="fas fa-folder me-2"></i>
                                التصنيفات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

