@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => 'تفاصيل النشاط',
        'items' => [
            ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
            ['label' => 'سجل النشاطات', 'url' => route('activitylog.index')],
            ['label' => 'تفاصيل النشاط'],
        ],
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        تفاصيل النشاط
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">معلومات المستخدم</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">المستخدم:</th>
                                    <td>
                                        @if ($activity->causer)
                                            <span class="fw-bold">{{ $activity->causer->name }}</span>
                                            <br>
                                            <small class="text-muted">{{ $activity->causer->email }}</small>
                                        @else
                                            <span class="text-muted">نظام</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>الوصف:</th>
                                    <td><span class="fw-bold">{{ $activity->description }}</span></td>
                                </tr>
                                <tr>
                                    <th>نوع النشاط:</th>
                                    <td>
                                        @php
                                            $eventColors = [
                                                'created' => 'success',
                                                'updated' => 'warning',
                                                'deleted' => 'danger',
                                            ];
                                            $color = $eventColors[$activity->event] ?? 'info';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ $activity->event ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">معلومات الكائن</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">نوع الكائن:</th>
                                    <td>
                                        @if ($activity->subject_type)
                                            <span class="badge bg-secondary">
                                                {{ class_basename($activity->subject_type) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>معرف الكائن:</th>
                                    <td>
                                        @if ($activity->subject_id)
                                            <code>{{ $activity->subject_id }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>التاريخ والوقت:</th>
                                    <td>
                                        <span class="fw-bold">{{ $activity->created_at->format('Y-m-d H:i:s') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if ($activity->properties && count($activity->properties) > 0)
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">الخصائص</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($activity->changes && count($activity->changes) > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">التغييرات</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>الحقل</th>
                                                <th>القيمة القديمة</th>
                                                <th>القيمة الجديدة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity->changes as $key => $change)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        @if (isset($change['old']))
                                                            <span class="text-danger">
                                                                {{ is_array($change['old']) ? json_encode($change['old'], JSON_UNESCAPED_UNICODE) : $change['old'] }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (isset($change['attributes']))
                                                            <span class="text-success">
                                                                {{ is_array($change['attributes']) ? json_encode($change['attributes'], JSON_UNESCAPED_UNICODE) : $change['attributes'] }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('activitylog.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        code {
            background: transparent;
            padding: 0;
        }
    </style>
@endpush
