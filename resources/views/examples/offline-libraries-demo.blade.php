<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>مثال المكتبات المحلية - Offline Libraries Demo</title>
    
    {{-- CSS Files (Bootstrap + SweetAlert2) --}}
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">🎉 مثال المكتبات المحلية (Offline)</h1>
        <p class="lead">جميع المكتبات تعمل محلياً بدون اتصال بالإنترنت</p>

        <div class="row g-4">
            {{-- Bootstrap Examples --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Bootstrap 5</h5>
                    </div>
                    <div class="card-body">
                        <p>أمثلة Bootstrap:</p>
                        <button class="btn btn-primary">Primary</button>
                        <button class="btn btn-success">Success</button>
                        <button class="btn btn-danger">Danger</button>
                        <button class="btn btn-main mt-2">زر خاص</button>
                        
                        <div class="mt-3">
                            <span class="badge bg-primary">جديد</span>
                            <span class="badge bg-success">نشط</span>
                            <span class="badge bg-danger">غير نشط</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SweetAlert2 Examples --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">SweetAlert2</h5>
                    </div>
                    <div class="card-body">
                        <p>أمثلة SweetAlert2:</p>
                        <button onclick="showSuccess()" class="btn btn-success">
                            <i class="las la-check"></i> نجاح
                        </button>
                        <button onclick="showError()" class="btn btn-danger">
                            <i class="las la-times"></i> خطأ
                        </button>
                        <button onclick="showWarning()" class="btn btn-warning">
                            <i class="las la-exclamation-triangle"></i> تحذير
                        </button>
                        <button onclick="showConfirm()" class="btn btn-info mt-2">
                            <i class="las la-question"></i> تأكيد
                        </button>
                    </div>
                </div>
            </div>

            {{-- Chart.js Examples --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Chart.js - الرسوم البيانية</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6>Bar Chart (أعمدة)</h6>
                                <canvas id="barChart"></canvas>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h6>Line Chart (خط)</h6>
                                <canvas id="lineChart"></canvas>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h6>Pie Chart (دائري)</h6>
                                <canvas id="pieChart"></canvas>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h6>Doughnut Chart (دونات)</h6>
                                <canvas id="doughnutChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bootstrap Modal Example --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Bootstrap Modal</h5>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            فتح النافذة المنبثقة
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="alert alert-success mt-4" role="alert">
            <h5 class="alert-heading">✅ جميع المكتبات تعمل محلياً!</h5>
            <p class="mb-0">Bootstrap 5, Chart.js, و SweetAlert2 - كلها محلية وتعمل بدون إنترنت</p>
        </div>
    </div>

    {{-- Bootstrap Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">نافذة منبثقة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>هذه نافذة منبثقة من Bootstrap تعمل محلياً!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript Files --}}
    @vite([
        'resources/js/app.js',
        'resources/js/chart-setup.js',
        'resources/js/sweetalert-setup.js'
    ])

    <script>
        // SweetAlert2 Examples
        function showSuccess() {
            Swal.fire({
                icon: 'success',
                title: 'نجح!',
                text: 'تم العملية بنجاح',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#34d3a3'
            });
        }

        function showError() {
            Swal.fire({
                icon: 'error',
                title: 'خطأ!',
                text: 'حدث خطأ ما',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#e61717'
            });
        }

        function showWarning() {
            Swal.fire({
                icon: 'warning',
                title: 'تحذير!',
                text: 'انتبه لهذا الأمر',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#e6a817'
            });
        }

        function showConfirm() {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن هذا!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#34d3a3',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، متأكد!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        'تم!',
                        'تم تأكيد العملية.',
                        'success'
                    );
                }
            });
        }

        // Chart.js Examples
        const chartColors = {
            primary: '#34d3a3',
            secondary: '#1aa1c4',
            success: '#17b860',
            danger: '#e61717',
            warning: '#e6a817',
            info: '#0075e6'
        };

        // Bar Chart
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'المبيعات',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: chartColors.primary,
                    borderColor: chartColors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Line Chart
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'الإيرادات',
                    data: [65, 59, 80, 81, 56, 55],
                    borderColor: chartColors.secondary,
                    backgroundColor: 'rgba(26, 161, 196, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true
            }
        });

        // Pie Chart
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: ['منتج أ', 'منتج ب', 'منتج ج', 'منتج د'],
                datasets: [{
                    data: [30, 20, 25, 25],
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.secondary,
                        chartColors.success,
                        chartColors.warning
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });

        // Doughnut Chart
        new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: {
                labels: ['مبيعات', 'مصروفات', 'أرباح'],
                datasets: [{
                    data: [300, 150, 150],
                    backgroundColor: [
                        chartColors.success,
                        chartColors.danger,
                        chartColors.info
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>
