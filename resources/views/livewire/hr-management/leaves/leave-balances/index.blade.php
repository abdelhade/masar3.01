<div class="container-fluid">
    <div class="row">
        <!-- رسائل النجاح -->
        @if (session()->has('message'))
            <div class="col-12">
                <div class="toast show" role="alert">
                    <div class="toast-header bg-success text-white">
                        <strong class="me-auto">نجح</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        {{ session('message') }}
                    </div>
                </div>
            </div>
        @endif
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">رصيد الإجازات</h3>
                </div>
                <div class="card-body">
                    <!-- فلاتر البحث -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control font-family-cairo fw-bold font-14"
                                placeholder="البحث في اسم الموظف...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الموظف</label>
                            <select wire:model.live="selectedEmployee" class="form-select font-family-cairo fw-bold font-14">
                                <option value="">جميع الموظفين</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">نوع الإجازة</label>
                            <select wire:model.live="selectedLeaveType" class="form-select font-family-cairo fw-bold font-14">
                                <option value="">جميع الأنواع</option>
                                @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">السنة</label>
                            <select wire:model.live="selectedYear" class="form-select font-family-cairo fw-bold font-14">
                                @for ($year = now()->year + 1; $year >= now()->year - 2; $year--)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <a href="{{ route('leaves.balances.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                                <i class="fas fa-plus"></i>
                                إضافة رصيد جديد
                            </a>
                        </div>
                    </div>

                    <!-- جدول رصيد الإجازات -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-white font-family-cairo fw-bold font-14">الموظف</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">نوع الإجازة</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">السنة</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">الرصيد الافتتاحي</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">المتراكم</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">المستخدم</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">المعلق</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">المحول</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">المتبقي</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($balances as $balance)
                                    <tr>
                                        <td class="font-family-cairo fw-bold font-14">{{ $balance->employee->name }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ $balance->leaveType->name }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ $balance->year }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ number_format($balance->opening_balance_days, 1) }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ number_format($balance->accrued_days, 1) }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ number_format($balance->used_days, 1) }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ number_format($balance->pending_days, 1) }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ number_format($balance->carried_over_days, 1) }}</td>
                                        <td>
                                            <span
                                                class="badge font-family-cairo fw-bold font-14 {{ $balance->remaining_days > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ number_format($balance->remaining_days, 1) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('leaves.balances.edit', $balance->id) }}"
                                                    class="btn btn-sm btn-warning font-family-cairo fw-bold font-14">
                                                    <i class="fas fa-edit" title="تعديل الرصيد"></i>
                                                </a>
                                                <button type="button" wire:click="deleteBalance({{ $balance->id }})"
                                                    wire:confirm="هل أنت متأكد من حذف هذا الرصيد؟"
                                                    class="btn btn-sm btn-danger font-family-cairo fw-bold font-14">
                                                    <i class="fas fa-trash" title="حذف الرصيد"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">لا توجد بيانات لعرضها</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- ترقيم الصفحات -->
                    <div class="d-flex justify-content-center">
                        {{ $balances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
