<?php

use Livewire\Volt\Component;
use App\Models\ProductionOrder;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public ProductionOrder $productionOrder;
    
    public function mount(ProductionOrder $productionOrder)
    {
        $this->productionOrder = $productionOrder->load([
            'items', 
            'customer', 
            'createdBy', 
            'updatedBy',
            'productionInvoice'
        ]);
    }
    
    public function delete()
    {
        // Check if order can be deleted (not completed)
        if ($this->productionOrder->status === 'completed') {
            session()->flash('error', 'لا يمكن حذف أمر إنتاج مكتمل');
            return;
        }
        
        try {
            // Delete related items first
            $this->productionOrder->items()->detach();
            
            // Then delete the production order
            $this->productionOrder->delete();
            
            session()->flash('success', 'تم حذف أمر الإنتاج بنجاح');
            return redirect()->route('production-orders.index');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء حذف أمر الإنتاج: ' . $e->getMessage());
        }
    }
    
    public function confirmDelete()
    {
        $this->dispatch('confirm-delete-view');
    }
    
    public function getStatusLabel($status)
    {
        return match($status) {
            'pending' => 'قيد الانتظار',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => $status
        };
    }
    
    public function getStatusColor($status)
    {
        return match($status) {
            'pending' => 'badge bg-warning',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary'
        };
    }
}; ?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-3">
            <h2 class="mb-1">تفاصيل أمر الإنتاج</h2>
            <p class="text-muted">رقم الأمر: {{ $this->productionOrder->order_number }}</p>
        </div>
        <div class="col-md-6 text-end mt-4">
            <a href="{{ route('production-orders.index') }}" class="btn btn-secondary me-2 font-hold fw-bold">
                <i class="fas fa-arrow-left"></i>
                العودة
            </a>
            @if($this->productionOrder->status !== 'completed')
                <a href="{{ route('production-orders.edit', $this->productionOrder->id) }}" class="btn btn-warning me-2 font-hold fw-bold">
                    <i class="fas fa-edit"></i>
                    تعديل
                </a>
                <button wire:click="confirmDelete" class="btn btn-danger font-hold fw-bold">
                     <i class="fas fa-trash"></i>
                     حذف
                 </button>
            @endif
        </div>
    </div>

    <!-- Status Badge -->
    <div class="row mb-4">
        <div class="col-12">
            <span class="{{ $this->getStatusColor($this->productionOrder->status) }} fs-6">
                {{ $this->getStatusLabel($this->productionOrder->status) }}
            </span>
        </div>
    </div>

    <!-- Basic Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">المعلومات الأساسية</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>رقم الأمر:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $this->productionOrder->order_number }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>تاريخ الأمر:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $this->productionOrder->order_date->format('Y-m-d') }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>العميل:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $this->productionOrder->customer->aname ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>المبلغ الإجمالي:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ number_format($this->productionOrder->total_amount, 2) }} ريال
                        </div>
                    </div>
                    
                    @if($this->productionOrder->notes)
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>ملاحظات:</strong>
                            </div>
                            <div class="col-sm-8">
                                {{ $this->productionOrder->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">معلومات النظام</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>أنشئ بواسطة:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $this->productionOrder->createdBy->name ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>تاريخ الإنشاء:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $this->productionOrder->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>
                    
                    @if($this->productionOrder->updatedBy)
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>عدل بواسطة:</strong>
                            </div>
                            <div class="col-sm-8">
                                {{ $this->productionOrder->updatedBy->name ?? '-' }}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>تاريخ التعديل:</strong>
                            </div>
                            <div class="col-sm-8">
                                {{ $this->productionOrder->updated_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    @endif
                    
                    @if($this->productionOrder->productionInvoice)
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>فترة الإنتاج المرتبطة:</strong>
                            </div>
                            <div class="col-sm-8">
                                رقم {{ $this->productionOrder->productionInvoice->id }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">الأصناف المطلوبة</h5>
        </div>
        <div class="card-body">
            @if($this->productionOrder->items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-white">الصنف</th>
                                <th class="text-white">الرمز</th>
                                <th class="text-white">الكمية</th>
                                <th class="text-white">ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->productionOrder->items as $item)
                                <tr>
                                    <td class="font-hold fw-bold">{{ $item->name }}</td>
                                    <td class="font-hold fw-bold">{{ $item->code }}</td>
                                    <td class="font-hold fw-bold">{{ number_format($item->pivot->quantity, 2) }}</td>
                                    <td class="font-hold fw-bold">{{ $item->pivot->note ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h5>لا توجد أصناف</h5>
                    <p class="text-muted">لم يتم إضافة أي أصناف لهذا الأمر</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    @if($this->productionOrder->status === 'pending')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">إجراءات سريعة</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button class="btn btn-success font-hold fw-bold">
                        <i class="fas fa-check"></i>
                        إكمال الأمر
                    </button>
                    
                    <button class="btn btn-danger font-hold fw-bold">
                        <i class="fas fa-times"></i>
                        إلغاء الأمر
                    </button>
                    
                    <button class="btn btn-info font-hold fw-bold">
                        <i class="fas fa-file-alt"></i>
                        إنشاء فاتورة إنتاج
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('confirm-delete-view', () => {
        if (confirm('هل أنت متأكد من حذف هذا الأمر؟')) {
            @this.delete();
        }
    });
});
</script>  