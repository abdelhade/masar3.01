@extends('progress::layouts.app')

@section('title', __('general.backup_restore'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active">
        {{ __('general.backup_restore') }}
    </li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-database me-2"></i>
                        {{ __('general.backup_restore') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>
                        {{ __('general.create_backup') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        إنشاء نسخة احتياطية من قاعدة البيانات بالكامل وتحميلها
                    </p>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تحذير:</strong> قد تستغرق العملية بعض الوقت حسب حجم قاعدة البيانات
                    </div>

                    <form action="{{ route('progress.backup.export') }}" method="GET">
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-download me-2"></i>
                            إنشاء وتحميل النسخة الاحتياطية
                        </button>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>
                        {{ __('general.restore_backup') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        استيراد نسخة احتياطية واستعادة البيانات
                    </p>
                    
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تحذير:</strong> سيتم استبدال جميع البيانات الحالية!
                    </div>

                    <form action="{{ route('progress.backup.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div class="mb-3">
                            <label for="sql_file" class="form-label fw-bold">
                                اختر ملف SQL
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   name="sql_file" 
                                   id="sql_file" 
                                   class="form-control @error('sql_file') is-invalid @enderror"
                                   accept=".sql,.txt"
                                   required>
                            @error('sql_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                الحد الأقصى: 100MB | الصيغة: .sql
                            </small>
                        </div>

                        <button type="button" class="btn btn-danger btn-lg w-100" onclick="confirmImport()">
                            <i class="fas fa-upload me-2"></i>
                            استعادة النسخة الاحتياطية
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        النسخ الاحتياطية المحفوظة
                    </h5>
                </div>
                <div class="card-body">
                    @if($backups->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-database fa-3x mb-3"></i>
                            <p>لا توجد نسخ احتياطية محفوظة</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file me-1"></i> اسم الملف</th>
                                        <th><i class="fas fa-hdd me-1"></i> الحجم</th>
                                        <th><i class="fas fa-calendar me-1"></i> التاريخ</th>
                                        <th class="text-center"><i class="fas fa-cog me-1"></i> الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-code text-primary me-2"></i>
                                            {{ $backup['name'] }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $backup['size'] }}</span>
                                        </td>
                                        <td>{{ $backup['date'] }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('progress.backup.download', $backup['name']) }}" 
                                                   class="btn btn-sm btn-success"
                                                   title="تحميل">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger"
                                                        onclick="confirmDelete('{{ $backup['name'] }}')"
                                                        title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmImport() {
    if (!document.getElementById('sql_file').files.length) {
        alert('الرجاء اختيار ملف SQL');
        return;
    }
    
    if (confirm('تحذير: سيتم استبدال جميع البيانات الحالية بالنسخة الاحتياطية!\n\nهل أنت متأكد من المتابعة؟')) {
        document.getElementById('importForm').submit();
    }
}

function confirmDelete(filename) {
    if (confirm('هل أنت متأكد من حذف النسخة الاحتياطية:\n' + filename + '؟')) {
        const form = document.getElementById('deleteForm');
        form.action = '/backup/delete/' + encodeURIComponent(filename);
        form.submit();
    }
}
</script>
@endpush

