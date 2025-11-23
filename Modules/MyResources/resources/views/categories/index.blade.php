@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">التصنيفات</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title">قائمة التصنيفات</h4>
                        <div>
                            <a href="{{ route('myresources.categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة تصنيف جديد
                            </a>
                            <a href="{{ route('myresources.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> إدارة الموارد
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>الاسم العربي</th>
                                    <th>الوصف</th>
                                    <th>الأيقونة</th>
                                    <th>اللون</th>
                                    <th>ترتيب العرض</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->name_ar }}</td>
                                        <td>{{ $category->description ?? '-' }}</td>
                                        <td>
                                            @if($category->icon)
                                                <i class="{{ $category->icon }}"></i>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($category->color)
                                                <span class="badge bg-{{ $category->color }}">{{ $category->color }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $category->sort_order ?? 0 }}</td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('myresources.categories.edit', $category) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('myresources.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">لا توجد تصنيفات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

