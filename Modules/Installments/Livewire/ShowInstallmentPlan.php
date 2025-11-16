<?php

namespace Modules\Installments\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Modules\Installments\Models\InstallmentPlan;
use Modules\Installments\Models\InstallmentPayment;

class ShowInstallmentPlan extends Component
{
    public InstallmentPlan $plan;

    public $selectedPaymentId;
    public $paymentAmount;
    public $paymentDate;
    public $notes;

    public function mount(InstallmentPlan $plan)
    {
        $this->plan = $plan->load('payments', 'client');
    }

    public function openPaymentModal($paymentId)
    {
        $payment = InstallmentPayment::findOrFail($paymentId);
        $this->selectedPaymentId = $payment->id;
        $this->paymentAmount = $payment->amount_due - $payment->amount_paid; // القيمة المتبقية كقيمة افتراضية
        $this->paymentDate = Carbon::now()->format('Y-m-d');
        $this->notes = '';

        // إرسال حدث للمتصفح لفتح النافذة المنبثقة
        $this->dispatch('open-modal', 'paymentModal');
    }

    public function recordPayment()
    {
        $payment = InstallmentPayment::findOrFail($this->selectedPaymentId);

        $validated = $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01|max:' . ($payment->amount_due - $payment->amount_paid),
            'paymentDate' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $payment->update([
            'amount_paid' => $payment->amount_paid + $validated['paymentAmount'],
            'payment_date' => $validated['paymentDate'],
            'notes' => $validated['notes'],
        ]);

        // تحديث حالة القسط
        if ($payment->amount_paid >= $payment->amount_due) {
            $payment->status = 'paid';
        }
        $payment->save();

        // تحديث الخطة بعد الدفع
        $this->plan->refresh();

        // إرسال حدث للمتصفح لإغلاق النافذة
        $this->dispatch('close-modal', 'paymentModal');
        session()->flash('message', 'تم تسجيل الدفعة بنجاح!');
    }

    public function render()
    {
        return view('installments::livewire.show-installment-plan');
    }
}
