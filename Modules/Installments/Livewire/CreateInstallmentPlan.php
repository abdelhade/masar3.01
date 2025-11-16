<?php

namespace Modules\Installments\Livewire;

use Carbon\Carbon;
use App\Models\Client;
use Livewire\Component;
use Modules\Installments\Models\InstallmentPlan;

class CreateInstallmentPlan extends Component
{
    public $clientId;
    public $totalAmount = 10000;
    public $downPayment = 0;
    public $amountToBeInstalled = 10000;
    public $numberOfInstallments = 1;
    public $installmentAmount = 10000;
    public $startDate;
    public $intervalType = 'monthly';

    public function mount()
    {
        $this->startDate = Carbon::now()->format('Y-m-d');
        $this->calculateInstallments();
    }

    public function updated($propertyName)
    {
        $this->calculateInstallments();
    }

    public function calculateInstallments()
    {
        $this->totalAmount = floatval($this->totalAmount) > 0 ? floatval($this->totalAmount) : 0;
        $this->downPayment = floatval($this->downPayment) > 0 ? floatval($this->downPayment) : 0;
        $this->numberOfInstallments = intval($this->numberOfInstallments) > 0 ? intval($this->numberOfInstallments) : 1;

        $this->amountToBeInstalled = $this->totalAmount - $this->downPayment;

        if ($this->amountToBeInstalled > 0 && $this->numberOfInstallments > 0) {
            $this->installmentAmount = round($this->amountToBeInstalled / $this->numberOfInstallments, 2);
        } else {
            $this->installmentAmount = 0;
        }
    }

    public function save()
    {
        $this->validate([
            'clientId' => 'required|exists:clients,id', // تأكد من اسم الجدول والحقل
            'totalAmount' => 'required|numeric|min:0',
            'downPayment' => 'required|numeric|min:0|lte:totalAmount',
            'numberOfInstallments' => 'required|integer|min:1',
            'startDate' => 'required|date',
            'intervalType' => 'required|in:monthly,daily',
        ]);

        $this->calculateInstallments();

        $plan = InstallmentPlan::create([
            'client_id' => $this->clientId,
            'total_amount' => $this->totalAmount,
            'down_payment' => $this->downPayment,
            'amount_to_be_installed' => $this->amountToBeInstalled,
            'number_of_installments' => $this->numberOfInstallments,
            'start_date' => $this->startDate,
            'interval_type' => $this->intervalType,
        ]);

        $dueDate = Carbon::parse($this->startDate);
        $remainingAmount = $this->amountToBeInstalled;

        for ($i = 1; $i <= $this->numberOfInstallments; $i++) {
            // توزيع المبلغ المتبقي على آخر قسط لضمان عدم وجود فرق كسور
            $currentInstallmentAmount = ($i === $this->numberOfInstallments) ? $remainingAmount : $this->installmentAmount;

            $plan->payments()->create([
                'installment_number' => $i,
                'amount_due' => $currentInstallmentAmount,
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'pending',
            ]);

            $remainingAmount -= $currentInstallmentAmount;

            if ($this->intervalType == 'monthly') {
                $dueDate->addMonth();
            } else {
                $dueDate->addDay();
            }
        }

        session()->flash('message', 'تم إنشاء خطة التقسيط بنجاح.');
        return $this->redirect('/installments/plans/' . $plan->id, navigate: true);
    }

    public function render()
    {
        $clients = Client::all();
        return view('installments::livewire.create-installment-plan', [
            'clients' => $clients,
        ]);
    }
}
