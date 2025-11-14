<?php

namespace Modules\Services\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Services\Models\ServiceInvoice as ServiceInvoiceModel;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceUnit;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;

class ServiceInvoice extends Component
{
    use WithPagination;

    public $type = 'sell'; // 'buy' or 'sell'
    public $search = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    protected $queryString = [
        'type' => ['except' => 'sell'],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
        $this->reset(['search', 'statusFilter', 'dateFrom', 'dateTo']);
    }

    public function render()
    {
        $invoices = ServiceInvoiceModel::query()
            ->where('type', $this->type)
            ->with(['supplier', 'customer', 'branch', 'creator'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function ($supplierQuery) {
                          $supplierQuery->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('customer', function ($customerQuery) {
                          $customerQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('invoice_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('invoice_date', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('services::livewire.service-invoice', [
            'invoices' => $invoices,
            'statuses' => [
                'draft' => 'مسودة',
                'pending' => 'في الانتظار',
                'approved' => 'معتمد',
                'rejected' => 'مرفوض',
                'cancelled' => 'ملغي',
            ],
        ]);
    }

    public function deleteInvoice($invoiceId)
    {
        $invoice = ServiceInvoiceModel::findOrFail($invoiceId);
        
        if (!in_array($invoice->status, ['draft', 'pending'])) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن حذف هذه الفاتورة'
            ]);
            return;
        }

        $invoice->delete();
        
        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم حذف الفاتورة بنجاح'
        ]);
    }

    public function approveInvoice($invoiceId)
    {
        $invoice = ServiceInvoiceModel::findOrFail($invoiceId);
        
        if (!$invoice->canBeApproved()) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن اعتماد هذه الفاتورة'
            ]);
            return;
        }

        $invoice->update([
            'status' => 'approved',
        ]);
        
        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم اعتماد الفاتورة بنجاح'
        ]);
    }

    public function rejectInvoice($invoiceId)
    {
        $invoice = ServiceInvoiceModel::findOrFail($invoiceId);
        
        if (!$invoice->canBeApproved()) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن رفض هذه الفاتورة'
            ]);
            return;
        }

        $invoice->update([
            'status' => 'rejected',
        ]);
        
        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم رفض الفاتورة بنجاح'
        ]);
    }

    public function cancelInvoice($invoiceId)
    {
        $invoice = ServiceInvoiceModel::findOrFail($invoiceId);
        
        if (!in_array($invoice->status, ['draft', 'pending'])) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن إلغاء هذه الفاتورة'
            ]);
            return;
        }

        $invoice->update(['status' => 'cancelled']);
        
        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم إلغاء الفاتورة بنجاح'
        ]);
    }
}
