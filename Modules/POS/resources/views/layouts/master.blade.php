<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>نظام نقاط البيع - {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- POS Global Styles -->
    <style>
        :root {
            --pos-primary: #3498db;
            --pos-success: #27ae60;
            --pos-danger: #e74c3c;
            --pos-warning: #f39c12;
            --pos-info: #8e44ad;
            --pos-light: #ecf0f1;
            --pos-dark: #2c3e50;
            --pos-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --pos-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            --pos-border-radius: 15px;
            --pos-transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: var(--pos-dark);
            background: var(--pos-gradient);
            margin: 0;
            padding: 0;
            direction: rtl;
            overflow-x: hidden;
        }

        .pos-fullscreen {
            height: 100vh;
            overflow: hidden;
        }

        /* إعدادات عامة للنصوص */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* أزرار مخصصة */
        .btn-pos {
            font-family: 'Cairo', sans-serif;
            font-weight: 500;
            border-radius: var(--pos-border-radius);
            padding: 0.75rem 1.5rem;
            transition: var(--pos-transition);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-pos:hover {
            transform: translateY(-2px);
            box-shadow: var(--pos-shadow);
        }

        .btn-pos.btn-primary {
            background: linear-gradient(135deg, var(--pos-primary) 0%, #74b9ff 100%);
            color: white;
        }

        .btn-pos.btn-success {
            background: linear-gradient(135deg, var(--pos-success) 0%, #2ecc71 100%);
            color: white;
        }

        .btn-pos.btn-danger {
            background: linear-gradient(135deg, var(--pos-danger) 0%, #ff6b6b 100%);
            color: white;
        }

        /* حقول الإدخال */
        .form-control-pos {
            font-family: 'Cairo', sans-serif;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: var(--pos-transition);
            background: white;
        }

        .form-control-pos:focus {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        /* بطاقات */
        .card-pos {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--pos-border-radius);
            padding: 2rem;
            box-shadow: var(--pos-shadow);
            border: none;
            transition: var(--pos-transition);
        }

        .card-pos:hover {
            transform: translateY(-2px);
        }

        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 768px) {
            body {
                font-size: 14px;
            }
            
            .card-pos {
                padding: 1rem;
                margin: 0.5rem;
            }
        }

        /* تأثيرات التحميل */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--pos-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* تخصيص شريط التمرير */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* تحسينات للطباعة */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>
<body>
    <!-- مؤشر التحميل -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner"></div>
    </div>

    <!-- المحتوى الأساسي -->
    <main class="pos-main">
        @yield('content')
    </main>

    <!-- رسائل التنبيه -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- jQuery (للتوافق مع المكونات القديمة) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    @livewireScripts

    <!-- POS Global Scripts -->
    <script>
        // إعدادات عامة للPOS
        window.POS = {
            config: {
                currency: 'ريال',
                locale: 'ar-SA',
                dateFormat: 'YYYY-MM-DD',
                timeFormat: 'HH:mm'
            },
            
            // دوال مساعدة
            utils: {
                formatCurrency: function(amount) {
                    return new Intl.NumberFormat('ar-SA', {
                        style: 'currency',
                        currency: 'SAR',
                        minimumFractionDigits: 2
                    }).format(amount);
                },
                
                formatNumber: function(number) {
                    return new Intl.NumberFormat('ar-SA').format(number);
                },
                
                showLoading: function() {
                    document.getElementById('loading-overlay').style.display = 'flex';
                },
                
                hideLoading: function() {
                    document.getElementById('loading-overlay').style.display = 'none';
                },
                
                showToast: function(message, type = 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: type,
                        title: message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            }
        };

        // إعداد CSRF token للطلبات
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // إعداد Axios للطلبات
        if (window.axios) {
            window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
        }

        // التعامل مع أخطاء الشبكة
        window.addEventListener('online', function() {
            POS.utils.showToast('تم استعادة الاتصال بالإنترنت', 'success');
        });

        window.addEventListener('offline', function() {
            POS.utils.showToast('انقطع الاتصال بالإنترنت', 'warning');
        });

        // إخفاء رسائل التنبيه تلقائياً
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // دعم اختصارات لوحة المفاتيح العامة
        document.addEventListener('keydown', function(e) {
            // Ctrl+Home للعودة للصفحة الرئيسية
            if (e.ctrlKey && e.key === 'Home') {
                e.preventDefault();
                window.location.href = '{{ route("pos.index") }}';
            }
            
            // Alt+F4 للخروج (العودة للنظام الأساسي)
            if (e.altKey && e.key === 'F4') {
                e.preventDefault();
                window.location.href = '{{ url("/") }}';
            }
        });

        // تحسين الأداء للأجهزة اللوحية
        if ('ontouchstart' in window) {
            document.body.classList.add('touch-device');
        }

        // إعداد PWA (إذا كان مطلوباً)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // يمكن إضافة service worker هنا لاحقاً
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
