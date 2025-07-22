@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>قائمة مراكز التكلفة</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="as_of_date">حتى تاريخ:</label>
                    <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                </div>
                <div class="col-md-3">
                    <label for="cost_center_type">نوع مركز التكلفة:</label>
                    <select id="cost_center_type" class="form-control" wire:model="costCenterType">
                        <option value="">الكل</option>
                        <option value="department">إدارة</option>
                        <option value="project">مشروع</option>
                        <option value="branch">فرع</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search">بحث:</label>
                    <input type="text" id="search" class="form-control" wire:model="search" placeholder="اسم مركز التكلفة">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">توليد التقرير</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>كود مركز التكلفة</th>
                            <th>اسم مركز التكلفة</th>
                            <th>النوع</th>
                            <th>المسؤول</th>
                            <th class="text-end">إجمالي المصروفات</th>
                            <th class="text-end">إجمالي الإيرادات</th>
                            <th class="text-end">صافي التكلفة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($costCenters as $center)
                        <tr>
                            <td>{{ $center->code ?? '---' }}</td>
                            <td>{{ $center->name ?? '---' }}</td>
                            <td>{{ $center->type ?? '---' }}</td>
                            <td>{{ $center->manager->name ?? '---' }}</td>
                            <td class="text-end">{{ number_format($center->total_expenses, 2) }}</td>
                            <td class="text-end">{{ number_format($center->total_revenues, 2) }}</td>
                            <td class="text-end">{{ number_format($center->net_cost, 2) }}</td>
                            <td>
                                @if($center->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="4">الإجمالي</th>
                            <th class="text-end">{{ number_format($totalExpenses, 2) }}</th>
                            <th class="text-end">{{ number_format($totalRevenues, 2) }}</th>
                            <th class="text-end">{{ number_format($totalNetCost, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($costCenters->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $costCenters->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>إجمالي مراكز التكلفة:</strong> {{ $totalCostCenters }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>مراكز التكلفة النشطة:</strong> {{ $activeCostCenters }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>متوسط التكلفة للمركز:</strong> {{ number_format($averageCostPerCenter, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>صافي التكلفة الإجمالي:</strong> {{ number_format($totalNetCost, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 