<?php

declare(strict_types=1);

namespace Modules\Installments\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\Installments\Models\InstallmentPlan;
use Modules\Installments\Models\InstallmentPayment;

class EditInstallmentPlan extends Component
{
    public InstallmentPlan $plan;
    public $acc_head_id;
    public $total_amount;
    public $down_payment;
    public $amount_to_be_installed;
    public $number_of_installments;
    public $start_date;
    public $interval_type = 'monthly';
    public $clients = [];
    public $accountBalance = 0;
    public $existingPlansTotal = 0;
    public $availableBalance = 0;
    public $paidPaymentsCount = 0;

    public function mount(InstallmentPlan $plan)
    {
        $this->plan = $plan->load('payments', 'account');

        // Load plan data
        $this->acc_head_id = $plan->acc_head_id;
        $this->total_amount = $plan->total_amount;
        $this->down_payment = $plan->down_payment;
        $this->amount_to_be_installed = $plan->amount_to_be_installed;
        $this->number_of_installments = $plan->number_of_installments;
        $this->start_date = $plan->start_date->format('Y-m-d');
        $this->interval_type = $plan->interval_type;

        // Count paid payments
        $this->paidPaymentsCount = $plan->payments()->where('status', 'paid')->count();

        // Load clients
        $this->clients = $this->getAccountsByCode('1103');

        // Calculate balances
        if ($this->acc_head_id) {
            $this->updateBalances();
        }
    }

    public function updatedAccHeadId()
    {
        $this->updateBalances();
    }

    private function updateBalances()
    {
        if (!$this->acc_head_id) {
            return;
        }

        $account = AccHead::find($this->acc_head_id);
        if (!$account) {
            return;
        }

        // Get account balance
        $this->accountBalance = $account->calculateCurrentBalance();

        // Get existing plans total (excluding current plan)
        $this->existingPlansTotal = InstallmentPlan::where('acc_head_id', $this->acc_head_id)
            ->where('id', '!=', $this->plan->id)
            ->sum('amount_to_be_installed');

        // Calculate available balance
        $this->availableBalance = $this->accountBalance - $this->existingPlansTotal;
    }

    public function updatedTotalAmount()
    {
        $this->calculateAmountToBeInstalled();
    }

    public function updatedDownPayment()
    {
        $this->calculateAmountToBeInstalled();
    }

    private function calculateAmountToBeInstalled()
    {
        if ($this->total_amount && $this->down_payment !== null) {
            $this->amount_to_be_installed = $this->total_amount - $this->down_payment;
        }
    }

    public function update()
    {
        // Validate
        $validated = $this->validate([
            'acc_head_id' => 'required|exists:acc_head,id',
            'total_amount' => 'required|numeric|min:0.01',
            'down_payment' => 'required|numeric|min:0',
            'amount_to_be_installed' => 'required|numeric|min:0.01',
            'number_of_installments' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'interval_type' => 'required|in:daily,weekly,monthly,yearly',
        ]);

        // Check if amount exceeds available balance
        if ($this->amount_to_be_installed > $this->availableBalance) {
            $this->dispatch('validation-error', [
                'title' => __('Amount Error'),
                'text' => __('The requested amount is greater than available balance', [
                    'amount' => $this->amount_to_be_installed,
                    'balance' => $this->availableBalance
                ]),
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            // Update plan
            $this->plan->update($validated);

            // Recalculate unpaid payments only
            $this->recalculatePayments();

            DB::commit();

            $this->dispatch('plan-updated', [
                'title' => __('Updated Successfully'),
                'text' => __('Installment plan updated successfully'),
                'planId' => $this->plan->id,
            ]);
        } catch (\Exception) {
            DB::rollBack();

            $this->dispatch('validation-error', [
                'title' => __('Error'),
                'text' => __('An error occurred while updating the plan'),
            ]);
        }
    }

    private function recalculatePayments()
    {
        // Delete only unpaid payments
        $this->plan->payments()->where('status', '!=', 'paid')->delete();

        // Calculate new installment amount
        $installmentAmount = $this->amount_to_be_installed / $this->number_of_installments;

        // Create new payments
        $startDate = Carbon::parse($this->start_date);

        for ($i = 1; $i <= $this->number_of_installments; $i++) {
            $dueDate = match ($this->interval_type) {
                'daily' => $startDate->copy()->addDays($i - 1),
                'weekly' => $startDate->copy()->addWeeks($i - 1),
                'monthly' => $startDate->copy()->addMonths($i - 1),
                'yearly' => $startDate->copy()->addYears($i - 1),
                default => $startDate->copy()->addMonths($i - 1),
            };

            InstallmentPayment::create([
                'installment_plan_id' => $this->plan->id,
                'installment_number' => $i,
                'amount_due' => $installmentAmount,
                'amount_paid' => 0,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);
        }
    }

    private function getAccountsByCode(string $code): array
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', $code . '%')
            ->select('id', 'aname', 'code')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('installments::livewire.edit-installment-plan');
    }
}
