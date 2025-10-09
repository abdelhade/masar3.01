@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('مراحل التصنيع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('مراحل التصنيع'), 'url' => route('manufacturing.stages.index')],
            ['label' => __('عرض')],
        ],
    ])

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">تفاصيل مرحلة التصنيع</h2>
                    <a href="{{ route('manufacturing.stages.index') }}" class="btn btn-light btn-sm">
                        <i class="las la-arrow-right"></i> رجوع
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">اسم المرحلة:</label>
                            <p class="mb-0">{{ $manufacturingStage->name }}</p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">الترتيب:</label>
                            <p class="mb-0">{{ (int) $manufacturingStage->order }}</p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">الفرع:</label>
                            <p class="mb-0">{{ optional($manufacturingStage->branch)->name ?? '—' }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">المدة التقديرية (ساعات):</label>
                            <p class="mb-0">{{ $manufacturingStage->estimated_duration ?? '—' }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">التكلفة (جنيه):</label>
                            <p class="mb-0">{{ number_format($manufacturingStage->cost, 2) }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">الحالة:</label>
                            @if ($manufacturingStage->is_active)
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-danger">غير نشط</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold">الوصف:</label>
                        <div class="border rounded p-3" style="background: #f9f9f9;">
                            {!! $manufacturingStage->description
                                ? nl2br(e($manufacturingStage->description))
                                : '<span class="text-muted">لا يوجد وصف</span>' !!}
                        </div>
                    </div>

                    <div class="d-flex justify-content-start gap-2">
                        <a href="{{ route('manufacturing.stages.edit', $manufacturingStage->id) }}" class="btn btn-success">
                            <i class="las la-edit"></i> تعديل
                        </a>

                        <form action="{{ route('manufacturing.stages.destroy', $manufacturingStage->id) }}" method="POST"
                            onsubmit="return confirm('هل أنت متأكد من حذف هذه المرحلة؟');" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="las la-trash"></i> حذف
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-footer text-muted text-end">
                    <small>تم الإنشاء في: {{ $manufacturingStage->created_at->format('Y-m-d H:i') }} |
                        آخر تحديث: {{ $manufacturingStage->updated_at->format('Y-m-d H:i') }}</small>
                </div>
            </div>
        </div>
    </div>
@endsection
