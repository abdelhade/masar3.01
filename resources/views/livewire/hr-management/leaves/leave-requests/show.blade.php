<div class="container-fluid">
    <div class="row">
        <!-- رسائل الخطأ -->
        @if (session()->has('error'))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if (session()->has('message'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <i class="fas fa-check-circle"></i>
                    {{ session('message') }}
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
            </div>
        @endif
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تفاصيل طلب الإجازة</h3>
                    <div class="card-tools">
                        <a href="{{ route('leaves.requests.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($request)
                        <!-- معلومات الطلب -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5>معلومات الطلب</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>الموظف:</strong></td>
                                        <td>{{ $request->employee->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>نوع الإجازة:</strong></td>
                                        <td>{{ $request->leaveType->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>تاريخ البداية:</strong></td>
                                        <td>{{ $request->start_date->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>تاريخ النهاية:</strong></td>
                                        <td>{{ $request->end_date->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>المدة:</strong></td>
                                        <td>{{ number_format($request->duration_days, 1) }} يوم</td>
                                    </tr>
                                    <tr>
                                        <td><strong>السبب:</strong></td>
                                        <td>{{ $request->reason ?: 'غير محدد' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center flex-column flex-md-row gap-2 p-2 mb-3">
                                    <h5>حالة الطلب</h5>
                                    <span class="badge fs-5 {{ $this->getStatusBadgeClass($request->status) }}">
                                        {{ $this->getStatusText($request->status) }}
                                    </span>
                                </div>
                                <div class="text-center mb-3">
                                    <p class="text-muted">{{ $this->getStatusDescription($request->status) }}</p>
                                </div>
                                
                                

                                @if ($request->approver && $request->approved_at)
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>المعتمد من:</strong></td>
                                            <td>{{ $request->approver->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>تاريخ الموافقة:</strong></td>
                                            <td>{{ $request->approved_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </table>
                                @endif

                                @if ($request->status === 'rejected' && $request->approver)
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>مرفوض من:</strong></td>
                                            <td>{{ $request->approver->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>تاريخ الرفض:</strong></td>
                                            <td>{{ $request->updated_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </table>
                                @endif

                                @if ($request->status === 'cancelled')
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>تاريخ الإلغاء:</strong></td>
                                            <td>{{ $request->updated_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </table>
                                @endif

                                <!-- أزرار الإجراءات -->
                                <div class="mt-3">
                                    @if ($request->status === 'draft')
                                        <button type="button" wire:click="submitRequest"
                                            wire:confirm="هل أنت متأكد من تقديم هذا الطلب؟"
                                            class="btn btn-primary btn-block mt-2">
                                            <i class="fas fa-paper-plane"></i>
                                            تقديم الطلب
                                        </button>
                                    @endif

                                    @if ($request->status === 'submitted' && auth()->user()->can('approve', $request))
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" wire:click="approveRequest"
                                                wire:confirm="هل أنت متأكد من الموافقة على هذا الطلب؟"
                                                class="btn btn-success">
                                                <i class="fas fa-check"></i>
                                                موافقة
                                            </button>
                                            <button type="button" wire:click="rejectRequest"
                                                wire:confirm="هل أنت متأكد من رفض هذا الطلب؟" class="btn btn-danger">
                                                <i class="fas fa-times"></i>
                                                رفض
                                            </button>
                                        </div>
                                    @endif

                                    @if (in_array($request->status, ['draft', 'submitted']) && auth()->user()->can('cancel', $request))
                                        <button type="button" wire:click="cancelRequest"
                                            wire:confirm="هل أنت متأكد من إلغاء هذا الطلب؟"
                                            class="btn btn-warning btn-block mt-2">
                                            <i class="fas fa-ban"></i>
                                            إلغاء الطلب
                                        </button>
                                    @endif

                                    @if (in_array($request->status, ['draft', 'submitted']) && auth()->user()->can('update', $request))
                                        <button type="button" wire:click="editRequest"
                                            wire:confirm="هل أنت متأكد من تعديل هذا الطلب؟"
                                            class="btn btn-info btn-block mt-2">
                                            <i class="fas fa-edit"></i>
                                            تعديل الطلب
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- خط زمني للحالة -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>خط زمني للحالة</h5>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <h6>إنشاء الطلب</h6>
                                            <p class="text-muted">{{ $request->created_at->format('Y-m-d H:i') }}</p>
                                        </div>
                                    </div>

                                    @if ($request->status !== 'draft')
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-info"></div>
                                            <div class="timeline-content">
                                                <h6>تقديم الطلب</h6>
                                                <p class="text-muted">{{ $request->updated_at->format('Y-m-d H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($request->status === 'approved' && $request->approved_at && $request->approver)
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6>موافقة على الطلب</h6>
                                                <p class="text-muted">{{ $request->approved_at->format('Y-m-d H:i') }}
                                                </p>
                                                <p>بواسطة: {{ $request->approver->name }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($request->status === 'rejected')
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-danger"></div>
                                            <div class="timeline-content">
                                                <h6>رفض الطلب</h6>
                                                <p class="text-muted">{{ $request->updated_at->format('Y-m-d H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($request->status === 'cancelled')
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-warning"></div>
                                            <div class="timeline-content">
                                                <h6>إلغاء الطلب</h6>
                                                <p class="text-muted">{{ $request->updated_at->format('Y-m-d H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            لم يتم العثور على الطلب المطلوب.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 50px;
        }

        .timeline-marker {
            position: absolute;
            left: 11px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 0 3px #e9ecef;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }

        .timeline-content h6 {
            margin: 0 0 5px 0;
            color: #495057;
        }

        .timeline-content p {
            margin: 0;
            font-size: 0.9em;
        }
    </style>
</div>
