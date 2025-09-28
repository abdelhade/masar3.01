<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-invoice me-2"></i>
                فاتورة {{ $invoice->type === 'buy' ? 'شراء' : 'بيع' }} - {{ $invoice->invoice_number }}
            </h3>
            <div class="card-tools">
                <a href="{{ route('services.invoices.index', ['type' => $invoice->type]) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i>
                    العودة للقائمة
                </a>
                @if($invoice->canBeEdited())
                    <a href="{{ route('services.invoices.edit', $invoice->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>
                        تعديل
                    </a>
                @endif
                <button class="btn btn-info" wire:click="print">
                    <i class="fas fa-print me-1"></i>
                    طباعة
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Invoice Header -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">معلومات الفاتورة</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>رقم الفاتورة:</strong></td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>تاريخ الفاتورة:</strong></td>
                                    <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                </tr>
                                @if($invoice->due_date)
                                    <tr>
                                        <td><strong>تاريخ الاستحقاق:</strong></td>
                                        <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>الحالة:</strong></td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'draft' => 'bg-secondary',
                                                'pending' => 'bg-warning',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'cancelled' => 'bg-dark',
                                            ];
                                            $statusLabels = [
                                                'draft' => 'مسودة',
                                                'pending' => 'في الانتظار',
                                                'approved' => 'معتمد',
                                                'rejected' => 'مرفوض',
                                                'cancelled' => 'ملغي',
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$invoice->status] ?? 'bg-secondary' }}">
                                            {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                {{ $invoice->type === 'buy' ? 'معلومات المورد' : 'معلومات العميل' }}
                            </h6>
                            <table class="table table-sm table-borderless">
                                @if($invoice->type === 'buy' && $invoice->supplier)
                                    <tr>
                                        <td><strong>اسم المورد:</strong></td>
                                        <td>{{ $invoice->supplier->name }}</td>
                                    </tr>
                                    @if($invoice->supplier->phone)
                                        <tr>
                                            <td><strong>الهاتف:</strong></td>
                                            <td>{{ $invoice->supplier->phone }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->supplier->email)
                                        <tr>
                                            <td><strong>البريد الإلكتروني:</strong></td>
                                            <td>{{ $invoice->supplier->email }}</td>
                                        </tr>
                                    @endif
                                @elseif($invoice->type === 'sell' && $invoice->customer)
                                    <tr>
                                        <td><strong>اسم العميل:</strong></td>
                                        <td>{{ $invoice->customer->name }}</td>
                                    </tr>
                                    @if($invoice->customer->phone)
                                        <tr>
                                            <td><strong>الهاتف:</strong></td>
                                            <td>{{ $invoice->customer->phone }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->customer->email)
                                        <tr>
                                            <td><strong>البريد الإلكتروني:</strong></td>
                                            <td>{{ $invoice->customer->email }}</td>
                                        </tr>
                                    @endif
                                @else
                                    <tr>
                                        <td colspan="2" class="text-muted">غير محدد</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        عناصر الفاتورة
                    </h5>
                </div>
                <div class="card-body">
                    @if($invoice->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>الخدمة</th>
                                        <th>الوحدة</th>
                                        <th>الكمية</th>
                                        <th>سعر الوحدة</th>
                                        <th>الخصم</th>
                                        <th>الضريبة</th>
                                        <th>المجموع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $item->service->name }}</div>
                                                @if($item->description)
                                                    <small class="text-muted">{{ $item->description }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->serviceUnit)
                                                    <span class="badge bg-info">{{ $item->serviceUnit->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($item->quantity, 3) }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }} ر.س</td>
                                            <td>
                                                @if($item->discount_percentage > 0)
                                                    <div>{{ $item->discount_percentage }}%</div>
                                                    <small class="text-muted">{{ number_format($item->discount_amount, 2) }} ر.س</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->tax_percentage > 0)
                                                    <div>{{ $item->tax_percentage }}%</div>
                                                    <small class="text-muted">{{ number_format($item->tax_amount, 2) }} ر.س</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    {{ number_format($item->line_total, 2) }} ر.س
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">لا توجد عناصر في الفاتورة</h6>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Totals -->
            <div class="row mb-4">
                <div class="col-md-8">
                    @if($invoice->notes)
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">ملاحظات</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $invoice->notes }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($invoice->terms_conditions)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">الشروط والأحكام</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $invoice->terms_conditions }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="card-title mb-0">إجماليات الفاتورة</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>المجموع الفرعي:</span>
                                <span class="fw-bold">{{ number_format($invoice->subtotal, 2) }} ر.س</span>
                            </div>
                            @if($invoice->discount_amount > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>إجمالي الخصم:</span>
                                    <span class="fw-bold text-warning">-{{ number_format($invoice->discount_amount, 2) }} ر.س</span>
                                </div>
                            @endif
                            @if($invoice->tax_amount > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>إجمالي الضريبة:</span>
                                    <span class="fw-bold text-info">+{{ number_format($invoice->tax_amount, 2) }} ر.س</span>
                                </div>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">المجموع الكلي:</span>
                                <span class="fw-bold text-success fs-5">{{ number_format($invoice->total_amount, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-2">
                @if($invoice->canBeApproved())
                    <button wire:click="approve" 
                            class="btn btn-success"
                            onclick="return confirm('هل أنت متأكد من اعتماد هذه الفاتورة؟')">
                        <i class="fas fa-check me-1"></i>
                        اعتماد الفاتورة
                    </button>
                    <button wire:click="reject" 
                            class="btn btn-danger"
                            onclick="return confirm('هل أنت متأكد من رفض هذه الفاتورة؟')">
                        <i class="fas fa-times me-1"></i>
                        رفض الفاتورة
                    </button>
                @endif

                @if(in_array($invoice->status, ['draft', 'pending']))
                    <button wire:click="cancel" 
                            class="btn btn-warning"
                            onclick="return confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟')">
                        <i class="fas fa-ban me-1"></i>
                        إلغاء الفاتورة
                    </button>
                @endif

                @if($invoice->canBeDeleted())
                    <button wire:click="delete" 
                            class="btn btn-danger"
                            onclick="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                        <i class="fas fa-trash me-1"></i>
                        حذف الفاتورة
                    </button>
                @endif
            </div>

            <!-- Invoice Info -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <strong>أنشئ بواسطة:</strong> {{ $invoice->creator->name ?? 'غير محدد' }}<br>
                                        <strong>تاريخ الإنشاء:</strong> {{ $invoice->created_at->format('Y-m-d H:i:s') }}
                                    </small>
                                </div>
                                @if($invoice->updater)
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            <strong>آخر تحديث بواسطة:</strong> {{ $invoice->updater->name }}<br>
                                            <strong>تاريخ التحديث:</strong> {{ $invoice->updated_at->format('Y-m-d H:i:s') }}
                                        </small>
                                    </div>
                                @endif
                                @if($invoice->approver)
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            <strong>اعتمد بواسطة:</strong> {{ $invoice->approver->name }}<br>
                                            <strong>تاريخ الاعتماد:</strong> {{ $invoice->approved_at->format('Y-m-d H:i:s') }}
                                        </small>
                                    </div>
                                @endif
                                @if($invoice->branch)
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            <strong>الفرع:</strong> {{ $invoice->branch->name }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Component -->
    <div x-data="{ show: false, type: '', message: '' }" 
         x-on:show-alert.window="show = true; type = $event.detail.type; message = $event.detail.message; setTimeout(() => show = false, 5000)"
         x-show="show" 
         x-transition
         class="position-fixed top-0 end-0 p-3" 
         style="z-index: 1050;">
        <div class="alert alert-dismissible fade show" 
             :class="{
                 'alert-success': type === 'success',
                 'alert-danger': type === 'error',
                 'alert-warning': type === 'warning',
                 'alert-info': type === 'info'
             }" 
             role="alert">
            <span x-text="message"></span>
            <button type="button" class="btn-close" @click="show = false"></button>
        </div>
    </div>
</div>
