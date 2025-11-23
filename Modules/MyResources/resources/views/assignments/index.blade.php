@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">تعيينات الموارد</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title">قائمة التعيينات</h4>
                        <div>
                            <a href="{{ route('myresources.assignments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة تعيين جديد
                            </a>
                            <a href="{{ route('myresources.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> إدارة الموارد
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المورد</th>
                                    <th>المشروع</th>
                                    <th>تاريخ البدء</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>الحالة</th>
                                    <th>النوع</th>
                                    <th>التكلفة اليومية</th>
                                    <th>تم التعيين بواسطة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->id }}</td>
                                        <td>
                                            <strong>{{ $assignment->resource->name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $assignment->resource->code ?? '' }}</small>
                                        </td>
                                        <td>{{ $assignment->project->name ?? 'N/A' }}</td>
                                        <td>{{ $assignment->start_date?->format('Y-m-d') ?? 'N/A' }}</td>
                                        <td>{{ $assignment->end_date?->format('Y-m-d') ?? 'N/A' }}</td>
                                        <td>
                                            @if($assignment->status instanceof \Modules\MyResources\Enums\ResourceAssignmentStatus)
                                                <span class="badge bg-{{ $assignment->status->color() }}">
                                                    {{ $assignment->status->label() }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment->assignment_type instanceof \Modules\MyResources\Enums\AssignmentType)
                                                {{ $assignment->assignment_type->label() }}
                                            @else
                                                @php
                                                    $typeLabels = [
                                                        'current' => 'حالي',
                                                        'upcoming' => 'قادم',
                                                        'past' => 'سابق'
                                                    ];
                                                    $typeValue = $assignment->assignment_type;
                                                    $typeLabel = $typeLabels[$typeValue] ?? $typeValue;
                                                @endphp
                                                {{ $typeLabel }}
                                            @endif
                                        </td>
                                        <td>{{ $assignment->daily_cost ? number_format($assignment->daily_cost, 2) : 'N/A' }}</td>
                                        <td>{{ $assignment->assignedBy->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('myresources.assignments.edit', $assignment) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('myresources.assignments.destroy', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">لا توجد تعيينات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

