<?php

namespace Modules\Services\Livewire;

use Livewire\Component;
use Modules\Services\Models\ServiceInvoice as ServiceInvoiceModel;

class ServiceInvoiceShow extends Component
{
    public $invoice;

    public function mount($invoiceId)
    {
        $this->invoice = ServiceInvoiceModel::with([
            'items.service',
            'items.serviceUnit',
            'supplier',
            'customer',
            'branch',
            'creator',
            'updater',
            'approver'
        ])->findOrFail($invoiceId);
    }

    public function approve()
    {
        if (!$this->invoice->canBeApproved()) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن اعتماد هذه الفاتورة'
            ]);
            return;
        }

        $this->invoice->update([
            'status' => 'approved',
        ]);

        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم اعتماد الفاتورة بنجاح'
        ]);

        $this->invoice->refresh();
    }

    public function reject()
    {
        if (!$this->invoice->canBeApproved()) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن رفض هذه الفاتورة'
            ]);
            return;
        }

        $this->invoice->update([
            'status' => 'rejected',
        ]);

        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم رفض الفاتورة بنجاح'
        ]);

        $this->invoice->refresh();
    }

    public function cancel()
    {
        if (!in_array($this->invoice->status, ['draft', 'pending'])) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن إلغاء هذه الفاتورة'
            ]);
            return;
        }

        $this->invoice->update(['status' => 'cancelled']);

        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم إلغاء الفاتورة بنجاح'
        ]);

        $this->invoice->refresh();
    }

    public function delete()
    {
        if (!$this->invoice->canBeDeleted()) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'لا يمكن حذف هذه الفاتورة'
            ]);
            return;
        }

        $this->invoice->delete();

        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => 'تم حذف الفاتورة بنجاح'
        ]);

        return redirect()->route('services.invoices.index', ['type' => $this->invoice->type]);
    }

    public function print()
    {
        // Implement print functionality
        $this->dispatch('print-invoice', ['invoiceId' => $this->invoice->id]);
    }

    public function render()
    {
        return view('services::livewire.service-invoice-show');
    }
}
