@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Account Movement'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('كمية الأصناف')]],
    ])

    <!-- تضمين Bootstrap CSS و JS -->

    <div class="container py-5">
        <!-- عنوان الصفحة -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fs-3 fw-bold text-dark">مراقبة كميات الأصناف</h1>
            <button class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="إضافة صنف جديد">
                <i class="bi bi-plus-circle me-2"></i> إضافة صنف
            </button>
        </div>

        <!-- الجدول -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-end fw-semibold text-uppercase small px-4 py-3">الكود</th>
                                <th scope="col" class="text-end fw-semibold text-uppercase small px-4 py-3">الاسم</th>
                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">الكمية
                                    الحالية</th>
                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">الحد
                                    الأدنى</th>
                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">الحد
                                    الأقصى</th>
                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">الحالة
                                </th>
                                <th scope="col" class="text-center fw-semibold text-uppercase small px-4 py-3">الإجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr
                                    class="@if ($item['status'] == 'below_min') table-danger @elseif ($item['status'] == 'above_max') table-primary @else table-success @endif">
                                    <td class="text-end px-4 py-3">{{ $item['code'] }}</td>
                                    <td class="text-end px-4 py-3">{{ $item['name'] }}</td>
                                    <td class="text-center px-4 py-3">{{ number_format($item['current_quantity'], 2) }}</td>
                                    <td class="text-center px-4 py-3">{{ $item['min_order_quantity'] }}</td>
                                    <td class="text-center px-4 py-3">{{ $item['max_order_quantity'] }}</td>
                                    <td class="text-center px-4 py-3">
                                        @if ($item['status'] == 'below_min')
                                            <span class="badge bg-danger rounded-pill">
                                                <i class="bi bi-arrow-down me-1"></i> أقل من الحد الأدنى
                                            </span>
                                        @elseif ($item['status'] == 'above_max')
                                            <span class="badge bg-primary rounded-pill">
                                                <i class="bi bi-arrow-up me-1"></i> أعلى من الحد الأقصى
                                            </span>
                                        @else
                                            <span class="badge bg-success rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> ضمن الحدود
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center px-4 py-3">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="tooltip"
                                                title="تعديل">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="tooltip"
                                                title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- تذييل الجدول -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <span class="text-muted small">إجمالي الأصناف: {{ count($items) }}</span>
            <div>
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-2"></i> تصدير إلى Excel
                </button>
            </div>
        </div>
    </div>

    <!-- أنماط مخصصة -->
    <style>
        .table th,
        .table td {
            vertical-align: middle;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 9999px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f3f5;
        }

        .btn-group .btn {
            transition: all 0.2s ease;
        }

        .btn-group .btn:hover {
            transform: translateY(-1px);
        }
    </style>

    <!-- تفعيل Tooltips -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
