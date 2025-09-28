<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-{{ $invoice ? 'edit' : 'plus' }} me-2"></i>
                {{ $invoice ? 'تعديل الفاتورة' : 'إضافة فاتورة جديدة' }} - {{ $type === 'buy' ? 'الشراء' : 'البيع' }}
            </h3>
            <div class="card-tools">
                <a href="{{ route('services.invoices.index', ['type' => $type]) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i>
                    العودة للقائمة
                </a>
            </div>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="save">
                <!-- Invoice Header -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">نوع الفاتورة</label>
                        <select class="form-select" wire:model="type" {{ $invoice ? 'disabled' : '' }}>
                            <option value="sell">فاتورة بيع</option>
                            <option value="buy">فاتورة شراء</option>
                        </select>
                        @error('type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model="invoice_date">
                        @error('invoice_date') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" class="form-control" wire:model="due_date">
                        @error('due_date') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">حالة الفاتورة</label>
                        <select class="form-select" wire:model="status">
                            <option value="draft">مسودة</option>
                            <option value="pending">في الانتظار</option>
                            <option value="approved">معتمد</option>
                            <option value="rejected">مرفوض</option>
                            <option value="cancelled">ملغي</option>
                        </select>
                        @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    @if($type === 'buy')
                        <div class="col-md-6">
                            <label class="form-label">المورد <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="supplier_id">
                                <option value="">اختر المورد</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    @else
                        <div class="col-md-6">
                            <label class="form-label">العميل <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="customer_id">
                                <option value="">اختر العميل</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">الفرع</label>
                        <select class="form-select" wire:model="branch_id">
                            <option value="">اختر الفرع</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>
                            عناصر الفاتورة
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="addNewItem">
                            <i class="fas fa-plus me-1"></i>
                            إضافة عنصر
                        </button>
                    </div>
                    <div class="card-body">
                        @if(count($items) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الخدمة</th>
                                            <th>الوحدة</th>
                                            <th>الكمية</th>
                                            <th>سعر الوحدة</th>
                                            <th>الخصم %</th>
                                            <th>الضريبة %</th>
                                            <th>المجموع</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $index => $item)
                                            <tr>
                                                <td>
                                                    <select class="form-select form-select-sm" 
                                                            wire:model="items.{{ $index }}.service_id"
                                                            wire:change="updatedItems">
                                                        <option value="">اختر الخدمة</option>
                                                        @foreach($services as $service)
                                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error("items.{$index}.service_id") 
                                                        <div class="text-danger small">{{ $message }}</div> 
                                                    @enderror
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" 
                                                            wire:model="items.{{ $index }}.service_unit_id">
                                                        <option value="">اختر الوحدة</option>
                                                        @foreach($serviceUnits as $unit)
                                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm" 
                                                           step="0.001"
                                                           min="0.001"
                                                           wire:model="items.{{ $index }}.quantity"
                                                           wire:change="updatedItems">
                                                    @error("items.{$index}.quantity") 
                                                        <div class="text-danger small">{{ $message }}</div> 
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm" 
                                                           step="0.01"
                                                           min="0"
                                                           wire:model="items.{{ $index }}.unit_price"
                                                           wire:change="updatedItems">
                                                    @error("items.{$index}.unit_price") 
                                                        <div class="text-danger small">{{ $message }}</div> 
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm" 
                                                           step="0.01"
                                                           min="0"
                                                           max="100"
                                                           wire:model="items.{{ $index }}.discount_percentage"
                                                           wire:change="updatedItems">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm" 
                                                           step="0.01"
                                                           min="0"
                                                           max="100"
                                                           wire:model="items.{{ $index }}.tax_percentage"
                                                           wire:change="updatedItems">
                                                </td>
                                                <td>
                                                    @php
                                                        $lineSubtotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                                        $discountAmount = ($lineSubtotal * ($item['discount_percentage'] ?? 0)) / 100;
                                                        $discountedAmount = $lineSubtotal - $discountAmount;
                                                        $taxAmount = ($discountedAmount * ($item['tax_percentage'] ?? 0)) / 100;
                                                        $lineTotal = $discountedAmount + $taxAmount;
                                                    @endphp
                                                    <span class="fw-bold text-success">
                                                        {{ number_format($lineTotal, 2) }} ر.س
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            wire:click="removeItem({{ $index }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <p class="text-muted">لا توجد عناصر في الفاتورة</p>
                                <button type="button" class="btn btn-primary" wire:click="addNewItem">
                                    <i class="fas fa-plus me-1"></i>
                                    إضافة عنصر جديد
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Totals -->
                <div class="row mb-4">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>المجموع الفرعي:</span>
                                    <span class="fw-bold">{{ number_format($subtotal, 2) }} ر.س</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>إجمالي الخصم:</span>
                                    <span class="fw-bold text-warning">{{ number_format($totalDiscount, 2) }} ر.س</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>إجمالي الضريبة:</span>
                                    <span class="fw-bold text-info">{{ number_format($totalTax, 2) }} ر.س</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">المجموع الكلي:</span>
                                    <span class="fw-bold text-success fs-5">{{ number_format($totalAmount, 2) }} ر.س</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" rows="3" wire:model="notes"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الشروط والأحكام</label>
                        <textarea class="form-control" rows="3" wire:model="terms_conditions"></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('services.invoices.index', ['type' => $type]) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>
                        إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ $invoice ? 'تحديث الفاتورة' : 'حفظ الفاتورة' }}
                    </button>
                </div>
            </form>
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
