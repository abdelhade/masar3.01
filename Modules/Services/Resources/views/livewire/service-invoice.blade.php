<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="fas fa-file-invoice me-2"></i>
                فواتير الخدمات - {{ $type === 'buy' ? 'الشراء' : 'البيع' }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('services.invoices.create', ['type' => $type]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    إضافة فاتورة جديدة
                </a>
                <div class="btn-group" role="group">
                    <a href="{{ route('services.invoices.index', ['type' => 'sell']) }}" 
                       class="btn {{ $type === 'sell' ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="fas fa-shopping-cart me-1"></i>
                        فواتير البيع
                    </a>
                    <a href="{{ route('services.invoices.index', ['type' => 'buy']) }}" 
                       class="btn {{ $type === 'buy' ? 'btn-info' : 'btn-outline-info' }}">
                        <i class="fas fa-shopping-bag me-1"></i>
                        فواتير الشراء
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" 
                           class="form-control" 
                           placeholder="البحث في رقم الفاتورة أو اسم {{ $type === 'buy' ? 'المورد' : 'العميل' }}..."
                           wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">جميع الحالات</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" 
                           class="form-control" 
                           placeholder="من تاريخ"
                           wire:model.live="dateFrom">
                </div>
                <div class="col-md-2">
                    <input type="date" 
                           class="form-control" 
                           placeholder="إلى تاريخ"
                           wire:model.live="dateTo">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="perPage">
                        <option value="15">15 لكل صفحة</option>
                        <option value="25">25 لكل صفحة</option>
                        <option value="50">50 لكل صفحة</option>
                        <option value="100">100 لكل صفحة</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary" 
                            wire:click="$set('search', ''); $set('statusFilter', ''); $set('dateFrom', ''); $set('dateTo', '')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>التاريخ</th>
                                <th>{{ $type === 'buy' ? 'المورد' : 'العميل' }}</th>
                                <th>المجموع الفرعي</th>
                                <th>الخصم</th>
                                <th>الضريبة</th>
                                <th>المجموع الكلي</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('services.invoices.show', $invoice->id) }}" 
                                           class="text-decoration-none fw-bold">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div>{{ $invoice->invoice_date->format('Y-m-d') }}</div>
                                        @if($invoice->due_date)
                                            <small class="text-muted">
                                                استحقاق: {{ $invoice->due_date->format('Y-m-d') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($type === 'buy' && $invoice->supplier)
                                            <span class="badge bg-info">{{ $invoice->supplier->name }}</span>
                                        @elseif($type === 'sell' && $invoice->customer)
                                            <span class="badge bg-success">{{ $invoice->customer->name }}</span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-primary fw-bold">
                                            {{ number_format($invoice->subtotal, 2) }} ر.س
                                        </span>
                                    </td>
                                    <td>
                                        @if($invoice->discount_amount > 0)
                                            <span class="text-warning">
                                                {{ number_format($invoice->discount_amount, 2) }} ر.س
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->tax_amount > 0)
                                            <span class="text-info">
                                                {{ number_format($invoice->tax_amount, 2) }} ر.س
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold">
                                            {{ number_format($invoice->total_amount, 2) }} ر.س
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'draft' => 'bg-secondary',
                                                'pending' => 'bg-warning',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'cancelled' => 'bg-dark',
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$invoice->status] ?? 'bg-secondary' }}">
                                            {{ $statuses[$invoice->status] ?? $invoice->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('services.invoices.show', $invoice->id) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($invoice->canBeEdited())
                                                <a href="{{ route('services.invoices.edit', $invoice->id) }}" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @if($invoice->canBeApproved())
                                                <button wire:click="approveInvoice({{ $invoice->id }})" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="اعتماد"
                                                        onclick="return confirm('هل أنت متأكد من اعتماد هذه الفاتورة؟')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button wire:click="rejectInvoice({{ $invoice->id }})" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="رفض"
                                                        onclick="return confirm('هل أنت متأكد من رفض هذه الفاتورة؟')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif

                                            @if(in_array($invoice->status, ['draft', 'pending']))
                                                <button wire:click="cancelInvoice({{ $invoice->id }})" 
                                                        class="btn btn-sm btn-outline-dark" 
                                                        title="إلغاء"
                                                        onclick="return confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif

                                            @if($invoice->canBeDeleted())
                                                <button wire:click="deleteInvoice({{ $invoice->id }})" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="حذف"
                                                        onclick="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد فواتير</h5>
                    <p class="text-muted">ابدأ بإضافة فاتورة جديدة</p>
                    <a href="{{ route('services.invoices.create', ['type' => $type]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة فاتورة جديدة
                    </a>
                </div>
            @endif
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
