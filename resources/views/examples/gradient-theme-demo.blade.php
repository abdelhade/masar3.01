{{-- 
    Bootstrap Gradient Theme Demo
    مثال شامل لاستخدام الـ theme الجديد
--}}

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Gradient Theme - Demo</title>
    
    {{-- Bootstrap 5 RTL --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    {{-- Line Awesome Icons --}}
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    
    {{-- Vite Assets --}}
    @vite(['resources/css/design-system.css', 'resources/css/themes/bootstrap-gradient-theme.css', 'resources/css/app.css'])
</head>
<body class="bg-light">

    {{-- Navbar --}}
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="las la-palette"></i>
                Bootstrap Gradient Theme Demo
            </span>
        </div>
    </nav>

    <div class="container py-4">
        
        {{-- Page Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-gradient-brand display-4 mb-2">مرحباً بك في Gradient Theme</h1>
                <p class="lead text-muted">استكشف جميع المكونات مع الـ gradients الجميلة</p>
            </div>
        </div>

        {{-- Buttons Section --}}
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="las la-mouse-pointer"></i> الأزرار (Buttons)</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button class="btn btn-primary">Primary</button>
                            <button class="btn btn-secondary">Secondary</button>
                            <button class="btn btn-success">Success</button>
                            <button class="btn btn-danger">Danger</button>
                            <button class="btn btn-warning">Warning</button>
                            <button class="btn btn-info">Info</button>
                            <button class="btn btn-light">Light</button>
                            <button class="btn btn-dark">Dark</button>
                        </div>
                        
                        <h6 class="mt-4 mb-3">أحجام مختلفة:</h6>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button class="btn btn-primary btn-sm">صغير</button>
                            <button class="btn btn-primary">عادي</button>
                            <button class="btn btn-primary btn-lg">كبير</button>
                        </div>

                        <h6 class="mt-4 mb-3">أزرار مع أيقونات:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-success">
                                <i class="las la-check"></i> حفظ
                            </button>
                            <button class="btn btn-danger">
                                <i class="las la-trash"></i> حذف
                            </button>
                            <button class="btn btn-info">
                                <i class="las la-eye"></i> عرض
                            </button>
                            <button class="btn btn-warning">
                                <i class="las la-edit"></i> تعديل
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cards Section --}}
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3"><i class="las la-layer-group"></i> البطاقات (Cards)</h3>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header">بطاقة عادية</div>
                    <div class="card-body">
                        <h5 class="card-title">عنوان البطاقة</h5>
                        <p class="card-text">محتوى البطاقة مع gradient خفيف في الخلفية.</p>
                        <button class="btn btn-primary">إجراء</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">بطاقة Primary</h5>
                        <p class="card-text">بطاقة ملونة بالكامل مع gradient جميل.</p>
                        <button class="btn btn-light">إجراء</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card bg-gradient-brand text-white">
                    <div class="card-body">
                        <h5 class="card-title">بطاقة Brand</h5>
                        <p class="card-text">بطاقة مع gradient العلامة التجارية.</p>
                        <button class="btn btn-light">إجراء</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dashboard Cards --}}
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3"><i class="las la-chart-bar"></i> بطاقات Dashboard</h3>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">إجمالي المبيعات</h6>
                                <h3 class="text-gradient-primary mb-0">$125,430</h3>
                            </div>
                            <div class="bg-gradient-primary p-3 rounded">
                                <i class="las la-dollar-sign text-white fs-2"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 5px;">
                            <div class="progress-bar" style="width: 75%"></div>
                        </div>
                        <small class="text-muted">+12% من الشهر الماضي</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">العملاء الجدد</h6>
                                <h3 class="text-gradient-brand mb-0">1,245</h3>
                            </div>
                            <div class="bg-gradient-success p-3 rounded">
                                <i class="las la-users text-white fs-2"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: 60%"></div>
                        </div>
                        <small class="text-muted">+8% من الشهر الماضي</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">الطلبات</h6>
                                <h3 class="text-gradient-brand mb-0">3,567</h3>
                            </div>
                            <div class="bg-gradient-info p-3 rounded">
                                <i class="las la-shopping-cart text-white fs-2"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 5px;">
                            <div class="progress-bar bg-info" style="width: 85%"></div>
                        </div>
                        <small class="text-muted">+15% من الشهر الماضي</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">الإيرادات</h6>
                                <h3 class="text-gradient-brand mb-0">$89,340</h3>
                            </div>
                            <div class="bg-gradient-warning p-3 rounded">
                                <i class="las la-chart-line text-white fs-2"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 5px;">
                            <div class="progress-bar bg-warning" style="width: 70%"></div>
                        </div>
                        <small class="text-muted">+10% من الشهر الماضي</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Badges Section --}}
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="las la-tag"></i> الشارات (Badges)</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">Primary</span>
                            <span class="badge bg-secondary">Secondary</span>
                            <span class="badge bg-success">Success</span>
                            <span class="badge bg-danger">Danger</span>
                            <span class="badge bg-warning">Warning</span>
                            <span class="badge bg-info">Info</span>
                            <span class="badge bg-light text-dark">Light</span>
                            <span class="badge bg-dark">Dark</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerts Section --}}
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3"><i class="las la-bell"></i> التنبيهات (Alerts)</h3>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="alert alert-success">
                    <i class="las la-check-circle"></i>
                    تم حفظ البيانات بنجاح!
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="alert alert-danger">
                    <i class="las la-exclamation-circle"></i>
                    حدث خطأ أثناء العملية!
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="alert alert-warning">
                    <i class="las la-exclamation-triangle"></i>
                    تحذير: يرجى مراجعة البيانات!
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="alert alert-info">
                    <i class="las la-info-circle"></i>
                    معلومة: يمكنك تحديث البيانات لاحقاً.
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="las la-table"></i> الجداول (Tables)</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>أحمد محمد</td>
                                    <td>ahmed@example.com</td>
                                    <td><span class="badge bg-success">نشط</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="las la-eye"></i></button>
                                        <button class="btn btn-sm btn-warning"><i class="las la-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="las la-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>فاطمة علي</td>
                                    <td>fatima@example.com</td>
                                    <td><span class="badge bg-warning">معلق</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="las la-eye"></i></button>
                                        <button class="btn btn-sm btn-warning"><i class="las la-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="las la-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>محمد حسن</td>
                                    <td>mohamed@example.com</td>
                                    <td><span class="badge bg-danger">غير نشط</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="las la-eye"></i></button>
                                        <button class="btn btn-sm btn-warning"><i class="las la-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="las la-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Section --}}
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="las la-edit"></i> النماذج (Forms)</h4>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الاسم الكامل</label>
                                    <input type="text" class="form-control" placeholder="أدخل الاسم">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" placeholder="أدخل البريد">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الحالة</label>
                                    <select class="form-select">
                                        <option>نشط</option>
                                        <option>معلق</option>
                                        <option>غير نشط</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الدور</label>
                                    <select class="form-select">
                                        <option>مدير</option>
                                        <option>موظف</option>
                                        <option>عميل</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">الملاحظات</label>
                                    <textarea class="form-control" rows="3" placeholder="أدخل الملاحظات"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="agree">
                                        <label class="form-check-label" for="agree">
                                            أوافق على الشروط والأحكام
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="las la-save"></i> حفظ
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="las la-redo"></i> إعادة تعيين
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Bars --}}
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="las la-tasks"></i> أشرطة التقدم (Progress Bars)</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Primary - 75%</label>
                            <div class="progress">
                                <div class="progress-bar" style="width: 75%">75%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Success - 60%</label>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 60%">60%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Warning - 45%</label>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 45%">45%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Danger - 30%</label>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: 30%">30%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Special Gradients --}}
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3"><i class="las la-palette"></i> Gradients مخصصة</h3>
            </div>

            <div class="col-md-3 mb-3">
                <div class="bg-gradient-brand p-4 rounded text-white text-center">
                    <h5>Brand Gradient</h5>
                    <p class="mb-0">Mint + Teal</p>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="bg-gradient-sunset p-4 rounded text-white text-center">
                    <h5>Sunset Gradient</h5>
                    <p class="mb-0">Red + Yellow</p>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="bg-gradient-ocean p-4 rounded text-white text-center">
                    <h5>Ocean Gradient</h5>
                    <p class="mb-0">Blue + Purple</p>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="bg-gradient-forest p-4 rounded text-white text-center">
                    <h5>Forest Gradient</h5>
                    <p class="mb-0">Green + Light Green</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
