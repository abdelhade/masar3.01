<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أوراق القبض</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Tahoma', sans-serif;
        }
        .navbar {
            background-color: #d0d7e0;
        }
        .navbar-nav .nav-link {
            color: #000;
            font-weight: bold;
            padding: 10px 15px;
        }
        .toolbar button {
            margin: 5px;
        }
        .table th, .table td {
            text-align: center;
        }
        .search-form label {
            font-weight: bold;
        }
        .page-header {
            color: #b60000;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- شريط القوائم العلوي -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#">كشف حساب</a></li>
                <li class="nav-item"><a class="nav-link" href="#">يومية الأوراق المالية</a></li>
                <li class="nav-item"><a class="nav-link" href="#">حركة الأوراق المالية</a></li>
                <li class="nav-item"><a class="nav-link" href="#">أوراق الدفع</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">أوراق القبض</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- العنوان -->
        <h3 class="page-header mb-4">إدارة أوراق القبض</h3>

        <!-- شريط الأدوات -->
        <div class="toolbar mb-3">
            <button class="btn btn-primary btn-sm">طباعة</button>
            <button class="btn btn-secondary btn-sm">بحث جديد</button>
            <button class="btn btn-secondary btn-sm">بدء البحث</button>
            <button class="btn btn-success btn-sm">استعلام ورقة قبض</button>
            <button class="btn btn-info btn-sm">فتح الورقة المتعددة</button>
            <button class="btn btn-warning btn-sm">تحويل الورقة</button>
            <button class="btn btn-danger btn-sm">رفض الورقة</button>
            <button class="btn btn-secondary btn-sm">إلغاء تحويل ورقة قبض</button>
            <button class="btn btn-secondary btn-sm">إلغاء الحافظة</button>
            <button class="btn btn-danger btn-sm">تغيير الحافظة</button>
            <button class="btn btn-danger btn-sm">إلغاء تغيير الحافظة</button>
            <button class="btn btn-warning btn-sm">عرض</button>
        </div>

        <!-- نموذج البحث -->
        <form class="search-form mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">نوع الأوراق</label>
                    <select id="type" class="form-select">
                        <option>أوراق القبض</option>
                        <option>أوراق الدفع</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">حالة الشيك</label>
                    <select id="status" class="form-select">
                        <option>تحت التحصيل</option>
                        <option>مرفوض</option>
                        <option>محصل</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ الاستحقاق من</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ تحرير الشيك من</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">رقم الشيك</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">اسم صاحب الورقة</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">اسم المستفيد</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">تحديث</button>
                </div>
            </div>
        </form>

        <!-- الجدول -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>التاريخ</th>
                        <th>نوع العملية</th>
                        <th>حالة المستند</th>
                        <th>رقم السند</th>
                        <th>رقم الحساب</th>
                        <th>قيمة الشيك</th>
                        <th>رقم الشيك</th>
                        <th>اسم البنك</th>
                        <th>اسم المستفيد</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="9">لا توجد بيانات طبقاً للشروط المختارة</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- عدد السجلات -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>قيمة أوراق قبض: <strong>0</strong></div>
            <div>عدد السجلات: <strong>0</strong></div>
        </div>
    </div>
</body>
</html>
