@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('نقاط البيع'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('نقاط البيع')],
        ],
    ])

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="font-family-cairo fw-bold">
                            <i class="fas fa-cash-register me-2"></i>
                            نقاط البيع
                        </h1>
                    </div>
                    <div class="col-sm-6 text-end">
                        <a href="{{ route('pos-vouchers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            إنشاء عملية جديدة
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> نجح!</h5>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> خطأ!</h5>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title font-family-cairo fw-bold">
                            <i class="fas fa-list me-2"></i>
                            قائمة عمليات نقاط البيع
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="">
                                    <tr>
                                        <th class="font-family-cairo fw-bold">م</th>
                                        <th class="font-family-cairo fw-bold">التاريخ</th>
                                        <th class="font-family-cairo fw-bold">رقم العملية</th>
                                        <th class="font-family-cairo fw-bold">الرقم الدفتري</th>
                                        <th class="font-family-cairo fw-bold">رقم الإيصال</th>
                                        <th class="font-family-cairo fw-bold">العميل</th>
                                        <th class="font-family-cairo fw-bold">الصندوق</th>
                                        <th class="font-family-cairo fw-bold">الموظف</th>
                                        <th class="font-family-cairo fw-bold">المبلغ</th>
                                        <th class="font-family-cairo fw-bold">البيان</th>
                                        <th class="font-family-cairo fw-bold">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($posVouchers as $index => $voucher)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center">{{ $voucher->pro_date }}</td>
                                            <td class="text-center">{{ $voucher->pro_id }}</td>
                                            <td class="text-center">{{ $voucher->pro_serial ?? '-' }}</td>
                                            <td class="text-center">{{ $voucher->pro_num ?? '-' }}</td>
                                            <td class="text-center">{{ $voucher->account1->aname ?? '-' }}</td>
                                            <td class="text-center">{{ $voucher->account2->aname ?? '-' }}</td>
                                            <td class="text-center">{{ $voucher->emp1->aname ?? '-' }}</td>
                                            <td class="text-center font-weight-bold text-success">
                                                {{ number_format($voucher->pro_value, 2) }}
                                            </td>
                                            <td class="text-center">{{ $voucher->details ?? '-' }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('pos-vouchers.show', $voucher->id) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('pos-vouchers.edit', $voucher->id) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            title="حذف"
                                                            onclick="confirmDelete({{ $voucher->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted font-family-cairo">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <br>
                                                لا توجد عمليات نقاط بيع
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        تأكيد الحذف
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body font-family-cairo">
                    هل أنت متأكد من حذف هذه العملية؟ لا يمكن التراجع عن هذا الإجراء.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary font-family-cairo" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        إلغاء
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger font-family-cairo">
                            <i class="fas fa-trash me-1"></i>
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .font-family-cairo {
        font-family: 'Cairo', sans-serif;
    }
    
    .table th, .table td {
        font-family: 'Cairo', sans-serif;
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin: 0 1px;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    function confirmDelete(voucherId) {
        const form = document.getElementById('deleteForm');
        form.action = `/pos-vouchers/${voucherId}`;
        $('#deleteModal').modal('show');
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@endpush
