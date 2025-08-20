@extends('admin.dashboard')
@section('content')
    @push('styles')
        <style>
            .download-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                padding: 40px;
                max-width: 500px;
                margin: 0 auto;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .download-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 5px;
                background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57);
                background-size: 300% 100%;
                animation: rainbow 3s ease infinite;
            }

            @keyframes rainbow {

                0%,
                100% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }
            }

            .download-icon {
                font-size: 4rem;
                color: #667eea;
                margin-bottom: 30px;
                animation: bounce 2s infinite;
            }

            @keyframes bounce {

                0%,
                20%,
                50%,
                80%,
                100% {
                    transform: translateY(0);
                }

                40% {
                    transform: translateY(-10px);
                }

                60% {
                    transform: translateY(-5px);
                }
            }

            .download-btn {
                background: linear-gradient(45deg, #667eea, #764ba2);
                border: none;
                padding: 15px 40px;
                border-radius: 50px;
                color: white;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
                position: relative;
                overflow: hidden;
            }

            .download-btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
            }

            .download-btn:active {
                transform: translateY(-1px);
            }

            .download-btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                transition: left 0.5s;
            }

            .download-btn:hover::before {
                left: 100%;
            }

            .spinner {
                display: none;
                margin: 20px auto;
            }

            .progress-container {
                display: none;
                margin-top: 20px;
            }

            .progress-bar {
                height: 10px;
                border-radius: 5px;
                background: linear-gradient(45deg, #667eea, #764ba2);
                animation: progress-animation 2s ease-in-out;
            }

            @keyframes progress-animation {
                0% {
                    width: 0%;
                }

                100% {
                    width: 100%;
                }
            }

            .success-message {
                display: none;
                color: #28a745;
                margin-top: 20px;
                font-weight: 600;
            }

            .error-message {
                display: none;
                color: #dc3545;
                margin-top: 20px;
                font-weight: 600;
            }

            .info-text {
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
            }

            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 20px;
                margin-top: 30px;
                padding-top: 30px;
                border-top: 1px solid #eee;
            }

            .stat-item {
                text-align: center;
            }

            .stat-number {
                font-size: 2rem;
                font-weight: bold;
                color: #667eea;
            }

            .stat-label {
                font-size: 0.9rem;
                color: #666;
            }

            .download-options {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-top: 30px;
            }

            .option-btn {
                padding: 12px 20px;
                border: 2px solid #667eea;
                background: transparent;
                color: #667eea;
                border-radius: 10px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-weight: 500;
            }

            .option-btn:hover {
                background: #667eea;
                color: white;
                transform: translateY(-2px);
            }

            .option-btn.active {
                background: #667eea;
                color: white;
            }
        </style>
    @endpush
    <div class="container">
        <div class="download-card">
            <div class="download-icon">
                <i class="fas fa-cloud-download-alt"></i>
            </div>

            <h2 style="color: #333; margin-bottom: 20px;">تنزيل بيانات النظام</h2>

            <p class="info-text">
                يمكنك تنزيل جميع بيانات نظام الـ ERP لحفظها محلياً على جهازك.
                البيانات ستكون محمية ومضغوطة في ملف واحد.
            </p>

            <!-- خيارات التنزيل -->
            <div class="download-options">
                <button class="option-btn active" data-type="json" onclick="selectOption('json', this)">
                    <i class="fas fa-file-code"></i> JSON/CSV
                </button>
                <button class="option-btn" data-type="sql" onclick="selectOption('sql', this)">
                    <i class="fas fa-database"></i> SQL Database
                </button>
            </div>
            <br>

            <!-- زر التنزيل الرئيسي -->
            <button class="download-btn" id="downloadBtn" onclick="startDownload()">
                <i class="fas fa-download"></i>
                <span id="btnText">تنزيل البيانات</span>
            </button>

            <!-- مؤشر التحميل -->
            <div class="spinner" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-2">جاري تحضير البيانات للتنزيل...</p>
            </div>

            <!-- شريط التقدم -->
            <div class="progress-container" id="progressContainer">
                <div class="progress">
                    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                </div>
                <small class="text-muted mt-2 d-block">جاري ضغط الملفات...</small>
            </div>

            <!-- رسائل النجاح والخطأ -->
            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i>
                تم تنزيل البيانات بنجاح!
            </div>

            <div class="error-message" id="errorMessage">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="errorText">حدث خطأ أثناء التنزيل</span>
            </div>

            <!-- إحصائيات وهمية للعرض -->
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">1,247</div>
                    <div class="stat-label">سجل</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">15</div>
                    <div class="stat-label">جدول</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">2.4</div>
                    <div class="stat-label">ميجا بايت</div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script>
            let selectedType = 'json';

            function selectOption(type, button) {
                // إزالة الـ active من جميع الأزرار
                document.querySelectorAll('.option-btn').forEach(btn => {
                    btn.classList.remove('active');
                });

                // إضافة active للزر المختار
                button.classList.add('active');
                selectedType = type;

                // تحديث نص الزر
                const btnText = document.getElementById('btnText');
                if (type === 'json') {
                    btnText.innerHTML = '<i class="fas fa-download"></i> تنزيل JSON/CSV';
                } else {
                    btnText.innerHTML = '<i class="fas fa-download"></i> تنزيل SQL Database';
                }
            }

            function startDownload() {
                const downloadBtn = document.getElementById('downloadBtn');
                const spinner = document.getElementById('loadingSpinner');
                const progressContainer = document.getElementById('progressContainer');
                const successMessage = document.getElementById('successMessage');
                const errorMessage = document.getElementById('errorMessage');

                // إخفاء الرسائل السابقة
                successMessage.style.display = 'none';
                errorMessage.style.display = 'none';

                // تعطيل الزر وإظهار التحميل
                downloadBtn.disabled = true;
                spinner.style.display = 'block';

                // محاكاة عملية التحضير
                setTimeout(() => {
                    spinner.style.display = 'none';
                    progressContainer.style.display = 'block';

                    // بدء شريط التقدم
                    const progressBar = document.getElementById('progressBar');
                    let width = 0;
                    const interval = setInterval(() => {
                        width += 10;
                        progressBar.style.width = width + '%';

                        if (width >= 100) {
                            clearInterval(interval);

                            // محاكاة التنزيل الفعلي
                            setTimeout(() => {
                                downloadFile();
                            }, 500);
                        }
                    }, 200);
                }, 1000);
            }

            function downloadFile() {
                const progressContainer = document.getElementById('progressContainer');
                const successMessage = document.getElementById('successMessage');
                const errorMessage = document.getElementById('errorMessage');
                const downloadBtn = document.getElementById('downloadBtn');

                // تحديد الـ URL حسب النوع المختار
                let downloadUrl;
                if (selectedType === 'json') {
                    downloadUrl = '/settings/export-data'; // استبدل بالـ route الصحيح
                } else {
                    downloadUrl = '/settings/export-sql'; // استبدل بالـ route الصحيح
                }

                // محاولة التنزيل الفعلي
                fetch(downloadUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('فشل في تنزيل البيانات');
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // إنشاء رابط التنزيل
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download =
                            `erp_data_${selectedType}_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.${selectedType === 'json' ? 'zip' : 'sql'}`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);

                        // إظهار رسالة النجاح
                        progressContainer.style.display = 'none';
                        successMessage.style.display = 'block';

                        // إعادة تفعيل الزر
                        setTimeout(() => {
                            downloadBtn.disabled = false;
                            successMessage.style.display = 'none';
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // إظهار رسالة الخطأ
                        progressContainer.style.display = 'none';
                        errorMessage.style.display = 'block';
                        document.getElementById('errorText').textContent = error.message;

                        // إعادة تفعيل الزر
                        setTimeout(() => {
                            downloadBtn.disabled = false;
                            errorMessage.style.display = 'none';
                        }, 3000);
                    });
            }

            // محاكاة تحديث الإحصائيات (اختياري)
            function updateStats() {
                // يمكنك استدعاء API للحصول على الإحصائيات الفعلية
                fetch('/api/export-stats')
                    .then(response => response.json())
                    .then(data => {
                        if (data.records) {
                            document.querySelector('.stat-number').textContent = data.records.toLocaleString();
                        }
                        if (data.tables) {
                            document.querySelectorAll('.stat-number')[1].textContent = data.tables;
                        }
                        if (data.size) {
                            document.querySelectorAll('.stat-number')[2].textContent = data.size + ' MB';
                        }
                    })
                    .catch(error => console.log('Stats update failed:', error));
            }

            // تحديث الإحصائيات عند تحميل الصفحة
            document.addEventListener('DOMContentLoaded', function() {
                // updateStats(); // قم بتفعيل هذا إذا كان لديك API للإحصائيات
            });
        </script>
    @endpush
@endsection
