@extends('admin.dashboard')
@section('content')
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Tahoma', sans-serif;
        }
        .toolbar button {
            margin: 3px;
        }
        .section-title {
            color: #0d6efd;
            font-size: 1.1rem;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body class="p-3">
    <div class="container">
        <!-- شريط الأدوات -->
        <div class="toolbar mb-3">
            <button class="btn btn-danger btn-sm">إغلاق</button>
            <button class="btn btn-secondary btn-sm">تراجع</button>
            <button class="btn btn-primary btn-sm">طباعة</button>
            <button class="btn btn-success btn-sm">حفظ</button>
            <button class="btn btn-info btn-sm">جديد</button>
            <button class="btn btn-warning btn-sm">تحليل</button>
            <button class="btn btn-danger btn-sm">حذف</button>
        </div>

        <!-- عنوان -->
        <h4 class="mb-4 text-primary text-center">استلام ورقة قبض عام - جديد</h4>

        <!-- النموذج -->
        <form>
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">رقم السند</label>
                    <input type="text" class="form-control" value="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الرقم الدفتري</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ المستند</label>
                    <input type="date" class="form-control" value="2025-07-26">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label text-primary">استلمنا من الحساب</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-primary">المبلغ المسلم</label>
                    <input type="number" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" rows="2"></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">الرصيد قبل</label>
                    <input type="text" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">الرصيد بعد</label>
                    <input type="text" class="form-control" value="0">
                </div>
            </div>

            <h5 class="section-title">بيانات الشيك</h5>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">تاريخ تحرير الشيك</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">رقم الشيك</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ الشيك</label>
                    <input type="date" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">اسم البنك</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">اسم صاحب الورقة الأصلي</label>
                    <input type="text" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">حفظ</button>
        </form>
    </div>
@endsection
