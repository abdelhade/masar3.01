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
                    <h3 class="card-title">طلبات الإجازة</h3>
                </div>
                <div class="card-body">
                    <!-- فلاتر البحث -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label">البحث</label>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                placeholder="البحث في اسم الموظف...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الموظف</label>
                            <select wire:model.live="selectedEmployee" class="form-select font-family-cairo fw-bold font-14">
                                <option value="">جميع الموظفين</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">نوع الإجازة</label>
                            <select wire:model.live="selectedLeaveType" class="form-select font-family-cairo fw-bold font-14">
                                <option value="">جميع الأنواع</option>
                                @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select wire:model.live="selectedStatus" class="form-select font-family-cairo fw-bold font-14">
                                <option value="">جميع الحالات</option>
                                <option value="draft">مسودة</option>
                                <option value="submitted">مقدم</option>
                                <option value="approved">معتمد</option>
                                <option value="rejected">مرفوض</option>
                                <option value="cancelled">ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" wire:model.live="dateFrom" class="form-control font-family-cairo fw-bold font-14">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" wire:model.live="dateTo" class="form-control font-family-cairo fw-bold font-14">
                        </div>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <a href="{{ route('leaves.requests.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                                <i class="fas fa-plus"></i>
                                طلب إجازة جديد
                            </a>
                        </div>
                    </div>

                    <!-- جدول طلبات الإجازة -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-white font-family-cairo fw-bold font-14">الموظف</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">نوع الإجازة</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">تاريخ البداية</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">تاريخ النهاية</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">المدة (أيام)</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">الحالة</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">المعتمد من</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">تاريخ التقديم</th>
                                    <th class="text-white font-family-cairo fw-bold font-14">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td class="font-family-cairo fw-bold font-14">{{ $request->employee->name }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ $request->leaveType->name }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ $request->start_date->format('Y-m-d') }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ $request->end_date->format('Y-m-d') }}</td>
                                        <td class="font-family-cairo fw-bold font-14">{{ number_format($request->duration_days, 1) }}</td>
                                        <td>
                                            <span class="badge font-family-cairo fw-bold font-14 {{ $this->getStatusBadgeClass($request->status) }}">
                                                {{ $this->getStatusText($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($request->approver)
                                                {{ $request->approver->name }}
                                                @if ($request->approved_at)
                                                    <br><small
                                                        class="text-muted font-family-cairo fw-bold font-14">{{ $request->approved_at->format('Y-m-d H:i') }}</small>
                                                @else
                                                    <br><small
                                                    class="text-muted font-family-cairo fw-bold font-14">{{ $request->updated_at->format('Y-m-d H:i') }}</small>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="font-family-cairo fw-bold font-14">{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('leaves.requests.show', $request->id) }}"
                                                    class="btn btn-sm btn-info font-family-cairo fw-bold font-14">
                                                    <i class="fas fa-eye" title="عرض الطلب"></i>
                                                </a>
                                                @if ($request->canBeApproved())
                                                    <button type="button"
                                                        wire:click="approveRequest({{ $request->id }})"
                                                        class="btn btn-sm btn-success font-family-cairo fw-bold font-14">
                                                        <i class="fas fa-check" title="موافقة على الطلب"></i>
                                                    </button>
                                                @endif
                                                @if ($request->canBeRejected())
                                                    <button type="button"
                                                        wire:click="rejectRequest({{ $request->id }})"
                                                        class="btn btn-sm btn-danger font-family-cairo fw-bold font-14">
                                                        <i class="fas fa-times" title="رفض الطلب"></i>
                                                    </button>
                                                @endif
                                                @if ($request->canBeCancelled())
                                                    <button type="button"
                                                        wire:click="cancelRequest({{ $request->id }})"
                                                        class="btn btn-sm btn-warning font-family-cairo fw-bold font-14">
                                                        <i class="fas fa-ban" title="إلغاء الطلب"></i>
                                                    </button>
                                                @endif
                                                <button type="button" wire:click="deleteRequest({{ $request->id }})"
                                                    wire:confirm="هل أنت متأكد من حذف هذا الطلب؟"
                                                    class="btn btn-sm btn-danger font-family-cairo fw-bold font-14">
                                                    <i class="fas fa-trash" title="حذف الطلب"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center font-family-cairo fw-bold font-14">لا توجد بيانات لعرضها</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- ترقيم الصفحات -->
                    <div class="d-flex justify-content-center">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
