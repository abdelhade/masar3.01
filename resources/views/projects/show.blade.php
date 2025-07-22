@extends('admin.dashboard')
@section('content')

<style>
    .project-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .project-info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border-left: 4px solid #667eea;
        transition: transform 0.3s ease;
    }
    
    .project-info-card:hover {
        transform: translateY(-2px);
    }
    
    .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.8rem;
        padding: 0.5rem 0;
    }
    
    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 1rem;
        font-size: 1.2rem;
        color: white;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: bold;
        font-size: 0.9rem;
        text-align: center;
        display: inline-block;
        min-width: 120px;
    }
    
    .status-active {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
    }
    
    .status-completed {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
    }
    
    .status-pending {
        background: linear-gradient(45deg, #ffc107, #e0a800);
        color: #212529;
    }
    
    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .section-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .section-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
    }
    
    .section-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 1rem;
        font-size: 1.5rem;
        color: white;
    }
    
    .table-custom {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table-custom thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem;
        font-weight: 600;
    }
    
    .table-custom tbody tr {
        transition: background-color 0.3s ease;
    }
    
    .table-custom tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .amount-positive {
        color: #28a745;
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    .amount-negative {
        color: #dc3545;
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    .btn-view {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.4);
        color: white;
    }
    
    .summary-row {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: bold;
    }
    
    .summary-row td {
        padding: 1rem;
        font-size: 1.1rem;
    }
    
    @media (max-width: 768px) {
        .project-header {
            padding: 1rem;
        }
        
        .section-card {
            margin-bottom: 1rem;
        }
        
        .table-responsive {
            font-size: 0.9rem;
        }
    }
</style>

<!-- project head -->



<div class="project-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-3">
                <i class="fas fa-project-diagram me-3"></i>
                مشروع {{ $project->name }}
            </h2>
            <p class="mb-0 opacity-75">
                <i class="fas fa-info-circle me-2"></i>
                {{ $project->description }}
            </p>
        </div>
        <div class="col-md-4 text-end">
            @if($project->status == 'active')
                <span class="status-badge status-active">
                    <i class="fas fa-play-circle me-2"></i>
                    نشط
                </span>
            @elseif($project->status == 'completed')
                <span class="status-badge status-completed">
                    <i class="fas fa-check-circle me-2"></i>
                    مكتمل
                </span>
            @else
                <span class="status-badge status-pending">
                    <i class="fas fa-clock me-2"></i>
                    معلق
                </span>
            @endif
        </div>
    </div>
</div>

<div class="card-body">
    <!-- بيانات عامة عن المشروع -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="project-info-card">
                <div class="info-item">
                    <div class="info-icon" style="background: linear-gradient(45deg, #28a745, #20c997);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <strong>تاريخ البدء:</strong>
                        <span class="ms-2">{{ $project->start_date }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon" style="background: linear-gradient(45deg, #ffc107, #e0a800);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <strong>تاريخ الانتهاء المتوقع:</strong>
                        <span class="ms-2">{{ $project->end_date }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon" style="background: linear-gradient(45deg, #007bff, #0056b3);">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div>
                        <strong>تاريخ الانتهاء الفعلي:</strong>
                        <span class="ms-2">{{ $project->actual_end_date ?? 'لم ينتهي بعد' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="project-info-card">
                <div class="info-item">
                    <div class="info-icon" style="background: linear-gradient(45deg, #6f42c1, #5a2d91);">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <strong>أنشئ بواسطة:</strong>
                        <span class="ms-2">{{ $project->createdBy->name }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon" style="background: linear-gradient(45deg, #fd7e14, #e55a00);">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <strong>تم التحديث بواسطة:</strong>
                        <span class="ms-2">{{ $project->updatedBy->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- project body -->
    <!-- equipments and vouchers and operations -->
    <div class="row">
        <!-- operations -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(45deg, #28a745, #20c997);">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h5 class="card-title mb-0">العمليات</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-tasks me-2"></i>العملية</th>
                                    <th><i class="fas fa-money-bill-wave me-2"></i>المبلغ</th>
                                    <th><i class="fas fa-calendar me-2"></i>تاريخ السند</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($operations as $operation)
                                    <tr>
                                        <td>{{ $operation->type->ptext }}</td>
                                        <td class="amount-positive">{{ number_format($operation->pro_value, 2) }}</td>
                                        <td>{{ $operation->pro_date }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-3"></i>
                                            <br>لا توجد عمليات
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- equipments -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(45deg, #fd7e14, #e55a00);">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h5 class="card-title mb-0">المعدات</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-tools me-2"></i>المعده</th>
                                    <th><i class="fas fa-calendar-plus me-2"></i>تاريخ التأجير</th>
                                    <th><i class="fas fa-calendar-minus me-2"></i>تاريخ الانتهاء</th>
                                    <th><i class="fas fa-money-bill-wave me-2"></i>المبلغ</th>
                                    <th><i class="fas fa-cog me-2"></i>العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($equipmentOperations as $equipmentOp)
                                    <tr>
                                        <td>{{ $equipmentOp['equipment']->aname }}</td>
                                        <td>{{ $equipmentOp['operation']->start_date }}</td>
                                        <td>{{ $equipmentOp['operation']->end_date }}</td>
                                        <td class="amount-positive">{{ number_format($equipmentOp['operation']->pro_value, 2) }}</td>
                                        <td>
                                            <a href="{{ route('rentals.edit', $equipmentOp['operation']->id) }}" class="btn btn-view">
                                                <i class="fas fa-eye me-1"></i>عرض
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-truck fa-2x mb-3"></i>
                                            <br>لا توجد معدات
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- vouchers -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(45deg, #6f42c1, #5a2d91);">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h5 class="card-title mb-0">المقبوضات و المدفوعات</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-file-invoice me-2"></i>نوع السند</th>
                                    <th><i class="fas fa-calendar me-2"></i>تاريخ السند</th>
                                    <th><i class="fas fa-arrow-up text-success me-2"></i>المقبوض</th>
                                    <th><i class="fas fa-arrow-down text-danger me-2"></i>المدفوع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vouchers as $voucher)
                                    <tr>
                                        <td>{{ $voucher->type->ptext }}</td>
                                        <td>{{ $voucher->pro_date }}</td>
                                        <td class="amount-positive">{{ $voucher->pro_type == 1 ? number_format($voucher->pro_value, 2) : '0.00' }}</td>
                                        <td class="amount-negative">{{ $voucher->pro_type == 2 ? number_format($voucher->pro_value, 2) : '0.00' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-receipt fa-2x mb-3"></i>
                                            <br>لا توجد سندات
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="summary-row">
                                    <td><i class="fas fa-calculator me-2"></i>المجموع</td>
                                    <td></td>
                                    <td class="amount-positive">{{ number_format($vouchers->where('pro_type', 1)->sum('pro_value'), 2) }}</td>
                                    <td class="amount-negative">{{ number_format($vouchers->where('pro_type', 2)->sum('pro_value'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection