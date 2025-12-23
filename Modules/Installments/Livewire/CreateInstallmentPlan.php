<?php

namespace Modules\Installments\Livewire;

use Carbon\Carbon;
use App\Models\Client;
use Livewire\Component;
use Modules\Accounts\Models\AccHead;
use Modules\Installments\Models\InstallmentPlan;

class CreateInstallmentPlan extends Component
{
    public $clientId;
    public $accHeadId;
    public $accountBalance = 0;
    public $totalInstallmentPlans = 0;
    public $availableBalance = 0;
    public $existingPlans = [];
    public $showBalanceWarning = false;
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
        if ($propertyName === 'accHeadId') {
            $this->updateAccountBalance();
        }

        if ($propertyName === 'totalAmount' && $this->accHeadId) {
            $this->updateAccountBalance();

            // Check if amount exceeds available balance
            if ($this->totalAmount > $this->availableBalance) {
                $this->dispatch('amount-exceeds-balance', [
                    'amount' => $this->totalAmount,
                    'balance' => $this->availableBalance,
                    'accountBalance' => $this->accountBalance,
                    'existingPlansTotal' => $this->totalInstallmentPlans,
                    'existingPlansCount' => count($this->existingPlans),
                ]);
            }
        }

        $this->calculateInstallments();
    }

    public function updateAccountBalance()
    {
        if ($this->accHeadId) {
            $account = AccHead::find($this->accHeadId);
            $this->accountBalance = $account ? $account->calculateCurrentBalance() : 0;

            // Get existing installment plans for this account
            $this->existingPlans = InstallmentPlan::where('acc_head_id', $this->accHeadId)
                ->where('status', '!=', 'cancelled')
                ->select('id', 'total_amount', 'status', 'created_at')
                ->get()
                ->toArray();

            // Calculate total amount in existing plans
            $this->totalInstallmentPlans = InstallmentPlan::where('acc_head_id', $this->accHeadId)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');

            // Calculate available balance
            $this->availableBalance = $this->accountBalance - $this->totalInstallmentPlans;
            $this->showBalanceWarning = $this->availableBalance <= 0;
        } else {
            $this->accountBalance = 0;
            $this->totalInstallmentPlans = 0;
            $this->availableBalance = 0;
            $this->existingPlans = [];
            $this->showBalanceWarning = false;
        }
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
        // Get account balance for validation
        $account = AccHead::find($this->accHeadId);
        $accountBalance = $account ? $account->calculateCurrentBalance() : 0;

        // Calculate existing plans total
        $existingPlansTotal = InstallmentPlan::where('acc_head_id', $this->accHeadId)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $availableBalance = $accountBalance - $existingPlansTotal;

        // Get existing plans for error message
        $existingPlans = InstallmentPlan::where('acc_head_id', $this->accHeadId)
            ->where('status', '!=', 'cancelled')
            ->get();

        // Check if amount exceeds available balance
        if ($this->totalAmount > $availableBalance) {
            $errorMessage = 'قيمة التقسيط (' . number_format($this->totalAmount, 2) . ' [جنيه]) أكبر من الرصيد المتاح (' . number_format($availableBalance, 2) . ' ريال)';

            if ($existingPlans->count() > 0) {
                $errorMessage .= "\n\n" . 'تفاصيل الحساب:';
                $errorMessage .= "\n" . '• إجمالي الرصيد: ' . number_format($accountBalance, 2) . ' ريال';
                $errorMessage .= "\n" . '• عدد الخطط الموجودة: ' . $existingPlans->count();
                $errorMessage .= "\n" . '• إجمالي الخطط الموجودة: ' . number_format($existingPlansTotal, 2) . ' ريال';
                $errorMessage .= "\n" . '• المتاح للتقسيط: ' . number_format($availableBalance, 2) . ' ريال';
            }

            $this->dispatch('validation-error', [
                'title' => __('Amount Error'),
                'text' => $errorMessage,
                'html' => true,
            ]);
            return;
        }

        $this->validate([
            'accHeadId' => 'required|exists:acc_head,id',
            'totalAmount' => 'required|numeric|min:0',
            'downPayment' => 'required|numeric|min:0|lte:totalAmount',
            'numberOfInstallments' => 'required|integer|min:1',
            'startDate' => 'required|date',
            'intervalType' => 'required|in:monthly,daily',
        ]);

        $this->calculateInstallments();

        $plan = InstallmentPlan::create([
            'acc_head_id' => $this->accHeadId,
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

        session()->flash('message', __('Installment plan created successfully'));

        $this->dispatch('save-success', [
            'title' => __('Saved Successfully'),
            'text' => __('Installment plan created successfully'),
            'planId' => $plan->id,
        ]);
    }

    public function render()
    {
        $clients = $this->getClientAccounts();
        return view('installments::livewire.create-installment-plan', [
            'clients' => $clients,
            'existingPlans' => $this->existingPlans,
        ]);
    }

    /**
     * Get client accounts from acc_head tree (code 1103)
     */
    private function getClientAccounts()
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1103%')
            ->select('id', 'aname', 'code')
            ->get();
    }
}
